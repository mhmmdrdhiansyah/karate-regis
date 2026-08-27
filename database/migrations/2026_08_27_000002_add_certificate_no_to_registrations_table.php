<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nomor urut sertifikat per event — diisi SEKALI saat generate PDF pertama
     * (placeholder {xxx}), lalu terkunci: hapus/ubah registration lain tidak
     * menggeser nomor yang sudah tercetak.
     */
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->unsignedInteger('certificate_no')->nullable()->after('status_berkas');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn('certificate_no');
        });
    }
};
