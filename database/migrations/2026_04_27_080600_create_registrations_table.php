<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained()->restrictOnDelete();
            $table->foreignId('payment_id')->constrained()->restrictOnDelete();
            $table->foreignId('sub_category_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->enum('status_berkas', ['unsubmitted', 'pending_review', 'verified', 'rejected'])
                ->default('unsubmitted')
                ->notNull();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('status_berkas');
            $table->index(['participant_id', 'sub_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
