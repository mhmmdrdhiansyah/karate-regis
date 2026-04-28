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
        'min_birth_date',
        'max_birth_date',
    ];

    protected function casts(): array
    {
        return [
            'type' => EventCategoryType::class,
            'min_birth_date' => 'date',
            'max_birth_date' => 'date',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function subCategories(): HasMany
    {
        return $this->hasMany(SubCategory::class);
    }
}
