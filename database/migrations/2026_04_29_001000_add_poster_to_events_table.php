<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('events', 'poster')) {
            Schema::table('events', function (Blueprint $table) {
                $table->string('poster')->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('events', 'poster')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('poster');
            });
        }
    }
};
