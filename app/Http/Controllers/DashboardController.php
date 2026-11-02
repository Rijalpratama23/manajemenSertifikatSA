<?php

namespace App\Http\Controllers;

use App\Models\JenisSertifikat;
use App\Models\Peserta;
use App\Models\Sertifikat;
use App\Models\TemplateSertifikat;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $validated = $request->validate([
            'period' => ['nullable', 'date_format:Y-m'],
        ]);
        $selectedPeriod = $validated['period'] ?? now()->format('Y-m');
        $periodDate = Carbon::createFromFormat('!Y-m', $selectedPeriod);
        $awalBulan = $periodDate->copy()->startOfMonth()->toDateString();
        $akhirBulan = $periodDate->copy()->endOfMonth()->toDateString();
        $jenisAktif = JenisSertifikat::query()->where('aktif', true)->count();
        $jumlahJenis = JenisSertifikat::query()->count();
        $templateAktif = TemplateSertifikat::query()->where('aktif', true)->count();
        $jumlahTemplate = TemplateSertifikat::query()->count();
        $sertifikatAktif = Sertifikat::query()->where('status', Sertifikat::STATUS_AKTIF)->count();
        $jumlahSertifikat = Sertifikat::query()->count();
        $arsipTersedia = Sertifikat::query()->whereNotNull('berkas_pdf')->count();

        return view('dashboard', [
            'period' => $periodDate->copy()->locale('id')->translatedFormat('F Y'),
            'selectedPeriod' => $selectedPeriod,
            'statistics' => [
                [
                    'label' => 'Jenis Sertifikat',
                    'value' => $jenisAktif,
                    'detail' => 'jenis aktif untuk terbitan',
                    'tone' => 'orange',
                    'icon' => 'document',
                ],
                [
                    'label' => 'Template Aktif',
                    'value' => $templateAktif,
                    'detail' => 'template siap digunakan',
                    'tone' => 'teal',
                    'icon' => 'template',
                ],
                [
                    'label' => 'Sertifikat Terbit',
                    'value' => Sertifikat::query()
                        ->whereBetween('tanggal_terbit', [$awalBulan, $akhirBulan])
                        ->count(),
                    'detail' => 'sertifikat pada periode ini',
                    'tone' => 'orange',
                    'icon' => 'print',
                ],
            ],
            'summary' => [
                ['label' => 'Data peserta', 'value' => Peserta::query()->count()],
                ['label' => 'Jenis sertifikat aktif', 'value' => $jenisAktif],
                ['label' => 'Template siap', 'value' => $templateAktif],
                ['label' => 'Sertifikat aktif', 'value' => $sertifikatAktif],
                ['label' => 'Arsip tersedia', 'value' => $arsipTersedia],
            ],
            'statuses' => [
                [
                    'label' => 'Jenis sertifikat aktif',
                    'percentage' => $this->percentage($jenisAktif, $jumlahJenis),
                    'tone' => 'teal',
                ],
                [
                    'label' => 'Template aktif',
                    'percentage' => $this->percentage($templateAktif, $jumlahTemplate),
                    'tone' => 'orange',
                ],
                [
                    'label' => 'Sertifikat aktif',
                    'percentage' => $this->percentage($sertifikatAktif, $jumlahSertifikat),
                    'tone' => 'teal',
                ],
                [
                    'label' => 'Arsip tersedia',
                    'percentage' => $this->percentage($arsipTersedia, $jumlahSertifikat),
                    'tone' => 'teal',
                ],
            ],
            'recentCertificates' => Sertifikat::query()
                ->with([
                    'peserta:id_peserta,nama',
                    'jenis:id_jenis,nama_jenis',
                ])
                ->whereBetween('tanggal_terbit', [$awalBulan, $akhirBulan])
                ->orderByDesc('tanggal_terbit')
                ->limit(4)
                ->get(),
        ]);
    }

    private function percentage(int $value, int $total): int
    {
        return $total === 0 ? 0 : (int) round(($value / $total) * 100);
    }
}
