<?php

namespace App\Models;

use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'participant_id',
        'payment_id',
        'sub_category_id',
        'status_berkas',
        'rejection_reason',
        'verified_at',
        'verified_by',
        'team_group_id',
    ];

    protected function casts(): array
    {
        return [
            'status_berkas' => RegistrationStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function teamGroup(): BelongsTo
    {
        return $this->belongsTo(TeamGroup::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function result(): HasOne
    {
        return $this->hasOne(Result::class);
    }

    public function isForCoach(): bool
    {
        return $this->sub_category_id === null;
    }
}
