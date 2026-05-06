<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('registration_draft_items', function (Blueprint $table) {
            $table->foreignId('team_group_id')->nullable()->after('sub_category_id')
                  ->constrained('team_groups')->nullOnDelete();
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->foreignId('team_group_id')->nullable()->after('sub_category_id')
                  ->constrained('team_groups')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registration_draft_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('team_group_id');
        });
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('team_group_id');
        });
    }
};
