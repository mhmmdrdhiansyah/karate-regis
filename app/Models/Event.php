<?php

namespace App\Models;

use App\Enums\EventStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'event_date',
        'registration_deadline',
        'coach_fee',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'registration_deadline' => 'datetime',
            'coach_fee' => 'decimal:2',
            'status' => EventStatus::class,
        ];
    }

    public function categories(): HasMany
    {
        return $this->hasMany(EventCategory::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
