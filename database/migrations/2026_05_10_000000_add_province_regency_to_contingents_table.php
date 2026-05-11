<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contingents', function (Blueprint $table) {
            $table->string('province')->nullable()->after('address');
            $table->string('regency')->nullable()->after('province');
        });
    }

    public function down(): void
    {
        Schema::table('contingents', function (Blueprint $table) {
            $table->dropColumn(['province', 'regency']);
        });
    }
};
