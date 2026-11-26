<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $templates = DB::table('template_sertifikat')
            ->join('jenis_sertifikat', 'jenis_sertifikat.id_jenis', '=', 'template_sertifikat.id_jenis')
            ->whereIn('jenis_sertifikat.kode', ['KURSUS', '34'])
            ->select(['template_sertifikat.id_template', 'template_sertifikat.konfigurasi_posisi'])
            ->get();

        foreach ($templates as $template) {
            $configuration = is_array($template->konfigurasi_posisi)
                ? $template->konfigurasi_posisi
                : (json_decode((string) $template->konfigurasi_posisi, true) ?: []);
            $configuration['qr'] = array_replace(
                ['x' => 25, 'y' => 77, 'size' => 9, 'visible' => true],
                $configuration['qr'] ?? [],
                ['x' => 25, 'y' => 77, 'size' => 9],
            );

            DB::table('template_sertifikat')
                ->where('id_template', $template->id_template)
                ->update(['konfigurasi_posisi' => json_encode($configuration)]);
        }
    }

    public function down(): void
    {
        // Keep the explicit position configuration when rolling back; templates may have been edited since.
    }
};
