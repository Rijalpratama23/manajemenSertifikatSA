<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_sertifikat', function (Blueprint $table) {
            $table->increments('id_jenis');
            $table->string('kode', 10)->unique();
            $table->string('nama_jenis', 100);
            $table->boolean('aktif')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_sertifikat');
    }
};
