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
        Schema::create('pelatihan_lpk', function (Blueprint $table) {
            $table->increments('id_pelatihan');
            $table->unsignedInteger('id_sertifikat')->unique();
            $table->string('nama_lpk', 150);
            $table->string('program_pelatihan', 150);
            $table->unsignedInteger('jumlah_jam')->nullable();
            $table->string('instruktur', 150)->nullable();
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
        Schema::dropIfExists('pelatihan_lpk');
    }
};
