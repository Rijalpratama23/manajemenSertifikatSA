<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateCertificatePdf;
use App\Models\JenisSertifikat;
use App\Models\NomorUrut;
use App\Models\Peserta;
use App\Models\Sertifikat;
use App\Models\TemplateSertifikat;
use App\Services\CourseParticipantSyncService;
use App\Services\CertificateZipService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SertifikatController extends Controller
{
    public function create(Request $request, CourseParticipantSyncService $courseParticipantSync): View
    {
        $courseParticipantSync->sync();

        $peserta = Peserta::query()
            ->withCount('sertifikat')
            ->with(['sertifikat:id_sertifikat,id_peserta,id_jenis,status'])
            ->orderBy('nama')
            ->get([
                'id_peserta',
                'nis',
                'nama',
                'tempat_lahir',
                'tanggal_lahir',
                'email',
                'asal_instansi',
            ]);

        $jenisSertifikat = JenisSertifikat::query()
            ->where('aktif', true)
            ->whereHas('template', fn ($query) => $query->where('aktif', true))
            ->with(['template' => fn ($query) => $query
                ->where('aktif', true)
                ->orderByDesc('id_template')])
            ->orderBy('nama_jenis')
            ->get(['id_jenis', 'kode', 'nama_jenis']);
        $issuanceFieldsByType = $jenisSertifikat->mapWithKeys(
            fn (JenisSertifikat $jenis) => [$jenis->kode => $this->issuanceFieldsFor($jenis)],
        )->all();

        $validated = $request->validate([
            'ulangi' => [
                'nullable',
                'integer',
                Rule::exists('sertifikat', 'id_sertifikat')
                    ->where('status', Sertifikat::STATUS_DIBATALKAN),
            ],
        ]);
        $reissueCertificate = isset($validated['ulangi'])
            ? Sertifikat::query()->findOrFail($validated['ulangi'])
            : null;

        $issuedCertificate = session()->has('issuedCertificateId')
            ? Sertifikat::query()
                ->with(['peserta', 'jenis', 'template'])
                ->find(session('issuedCertificateId'))
            : null;
        $issuedCertificates = $request->boolean('batch') && session()->has('issuedCertificateIds')
            ? Sertifikat::query()
                ->with(['peserta', 'jenis'])
                ->whereIn('id_sertifikat', session('issuedCertificateIds'))
                ->orderBy('id_sertifikat')
                ->get()
            : collect();
        $issuedCertificate ??= $issuedCertificates->first();
        $issuedBatchReady = $issuedCertificates->count() > 1
            && $issuedCertificates->every(fn (Sertifikat $certificate) => (
                $certificate->pdf_status === Sertifikat::PDF_STATUS_COMPLETED
                && $certificate->berkas_pdf
                && Storage::disk('local')->exists($certificate->berkas_pdf)
            ));
        $pendingPdfStatuses = [
            Sertifikat::PDF_STATUS_PENDING,
            Sertifikat::PDF_STATUS_PROCESSING,
        ];
        $issuedPdfPending = (
            $issuedCertificate
            && in_array($issuedCertificate->pdf_status, $pendingPdfStatuses, true)
        ) || $issuedCertificates->contains(
            fn (Sertifikat $certificate) => in_array($certificate->pdf_status, $pendingPdfStatuses, true),
        );

        return view('certificates.issue', compact(
            'peserta',
            'jenisSertifikat',
            'issuanceFieldsByType',
            'issuedCertificate',
            'issuedCertificates',
            'issuedBatchReady',
            'issuedPdfPending',
            'reissueCertificate',
        ));
    }

    public function downloadBatch(Request $request, CertificateZipService $zipService): BinaryFileResponse|RedirectResponse
    {
        $validated = $request->validate([
            'certificate_ids' => ['required', 'array', 'min:2'],
            'certificate_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('sertifikat', 'id_sertifikat'),
            ],
        ]);

        $certificates = Sertifikat::query()
            ->whereIn('id_sertifikat', $validated['certificate_ids'])
            ->orderBy('id_sertifikat')
            ->get();

        try {
            return $zipService->download($certificates, 'sertifikat-batch.zip');
        } catch (ValidationException $exception) {
            return redirect()->route('certificate-issues.create', ['batch' => 1])
                ->withErrors($exception->errors());
        }
    }

    public function store(Request $request): RedirectResponse
    {
        if (! is_array($request->input('id_peserta'))) {
            $request->merge(['id_peserta' => [$request->input('id_peserta')]]);
        }

        $typeId = $request->input('id_jenis');
        $typeId = is_scalar($typeId) && is_numeric($typeId) ? (int) $typeId : null;
        $type = $typeId !== null
            ? JenisSertifikat::query()->whereKey($typeId)->first(['nama_jenis', 'kode'])
            : null;
        $typeFields = $type ? $this->issuanceFieldsFor($type)['fields'] : [];
        $isCourseCertificate = $type
            && (bool) ($this->issuanceFieldsFor($type)['course_certificate'] ?? false);
        $validator = Validator::make($request->all(), [
            'id_peserta' => ['required', 'array', 'min:1'],
            'id_peserta.*' => ['required', 'integer', 'distinct', Rule::exists('peserta', 'id_peserta')],
            'id_jenis' => [
                'required',
                'integer',
                Rule::exists('jenis_sertifikat', 'id_jenis')
                    ->where('aktif', true),
            ],
            'id_template' => [
                'nullable',
                'integer',
                Rule::exists('template_sertifikat', 'id_template')
                    ->where('id_jenis', $typeId)
                    ->where('aktif', true),
            ],
            'tanggal_terbit' => ['required', 'date'],
            'periode_mulai' => ['required', 'date'],
            'periode_selesai' => ['required', 'date', 'after_or_equal:periode_mulai'],
            'data_pendukung' => $typeFields
                ? ['required', 'array:'.implode(',', array_column($typeFields, 'key'))]
                : ['nullable', 'array'],
            'deskripsi' => [$isCourseCertificate ? 'nullable' : 'required', 'string', 'max:500'],
        ]);
        foreach ($typeFields as $field) {
            $validator->addRules([
                'data_pendukung.'.$field['key'] => [
                    $field['required'] ? 'required' : 'nullable',
                    'string',
                    'max:150',
                ],
            ]);
        }
        if ($validator->fails()) {
            return redirect()->route('certificate-issues.create')
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();
        $supportValues = [];
        $legacyValues = ['durasi' => null, 'nilai' => null, 'predikat' => null];
        foreach ($typeFields as $field) {
            $value = $validated['data_pendukung'][$field['key']] ?? null;
            if ($value !== null && $value !== '') {
                $supportValues[$field['key']] = $value;
            }
            if (isset($field['column'])) {
                $legacyValues[$field['column']] = $value;
            }
        }
        if ($isCourseCertificate) {
            $support = $validated['data_pendukung'];
            $validated['deskripsi'] = sprintf(
                'Telah menyelesaikan pembelajaran %s tahun %s dengan prestasi %s pada level %s.',
                $support['nama_kursus'],
                $support['tahun_ajaran'],
                $support['prestasi'],
                $support['level'],
            );
        }
        $certificateIds = DB::transaction(function () use ($validated, $supportValues, $legacyValues) {
            $jenis = JenisSertifikat::query()
                ->whereKey($validated['id_jenis'])
                ->where('aktif', true)
                ->lockForUpdate()
                ->first();
            $template = TemplateSertifikat::query()
                ->where('id_jenis', $validated['id_jenis'])
                ->where('aktif', true)
                ->orderByDesc('id_template')
                ->lockForUpdate()
                ->first();

            if (! $jenis) {
                throw ValidationException::withMessages([
                    'id_jenis' => 'Jenis sertifikat sudah tidak aktif. Pilih jenis lain.',
                ]);
            }

            if (! $template) {
                throw ValidationException::withMessages([
                    'id_jenis' => 'Jenis sertifikat sudah tidak memiliki template aktif. Pilih jenis lain.',
                ]);
            }

            if (
                ! empty($validated['id_template'])
                && (int) $validated['id_template'] !== $template->getKey()
            ) {
                throw ValidationException::withMessages([
                    'id_jenis' => 'Template aktif telah berubah. Periksa kembali pratinjau sertifikat.',
                ]);
            }

            $alreadyIssued = Sertifikat::query()
                ->whereIn('id_peserta', $validated['id_peserta'])
                ->where('id_jenis', $validated['id_jenis'])
                ->where('status', Sertifikat::STATUS_AKTIF)
                ->exists();
            if ($alreadyIssued) {
                throw ValidationException::withMessages([
                    'id_peserta' => 'Sebagian peserta sudah memiliki sertifikat aktif untuk jenis ini. Periksa kembali pilihan peserta.',
                ]);
            }

            $issueDate = Carbon::parse($validated['tanggal_terbit']);
            $year = (int) $issueDate->format('Y');

            DB::table('nomor_urut')->insertOrIgnore([
                'id_jenis' => $validated['id_jenis'],
                'tahun' => $year,
                'nomor_terakhir' => 0,
            ]);

            $sequence = NomorUrut::query()
                ->where('id_jenis', $validated['id_jenis'])
                ->where('tahun', $year)
                ->lockForUpdate()
                ->firstOrFail();
            $issued = [];
            foreach ($validated['id_peserta'] as $participantId) {
                $nextNumber = $sequence->nomor_terakhir + 1;
                $sequence->update(['nomor_terakhir' => $nextNumber]);
                $number = sprintf('%s/%d/%04d', $jenis->kode, $year, $nextNumber);

                $certificate = Sertifikat::query()->create([
                    'nomor_sertifikat' => $number,
                    'token_publik' => bin2hex(random_bytes(32)),
                    'kode_verifikasi' => strtoupper(bin2hex(random_bytes(6))),
                    'id_peserta' => $participantId,
                    'id_jenis' => $validated['id_jenis'],
                    'id_admin' => auth()->user()->getAuthIdentifier(),
                    'id_template' => $template->getKey(),
                    'periode_mulai' => $validated['periode_mulai'],
                    'periode_selesai' => $validated['periode_selesai'],
                    'durasi' => $legacyValues['durasi'],
                    'nilai' => $legacyValues['nilai'],
                    'predikat' => $legacyValues['predikat'],
                    'deskripsi' => $validated['deskripsi'],
                    'data_pendukung' => $supportValues,
                    'tanggal_terbit' => $validated['tanggal_terbit'],
                    'status' => Sertifikat::STATUS_AKTIF,
                    'pdf_status' => Sertifikat::PDF_STATUS_PENDING,
                ]);
                GenerateCertificatePdf::dispatch($certificate->getKey())
                    ->onConnection('certificate_pdfs')
                    ->onQueue('certificate-pdfs')
                    ->beforeCommit();
                $issued[] = $certificate->getKey();
            }

            return $issued;
        }, attempts: 3);

        session()->put('issuedCertificateIds', $certificateIds);

        return redirect()->route('certificate-issues.create', ['batch' => 1])
            ->with('success', count($certificateIds) === 1
                ? 'Sertifikat berhasil diterbitkan.'
                : count($certificateIds).' sertifikat berhasil diterbitkan.');
    }

    public function download(Sertifikat $sertifikat)
    {
        abort_unless($sertifikat->berkas_pdf, 404);
        $disk = Storage::disk('local');
        abort_unless($disk->exists($sertifikat->berkas_pdf), 404);

        $filename = str_replace(['/', '\\'], '-', $sertifikat->nomor_sertifikat).'.pdf';

        return $disk->download($sertifikat->berkas_pdf, $filename);
    }

    public function preview(Sertifikat $sertifikat)
    {
        abort_unless($sertifikat->berkas_pdf, 404);
        $disk = Storage::disk('local');
        abort_unless($disk->exists($sertifikat->berkas_pdf), 404);

        $filename = str_replace(['/', '\\'], '-', $sertifikat->nomor_sertifikat).'.pdf';

        return response()->file($disk->path($sertifikat->berkas_pdf), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    private function issuanceFieldsFor(JenisSertifikat $jenis): array
    {
        $types = config('certificate-issuance.types', []);
        $schema = $types[strtoupper($jenis->kode)] ?? null;
        if ($schema) {
            return $schema;
        }

        $name = mb_strtolower(trim($jenis->nama_jenis));
        foreach ($types as $candidate) {
            if (in_array($name, $candidate['names'] ?? [], true)) {
                return $candidate;
            }
        }

        return ['title' => 'Data pendukung', 'fields' => []];
    }
}
