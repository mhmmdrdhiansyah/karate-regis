<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_category_id')->constrained()->restrictOnDelete();
            $table->string('name')->notNull();
            $table->enum('gender', ['M', 'F', 'Mixed'])->notNull();
            $table->decimal('price', 12, 2)->notNull();
            $table->unsignedInteger('min_participants')->default(1)->notNull();
            $table->unsignedInteger('max_participants')->default(1)->notNull();
            $table->timestamps();

            $table->index('event_category_id');
        });

        DB::statement('ALTER TABLE sub_categories ADD CONSTRAINT sub_categories_participant_range_check CHECK (min_participants <= max_participants)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE sub_categories DROP CONSTRAINT IF EXISTS sub_categories_participant_range_check');
        Schema::dropIfExists('sub_categories');
    }
};
