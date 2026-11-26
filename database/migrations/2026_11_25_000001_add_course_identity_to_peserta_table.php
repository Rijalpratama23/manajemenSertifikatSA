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
            $table->unsignedInteger('nis')->nullable()->unique()->after('id_peserta');
            $table->string('email', 150)->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('peserta')->whereNull('email')->exists()) {
            throw new RuntimeException('Email peserta kursus yang kosong harus dilengkapi sebelum migrasi ini dapat dibatalkan.');
        }

        Schema::table('peserta', function (Blueprint $table) {
            $table->dropUnique(['nis']);
            $table->dropColumn('nis');
            $table->string('email', 150)->nullable(false)->change();
        });
    }
};
