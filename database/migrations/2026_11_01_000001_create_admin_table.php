<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin', function (Blueprint $table) {
            $table->increments('id_admin');
            $table->string('nama', 100);
            $table->string('username', 50)->unique();
            $table->string('password_hash', 255);
            $table->dateTime('dibuat_pada')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin');
    }
};
