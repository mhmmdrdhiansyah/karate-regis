<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->restrictOnDelete();
            $table->enum('medal_type', ['Gold', 'Silver', 'Bronze'])->notNull();
            $table->timestamps();

            $table->index('medal_type');
            $table->index('registration_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
