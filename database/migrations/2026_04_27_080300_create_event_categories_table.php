<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->restrictOnDelete();
            $table->enum('type', ['Open', 'Festival'])->notNull();
            $table->string('class_name')->notNull();
            $table->date('min_birth_date')->notNull();
            $table->date('max_birth_date')->notNull();
            $table->timestamps();

            $table->index('event_id');
        });

        DB::statement('ALTER TABLE event_categories ADD CONSTRAINT event_categories_date_range_check CHECK (min_birth_date <= max_birth_date)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE event_categories DROP CONSTRAINT IF EXISTS event_categories_date_range_check');
        Schema::dropIfExists('event_categories');
    }
};
