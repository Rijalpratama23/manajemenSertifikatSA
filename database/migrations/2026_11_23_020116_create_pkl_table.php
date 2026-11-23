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
        Schema::create('pkl', function (Blueprint $table) {
            $table->increments('id_pkl');
            $table->unsignedInteger('id_sertifikat')->unique();
            $table->string('nama_instansi', 150);
            $table->string('posisi', 150)->nullable();
            $table->string('pembimbing_instansi', 150)->nullable();
            $table->string('pembimbing_sekolah', 150)->nullable();
            $table->text('deskripsi')->nullable();
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
        Schema::dropIfExists('pkl');
    }
};
