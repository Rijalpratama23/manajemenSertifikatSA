<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_sertifikat', function (Blueprint $table) {
            $table->increments('id_template');
            $table->unsignedInteger('id_jenis');
            $table->string('nama_template', 100);
            $table->string('berkas_desain', 255);
            $table->json('konfigurasi_posisi')->nullable();
            $table->boolean('aktif')->default(true);
            $table->dateTime('dibuat_pada')->nullable();

            $table->foreign('id_jenis')
                ->references('id_jenis')->on('jenis_sertifikat')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_sertifikat');
    }
};
