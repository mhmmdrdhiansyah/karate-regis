<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('id_card_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete(); // NULL = fallback global
            $table->string('name');
            $table->string('image_path');                    // background kartu
            $table->json('texts')->nullable();               // blok teks, format = certificate_templates.texts
            $table->json('photo_box')->nullable();           // {x, y, w, h} persen — kotak foto
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['event_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('id_card_templates');
    }
};
