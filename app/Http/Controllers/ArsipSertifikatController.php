<?php

namespace App\Http\Controllers;

use App\Models\JenisSertifikat;
use App\Models\Sertifikat;
use App\Services\CertificateZipService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
        $certificateTypes = JenisSertifikat::query()
            ->select(['id_jenis', 'nama_jenis'])
            ->withCount([
                'sertifikat as total_certificates',
                'sertifikat as active_certificates' => fn (Builder $query) => $query
                    ->where('status', Sertifikat::STATUS_AKTIF),
                'sertifikat as cancelled_certificates' => fn (Builder $query) => $query
                    ->where('status', Sertifikat::STATUS_DIBATALKAN),
            ])
            ->orderBy('nama_jenis')
            ->get();
        $selectedCertificateType = $certificateTypes->firstWhere('id_jenis', $selectedType);
        $certificates = $selectedCertificateType
            ? $this->filteredCertificatesQuery($filters)
                ->orderByDesc('tanggal_terbit')
                ->orderByDesc('id_sertifikat')
                ->paginate(10)
                ->withQueryString()
            : null;

        $certificateCountsQuery = Sertifikat::query()
            ->when($selectedType !== '', fn (Builder $query) => $query->where('id_jenis', $selectedType))
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status');
        $certificateCounts = $certificateCountsQuery->pluck('total', 'status');
        $totalCertificates = (int) $certificateCounts->sum();
        $activeCertificates = (int) $certificateCounts->get(Sertifikat::STATUS_AKTIF, 0);
        $cancelledCertificates = (int) $certificateCounts->get(Sertifikat::STATUS_DIBATALKAN, 0);

        return view('certificate-archive.index', compact(
            'certificates',
            'certificateTypes',
            'selectedCertificateType',
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

    public function downloadAll(Request $request, CertificateZipService $zipService): BinaryFileResponse
    {
        $filters = $this->validatedFilters($request, $this->availableYears());
        $certificates = $this->filteredCertificatesQuery($filters)
            ->orderByDesc('tanggal_terbit')
            ->orderByDesc('id_sertifikat')
            ->get();

        return $zipService->download($certificates, 'arsip-sertifikat.zip');
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
