<?php

namespace Database\Seeders;

use App\Models\JenisSertifikat;
use Illuminate\Database\Seeder;

class JenisSertifikatSeeder extends Seeder
{
    public function run(): void
    {
        // Lima jenis sertifikat sesuai ruang lingkup (F-03).
        $jenis = [
            ['kode' => 'MAGANG', 'nama_jenis' => 'Magang'],
            ['kode' => 'PKL', 'nama_jenis' => 'Praktik Kerja Lapangan (PKL)'],
            ['kode' => 'LPK', 'nama_jenis' => 'Pelatihan LPK'],
            ['kode' => 'UJIKOM', 'nama_jenis' => 'Uji Kompetensi DUDI'],
            ['kode' => 'KURSUS', 'nama_jenis' => 'Kursus'],
        ];

        foreach ($jenis as $item) {
            JenisSertifikat::updateOrCreate(['kode' => $item['kode']], $item + ['aktif' => true]);
        }
    }
}
