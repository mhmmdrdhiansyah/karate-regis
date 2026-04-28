<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'contingent_id',
        'event_id',
        'total_amount',
        'transfer_proof',
        'status',
        'rejection_reason',
        'verified_at',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'status' => PaymentStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    public function contingent(): BelongsTo
    {
        return $this->belongsTo(Contingent::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function isActive(): bool
    {
        return $this->status !== PaymentStatus::Cancelled;
    }

    public function canUploadProof(): bool
    {
        return in_array($this->status, [PaymentStatus::Pending, PaymentStatus::Rejected]);
    }

    public function canBeCancelledByUser(): bool
    {
        return in_array($this->status, [PaymentStatus::Pending, PaymentStatus::Rejected]);
    }
}
