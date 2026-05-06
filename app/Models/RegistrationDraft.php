<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegistrationDraft extends Model
{
    use HasFactory;

    protected $fillable = [
        'contingent_id',
        'event_id',
        'status',
    ];

    public function contingent(): BelongsTo
    {
        return $this->belongsTo(Contingent::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RegistrationDraftItem::class);
    }
}
