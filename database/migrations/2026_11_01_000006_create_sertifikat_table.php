<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sertifikat', function (Blueprint $table) {
            $table->increments('id_sertifikat');

            // Identitas sertifikat. token_publik dan kode_verifikasi acak, dipakai
            // pada QR dan halaman verifikasi (bukan nomor urut).
            $table->string('nomor_sertifikat', 50)->unique();
            $table->string('token_publik', 64)->unique();
            $table->string('kode_verifikasi', 12)->unique();

            $table->unsignedInteger('id_peserta');
            $table->unsignedInteger('id_jenis');
            $table->unsignedInteger('id_admin');
            $table->unsignedInteger('id_template');

            $table->date('periode_mulai')->nullable();
            $table->date('periode_selesai')->nullable();
            $table->string('durasi', 50)->nullable();
            $table->string('nilai', 20)->nullable();
            $table->string('predikat', 30)->nullable();

            $table->date('tanggal_terbit');
            $table->enum('status', ['aktif', 'dibatalkan'])->default('aktif');
            $table->string('berkas_pdf', 255)->nullable();
            $table->string('alasan_batal', 255)->nullable();
            $table->dateTime('dibatalkan_pada')->nullable();

            $table->index('status');

            // Sertifikat tidak boleh kehilangan induknya: hapus ditolak (restrict).
            $table->foreign('id_peserta')->references('id_peserta')->on('peserta')->restrictOnDelete();
            $table->foreign('id_jenis')->references('id_jenis')->on('jenis_sertifikat')->restrictOnDelete();
            $table->foreign('id_admin')->references('id_admin')->on('admin')->restrictOnDelete();
            $table->foreign('id_template')->references('id_template')->on('template_sertifikat')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sertifikat');
    }
};
