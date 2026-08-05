<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Support\Facades\Storage;

class Contingent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'official_name',
        'phone',
        'address',
        'province',
        'regency',
        'photo',
    ];

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/' . ltrim($this->photo, '/')) : null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function teamGroups(): HasMany
    {
        return $this->hasMany(TeamGroup::class);
    }

    public function drafts(): HasMany
    {
        return $this->hasMany(RegistrationDraft::class);
    }
}
