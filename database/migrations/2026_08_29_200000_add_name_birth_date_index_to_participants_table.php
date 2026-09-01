<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            // Index untuk lookup sertifikat by nama + tanggal lahir.
            $table->index(['birth_date', 'name'], 'participants_birth_date_name_index');
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropIndex('participants_birth_date_name_index');
        });
    }
};
