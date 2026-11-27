<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peserta', function (Blueprint $table) {
            $table->unsignedBigInteger('nis')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('peserta')->where('nis', '>', 4_294_967_295)->exists()) {
            throw new RuntimeException('NIS di atas batas UNSIGNED INT harus diperbaiki sebelum migrasi ini dapat dibatalkan.');
        }

        Schema::table('peserta', function (Blueprint $table) {
            $table->unsignedInteger('nis')->nullable()->change();
        });
    }
};
