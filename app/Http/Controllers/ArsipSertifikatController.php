<?php

namespace App\Http\Controllers;

use App\Models\JenisSertifikat;
use App\Models\Sertifikat;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class ArsipSertifikatController extends Controller
{
    public function index(Request $request): View
    {
        $years = $this->availableYears();
        $filters = $this->validatedFilters($request, $years);
        $search = trim($filters['search'] ?? '');
        $selectedType = $filters['jenis'] ?? '';
        $status = $filters['status'] ?? 'all';
        $dateFilter = $filters['tanggal'] ?? 'all';
        $certificates = $this->filteredCertificatesQuery($filters)
            ->orderByDesc('tanggal_terbit')
            ->orderByDesc('id_sertifikat')
            ->paginate(10)
            ->withQueryString();

        $certificateCounts = Sertifikat::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');
        $totalCertificates = (int) $certificateCounts->sum();
        $activeCertificates = (int) $certificateCounts->get(Sertifikat::STATUS_AKTIF, 0);
        $cancelledCertificates = (int) $certificateCounts->get(Sertifikat::STATUS_DIBATALKAN, 0);
        $certificateTypes = JenisSertifikat::query()
            ->orderBy('nama_jenis')
            ->get(['id_jenis', 'nama_jenis']);

        return view('certificate-archive.index', compact(
            'certificates',
            'certificateTypes',
            'years',
            'search',
            'selectedType',
            'status',
            'dateFilter',
            'totalCertificates',
            'activeCertificates',
            'cancelledCertificates',
        ));
    }

    public function downloadAll(Request $request): BinaryFileResponse
    {
        abort_unless(class_exists(ZipArchive::class), 501, 'Unduhan massal memerlukan ekstensi PHP Zip.');

        $filters = $this->validatedFilters($request, $this->availableYears());
        $certificates = $this->filteredCertificatesQuery($filters)
            ->orderByDesc('tanggal_terbit')
            ->orderByDesc('id_sertifikat')
            ->get();

        if ($certificates->isEmpty()) {
            throw ValidationException::withMessages([
                'download' => 'Tidak ada sertifikat yang sesuai dengan filter untuk diunduh.',
            ]);
        }

        $disk = Storage::disk('local');
        foreach ($certificates as $certificate) {
            if (! $certificate->berkas_pdf || ! $disk->exists($certificate->berkas_pdf)) {
                throw ValidationException::withMessages([
                    'download' => "Berkas PDF sertifikat {$certificate->nomor_sertifikat} tidak tersedia. Unduhan dibatalkan agar hasil tidak terpotong.",
                ]);
            }
        }

        $archivePath = tempnam(storage_path('app'), 'certificate-archive-');
        if ($archivePath === false) {
            throw new RuntimeException('File sementara untuk arsip ZIP tidak dapat dibuat.');
        }

        $archive = new ZipArchive();
        if ($archive->open($archivePath, ZipArchive::OVERWRITE) !== true) {
            unlink($archivePath);
            throw new RuntimeException('Arsip ZIP sertifikat tidak dapat dibuat.');
        }

        foreach ($certificates as $certificate) {
            $filename = preg_replace('/[^A-Za-z0-9._-]+/', '-', $certificate->nomor_sertifikat).'.pdf';
            if (! $archive->addFile($disk->path($certificate->berkas_pdf), $filename)) {
                $archive->close();
                unlink($archivePath);
                throw new RuntimeException("PDF sertifikat {$certificate->nomor_sertifikat} gagal ditambahkan ke arsip ZIP.");
            }
        }

        if (! $archive->close()) {
            unlink($archivePath);
            throw new RuntimeException('Arsip ZIP sertifikat gagal diselesaikan.');
        }

        return response()->download($archivePath, 'arsip-sertifikat.zip')
            ->deleteFileAfterSend(true);
    }

    public function show(Sertifikat $sertifikat): View
    {
        $sertifikat->load(['peserta', 'jenis']);

        return view('certificate-archive.show', compact('sertifikat'));
    }

    public function cancel(Sertifikat $sertifikat): RedirectResponse
    {
        DB::transaction(function () use ($sertifikat) {
            $lockedCertificate = Sertifikat::query()
                ->whereKey($sertifikat->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedCertificate->isAktif()) {
                throw ValidationException::withMessages([
                    'sertifikat' => 'Sertifikat ini sudah dibatalkan.',
                ]);
            }

            $lockedCertificate->update([
                'status' => Sertifikat::STATUS_DIBATALKAN,
                'alasan_batal' => 'Dibatalkan oleh admin melalui arsip sertifikat.',
                'dibatalkan_pada' => now(),
            ]);
        });

        return redirect()->route('certificate-archive.index')
            ->with('success', "Sertifikat {$sertifikat->nomor_sertifikat} berhasil dibatalkan.");
    }

    private function availableYears(): array
    {
        $dateBounds = Sertifikat::query()
            ->selectRaw('MIN(tanggal_terbit) as earliest_date, MAX(tanggal_terbit) as latest_date')
            ->first();
        $years = $dateBounds->earliest_date
            ? range((int) substr($dateBounds->latest_date, 0, 4), (int) substr($dateBounds->earliest_date, 0, 4))
            : [];

        return array_map('strval', $years);
    }

    private function validatedFilters(Request $request, array $years): array
    {
        return $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'jenis' => ['nullable', 'integer', Rule::exists('jenis_sertifikat', 'id_jenis')],
            'status' => ['nullable', Rule::in(['all', Sertifikat::STATUS_AKTIF, Sertifikat::STATUS_DIBATALKAN])],
            'tanggal' => ['nullable', Rule::in(array_merge(['all', 'current_month', 'current_year'], $years))],
        ]);
    }

    private function filteredCertificatesQuery(array $filters): Builder
    {
        $search = trim($filters['search'] ?? '');
        $selectedType = $filters['jenis'] ?? '';
        $status = $filters['status'] ?? 'all';
        $dateFilter = $filters['tanggal'] ?? 'all';

        $query = Sertifikat::query()
            ->with([
                'peserta:id_peserta,nama',
                'jenis:id_jenis,nama_jenis',
            ])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('nomor_sertifikat', 'like', "%{$search}%")
                        ->orWhereHas('peserta', fn (Builder $participantQuery) => $participantQuery
                            ->where('nama', 'like', "%{$search}%"));
                });
            })
            ->when($selectedType !== '', fn (Builder $query) => $query->where('id_jenis', $selectedType))
            ->when($status !== 'all', fn (Builder $query) => $query->where('status', $status));

        if ($dateFilter === 'current_month') {
            $query->whereBetween('tanggal_terbit', [now()->startOfMonth(), now()->endOfMonth()]);
        } elseif ($dateFilter === 'current_year') {
            $query->whereYear('tanggal_terbit', now()->year);
        } elseif (ctype_digit($dateFilter)) {
            $query->whereYear('tanggal_terbit', (int) $dateFilter);
        }

        return $query;
    }
}
