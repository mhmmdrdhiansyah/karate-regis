<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration_drafts', function (Blueprint $table) {
            // Kode unik pembayaran (3 digit) — di-generate sekali saat invoice
            // pertama kali ditampilkan, lalu disimpan agar tidak berubah tiap
            // refresh dan konsisten antara halaman invoice, PDF, dan total di
            // tabel payments.
            $table->integer('unique_code')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('registration_drafts', function (Blueprint $table) {
            $table->dropColumn('unique_code');
        });
    }
};
