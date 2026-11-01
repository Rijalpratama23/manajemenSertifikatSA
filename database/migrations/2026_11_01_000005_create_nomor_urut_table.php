<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nomor_urut', function (Blueprint $table) {
            $table->increments('id_nomor');
            $table->unsignedInteger('id_jenis');
            $table->unsignedSmallInteger('tahun');
            $table->unsignedInteger('nomor_terakhir')->default(0);

            // Urutan dihitung terpisah per jenis dan per tahun (F-06).
            $table->unique(['id_jenis', 'tahun']);

            $table->foreign('id_jenis')
                ->references('id_jenis')->on('jenis_sertifikat')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nomor_urut');
    }
};
