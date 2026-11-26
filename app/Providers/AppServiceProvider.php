<?php

namespace App\Providers;

use App\Models\JenisSertifikat;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        view()->composer('components.admin.sidebar', function (View $view) {
            $certificateTypes = JenisSertifikat::query()
                ->where('aktif', true)
                ->whereIn('kode', ['KURSUS', '34', 'PKL', 'MAGANG', 'UJIKOM', 'LPK'])
                ->get(['id_jenis', 'kode', 'nama_jenis'])
                ->sortBy(fn (JenisSertifikat $certificateType) =>
                    ['KURSUS' => 1, '34' => 1, 'PKL' => 2, 'MAGANG' => 3, 'UJIKOM' => 4, 'LPK' => 5][$certificateType->kode]
                )
                ->values();

            $view->with(
                'sidebarCertificateTypes',
                $certificateTypes,
            );
        });
    }
}
