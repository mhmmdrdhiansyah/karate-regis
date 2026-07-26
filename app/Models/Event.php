<?php

namespace App\Models;

use App\Enums\EventStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'poster',
        'bank_name',
        'account_number',
        'account_holder',
        'event_date',
        'registration_deadline',
        'coach_fee',
        'event_fee',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'registration_deadline' => 'datetime',
            'coach_fee' => 'decimal:2',
            'event_fee' => 'decimal:2',
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

    public function allowedStatusTransitions(): array
    {
        return [
            EventStatus::Draft->value => [EventStatus::RegistrationOpen->value],
            EventStatus::RegistrationOpen->value => [EventStatus::RegistrationClosed->value],
            EventStatus::RegistrationClosed->value => [EventStatus::Ongoing->value],
            EventStatus::Ongoing->value => [EventStatus::Completed->value],
            EventStatus::Completed->value => [],
        ];
    }

    public function canTransitionTo(EventStatus $status): bool
    {
        return in_array($status->value, $this->allowedStatusTransitions()[$this->status->value] ?? [], true);
    }

    public function isLocked(): bool
    {
        return in_array($this->status, [EventStatus::Ongoing, EventStatus::Completed], true);
    }

    public function canEditImportantFields(): bool
    {
        return ! $this->isLocked();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            EventStatus::Draft => 'Draft',
            EventStatus::RegistrationOpen => 'Registration Open',
            EventStatus::RegistrationClosed => 'Registration Closed',
            EventStatus::Ongoing => 'Ongoing',
            EventStatus::Completed => 'Completed',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            EventStatus::Draft => 'badge-light-secondary',
            EventStatus::RegistrationOpen => 'badge-light-success',
            EventStatus::RegistrationClosed => 'badge-light-warning',
            EventStatus::Ongoing => 'badge-light-primary',
            EventStatus::Completed => 'badge-light-dark',
        };
    }

    /**
     * Get formatted event date for display.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->event_date->format('d M Y'); // e.g., "12 OKT 2024"
    }

    /**
     * Get the URL for the event poster/image.
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->poster) {
            // If poster is stored as path in storage
            if (filter_var($this->poster, FILTER_VALIDATE_URL)) {
                return $this->poster;
            }
            // If poster is a storage path
            if (Storage::disk('public')->exists($this->poster)) {
                return Storage::disk('public')->url($this->poster);
            }
            // If poster is relative path in assets
            if (file_exists(public_path('assets/' . $this->poster))) {
                return asset('assets/' . $this->poster);
            }
        }

        // Default fallback image
        return 'https://images.unsplash.com/photo-1555597673-b21d5c935865?w=800&q=80';
    }
}
