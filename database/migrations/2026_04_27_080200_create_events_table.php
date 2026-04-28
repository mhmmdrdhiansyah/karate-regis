<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name')->notNull();
            $table->date('event_date')->notNull();
            $table->dateTime('registration_deadline')->nullable();
            $table->decimal('coach_fee', 12, 2)->notNull();
            $table->enum('status', [
                'draft',
                'registration_open',
                'registration_closed',
                'ongoing',
                'completed',
            ])->default('draft')->notNull();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
