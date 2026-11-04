<?php

namespace App\Http\Controllers;

use App\Models\JenisSertifikat;
use App\Models\NomorUrut;
use App\Models\Peserta;
use App\Models\Sertifikat;
use App\Models\TemplateSertifikat;
use App\Services\CertificatePdfService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class SertifikatController extends Controller
{
    public function create(Request $request): View
    {
        $peserta = Peserta::query()
            ->withCount('sertifikat')
            ->orderBy('nama')
            ->get(['id_peserta', 'nama', 'email', 'asal_instansi']);

        $jenisSertifikat = JenisSertifikat::query()
            ->where('aktif', true)
            ->whereHas('template', fn ($query) => $query->where('aktif', true))
            ->with(['template' => fn ($query) => $query
                ->where('aktif', true)
                ->orderByDesc('id_template')])
            ->orderBy('nama_jenis')
            ->get(['id_jenis', 'kode', 'nama_jenis']);

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

        return view('certificates.issue', compact(
            'peserta',
            'jenisSertifikat',
            'issuedCertificate',
            'reissueCertificate',
        ));
    }

    public function store(Request $request, CertificatePdfService $pdfService): RedirectResponse
    {
        $typeId = $request->input('id_jenis');
        $validator = Validator::make($request->all(), [
            'id_peserta' => ['required', 'integer', Rule::exists('peserta', 'id_peserta')],
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
            'durasi' => ['nullable', 'string', 'max:50'],
            'nilai' => ['nullable', 'string', 'max:20'],
            'predikat' => ['nullable', 'string', 'max:30'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('certificate-issues.create')
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();
        $pdfPath = null;

        try {
            $certificate = DB::transaction(function () use ($validated, $pdfService, &$pdfPath) {
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

                $issueDate = \Illuminate\Support\Carbon::parse($validated['tanggal_terbit']);
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
                $nextNumber = $sequence->nomor_terakhir + 1;
                $sequence->update(['nomor_terakhir' => $nextNumber]);

                $number = sprintf('%s/%d/%04d', $jenis->kode, $year, $nextNumber);

                $certificate = Sertifikat::query()->create([
                    'nomor_sertifikat' => $number,
                    'token_publik' => bin2hex(random_bytes(32)),
                    'kode_verifikasi' => strtoupper(bin2hex(random_bytes(6))),
                    'id_peserta' => $validated['id_peserta'],
                    'id_jenis' => $validated['id_jenis'],
                    'id_admin' => auth()->user()->getAuthIdentifier(),
                    'id_template' => $template->getKey(),
                    'periode_mulai' => $validated['periode_mulai'],
                    'periode_selesai' => $validated['periode_selesai'],
                    'durasi' => $validated['durasi'] ?? null,
                    'nilai' => $validated['nilai'] ?? null,
                    'predikat' => $validated['predikat'] ?? null,
                    'tanggal_terbit' => $validated['tanggal_terbit'],
                    'status' => Sertifikat::STATUS_AKTIF,
                ]);
                $certificate->setRelation('peserta', Peserta::query()->findOrFail($validated['id_peserta']));
                $certificate->setRelation('jenis', $jenis);
                $certificate->setRelation('template', $template);

                $pdfPath = sprintf('certificates/%d-%s.pdf', $certificate->getKey(), bin2hex(random_bytes(8)));
                if (! Storage::disk('local')->put($pdfPath, $pdfService->render($certificate))) {
                    throw new RuntimeException('PDF sertifikat gagal disimpan.');
                }

                $certificate->update(['berkas_pdf' => $pdfPath]);

                return $certificate;
            }, attempts: 3);
        } catch (Throwable $exception) {
            if ($pdfPath !== null) {
                $disk = Storage::disk('local');
                if ($disk->exists($pdfPath) && ! $disk->delete($pdfPath)) {
                    logger()->warning('Gagal membersihkan PDF sementara setelah penerbitan sertifikat gagal.', [
                        'path' => $pdfPath,
                    ]);
                }
            }

            throw $exception;
        }

        return redirect()->route('certificate-issues.create')
            ->with('success', 'Sertifikat berhasil diterbitkan.')
            ->with('issuedCertificateId', $certificate->getKey());
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
}
