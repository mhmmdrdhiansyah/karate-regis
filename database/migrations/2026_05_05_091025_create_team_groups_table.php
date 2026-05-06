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
        Schema::create('team_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contingent_id')->constrained()->restrictOnDelete();
            $table->foreignId('sub_category_id')->constrained()->restrictOnDelete();
            $table->string('team_name');           // "Tim A", "Tim B"
            $table->unsignedSmallInteger('team_number')->default(1); // urutan: 1, 2, 3
            $table->timestamps();

            // Unique: 1 kontingen tidak boleh punya 2 tim dengan nomor sama di sub-kategori sama
            $table->unique(['contingent_id', 'sub_category_id', 'team_number']);
            $table->index('sub_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_groups');
    }
};
