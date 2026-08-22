<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sub_categories', function (Blueprint $table) {
            $table->string('discipline', 20)->default('lainnya')->after('category_type');
        });

        // Backfill dari nama untuk data lama
        DB::table('sub_categories')->update([
            'discipline' => DB::raw("CASE
                WHEN LOWER(name) LIKE '%kumite%' THEN 'kumite'
                WHEN LOWER(name) LIKE '%kata%' THEN 'kata'
                ELSE 'lainnya'
            END"),
        ]);
    }

    public function down(): void
    {
        Schema::table('sub_categories', function (Blueprint $table) {
            $table->dropColumn('discipline');
        });
    }
};
