<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contingent_id')->constrained()->restrictOnDelete();
            $table->foreignId('event_id')->constrained()->restrictOnDelete();
            $table->decimal('total_amount', 12, 2)->notNull();
            $table->string('transfer_proof')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected', 'cancelled'])
                ->default('pending')
                ->notNull();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index(['contingent_id', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
