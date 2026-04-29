<?php

namespace App\Models;

use App\Enums\SubCategoryGender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_category_id',
        'name',
        'gender',
        'price',
        'min_participants',
        'max_participants',
    ];

    protected function casts(): array
    {
        return [
            'gender' => SubCategoryGender::class,
            'price' => 'decimal:2',
            'min_participants' => 'integer',
            'max_participants' => 'integer',
        ];
    }

    public function eventCategory(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function isTeam(): bool
    {
        return $this->max_participants > 1;
    }

    public function hasActiveRegistrations(): bool
    {
        return $this->registrations()->whereNull('deleted_at')->exists();
    }

    public function hasPayments(): bool
    {
        return $this->registrations()->whereHas('payment')->exists();
    }

    public function canDelete(): bool
    {
        return ! $this->hasActiveRegistrations();
    }

    public function canEditPrice(): bool
    {
        return ! $this->hasPayments();
    }

    public function labelType(): string
    {
        return $this->isTeam() ? 'Beregu' : 'Individu';
    }
}
