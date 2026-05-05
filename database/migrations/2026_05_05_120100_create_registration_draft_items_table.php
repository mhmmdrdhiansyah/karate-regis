<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_draft_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_draft_id')->constrained('registration_drafts')->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained()->restrictOnDelete();
            $table->foreignId('sub_category_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index('registration_draft_id');
            $table->index(['participant_id', 'sub_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_draft_items');
    }
};
