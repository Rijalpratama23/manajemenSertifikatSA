<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peserta_jenis_sertifikat', function (Blueprint $table) {
            $table->unsignedInteger('id_peserta');
            $table->unsignedInteger('id_jenis');
            $table->primary(['id_peserta', 'id_jenis']);
            $table->foreign('id_peserta')->references('id_peserta')->on('peserta')->cascadeOnDelete();
            $table->foreign('id_jenis')->references('id_jenis')->on('jenis_sertifikat')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peserta_jenis_sertifikat');
    }
};
