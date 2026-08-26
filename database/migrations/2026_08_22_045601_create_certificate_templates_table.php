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
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete(); // null = fallback global
            $table->string('name');                    // label admin, mis. "Sertifikat Juara Emas"
            $table->string('scope');                   // champion_gold|champion_silver|champion_bronze|champion_other|participant|fallback
            $table->string('image_path');              // path di disk 'public'
            $table->string('orientation', 20)->default('landscape'); // portrait|landscape
            // Daftar blok teks fleksibel: [{content, x, y, font_size, bold}] —
            // content = teks bebas + placeholder {nama} {kategori} {status} {event} {kontingen};
            // x/y/font_size dalam PERSEN (0-100) relatif lebar/tinggi gambar — resolusi-independen
            $table->json('texts')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['event_id', 'scope', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_templates');
    }
};
