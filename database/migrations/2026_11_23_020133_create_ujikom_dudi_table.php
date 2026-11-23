<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ujikom_dudi', function (Blueprint $table) {
            $table->increments('id_ujikom');
            $table->unsignedInteger('id_sertifikat')->unique();
            $table->string('skema_uji', 150);
            $table->string('nomor_skema', 50)->nullable();
            $table->string('lokasi_uji', 150)->nullable();
            $table->string('asesor', 150)->nullable();
            $table->enum('status_kelulusan', [
                'kompeten',
                'belum_kompeten',
            ])->default('kompeten');
            $table->date('tanggal_uji')->nullable();
            $table->timestamps();

            $table->foreign('id_sertifikat')
                ->references('id_sertifikat')
                ->on('sertifikat')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ujikom_dudi');
    }
};
