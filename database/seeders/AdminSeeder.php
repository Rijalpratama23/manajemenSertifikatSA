<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Kredensial dibaca dari .env supaya tidak ada kata sandi yang tertulis di kode.
        $username = env('ADMIN_USERNAME');
        $password = env('ADMIN_PASSWORD');

        if (! $username || ! $password) {
            throw new RuntimeException('Isi ADMIN_USERNAME dan ADMIN_PASSWORD di .env sebelum menjalankan seeder.');
        }

        // Model meng-hash password_hash otomatis lewat cast 'hashed'.
        Admin::updateOrCreate(
            ['username' => $username],
            ['nama' => env('ADMIN_NAMA', 'Administrator'), 'password_hash' => $password],
        );
    }
}
