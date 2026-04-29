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
        'poster',
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
}
