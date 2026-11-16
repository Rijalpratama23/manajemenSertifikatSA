<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sertifikat', function (Blueprint $table) {
            $table->string('pdf_status', 20)->default('pending')->after('berkas_pdf');
        });

        DB::table('sertifikat')
            ->whereNotNull('berkas_pdf')
            ->update(['pdf_status' => 'completed']);
    }

    public function down(): void
    {
        Schema::table('sertifikat', function (Blueprint $table) {
            $table->dropColumn('pdf_status');
        });
    }
};
