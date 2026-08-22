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
            // Posisi & ukuran teks dalam PERSEN (0-100) relatif lebar/tinggi gambar — resolusi-independen
            $table->decimal('name_x', 5, 2)->default(50);
            $table->decimal('name_y', 5, 2)->default(45);
            $table->decimal('name_font_size', 5, 2)->default(5);   // persen dari tinggi gambar
            $table->decimal('category_x', 5, 2)->default(50);
            $table->decimal('category_y', 5, 2)->default(58);
            $table->decimal('category_font_size', 5, 2)->default(2.8);
            $table->decimal('status_x', 5, 2)->default(50);
            $table->decimal('status_y', 5, 2)->default(65);
            $table->decimal('status_font_size', 5, 2)->default(3.5);
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
