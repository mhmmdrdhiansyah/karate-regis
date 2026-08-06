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
        Schema::table('event_categories', function (Blueprint $table) {
            $table->string('discount_type')->default('fixed')->after('class_name');
            $table->decimal('discount_value', 12, 2)->default(0)->after('discount_type');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('total_discount', 12, 2)->default(0)->after('total_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_categories', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('total_discount');
        });
    }
};
