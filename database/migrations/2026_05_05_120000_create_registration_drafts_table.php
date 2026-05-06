<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contingent_id')->constrained()->restrictOnDelete();
            $table->foreignId('event_id')->constrained()->restrictOnDelete();
            $table->enum('status', ['draft', 'converted', 'expired'])->default('draft')->notNull();
            $table->timestamps();

            $table->index(['contingent_id', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_drafts');
    }
};
