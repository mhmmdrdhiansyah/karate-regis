<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeamGroup extends Model
{
    protected $fillable = [
        'contingent_id',
        'sub_category_id',
        'team_name',
        'team_number',
    ];

    protected function casts(): array
    {
        return [
            'team_number' => 'integer',
        ];
    }

    public function contingent(): BelongsTo
    {
        return $this->belongsTo(Contingent::class);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function draftItems(): HasMany
    {
        return $this->hasMany(RegistrationDraftItem::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * Ambil daftar participant yang tergabung di tim ini (dari draft).
     */
    public function draftParticipants()
    {
        return $this->draftItems()->with('participant');
    }
}
