<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contingent_id')->constrained()->restrictOnDelete();
            $table->enum('type', ['athlete', 'coach', 'official'])->notNull();
            $table->string('nik', 16)->nullable()->unique();
            $table->string('name')->notNull();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['M', 'F'])->nullable();
            $table->string('provinsi')->nullable();
            $table->string('institusi')->nullable();
            $table->string('photo')->notNull();
            $table->string('document')->nullable();
            $table->boolean('is_verified')->default(false)->notNull();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['type', 'gender', 'birth_date']);
            $table->index('contingent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
