<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peserta', function (Blueprint $table) {
            $table->increments('id_peserta');
            $table->string('nama', 150);
            $table->string('email', 150)->index();
            $table->string('asal_instansi', 150)->nullable();
            $table->dateTime('dibuat_pada')->nullable();
            $table->dateTime('diubah_pada')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peserta');
    }
};
