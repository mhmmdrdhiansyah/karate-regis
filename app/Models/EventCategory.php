<?php

namespace App\Models;

use App\Enums\EventCategoryType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'type',
        'class_name',
        'discount_type',
        'discount_value',
        'min_birth_date',
        'max_birth_date',
    ];

    protected function casts(): array
    {
        return [
            'type' => EventCategoryType::class,
            'discount_value' => 'decimal:2',
            'min_birth_date' => 'date',
            'max_birth_date' => 'date',
        ];
    }

    public function calculateDiscountAmount(float $subCategoryPrice): float
    {
        if ($this->discount_value <= 0) {
            return 0;
        }

        if ($this->discount_type === 'percentage') {
            return ($subCategoryPrice * (float) $this->discount_value) / 100;
        }

        return (float) $this->discount_value;
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function subCategories(): HasMany
    {
        return $this->hasMany(SubCategory::class);
    }

    public function hasActiveRegistrations(): bool
    {
        return $this->subCategories()
            ->whereHas('registrations', fn($query) => $query->whereNull('deleted_at'))
            ->exists();
    }

    public function canDelete(): bool
    {
        return ! $this->hasActiveRegistrations();
    }

    public function readableBirthRange(): string
    {
        return 'Lahir: ' . $this->min_birth_date?->translatedFormat('j M Y') . ' - ' . $this->max_birth_date?->translatedFormat('j M Y');
    }
}
