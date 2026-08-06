<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('results', function (Blueprint $table) {
            $table->string('rank_name')->nullable()->after('registration_id');
        });
        
        DB::statement("ALTER TABLE results MODIFY COLUMN medal_type ENUM('Gold', 'Silver', 'Bronze') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE results MODIFY COLUMN medal_type ENUM('Gold', 'Silver', 'Bronze') NOT NULL");
        Schema::table('results', function (Blueprint $table) {
            $table->dropColumn('rank_name');
        });
    }
};

