<?php

namespace App\Models;

use App\Enums\ParticipantGender;
use App\Enums\ParticipantType;
use App\Enums\SubCategoryGender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'contingent_id',
        'type',
        'nik',
        'name',
        'birth_date',
        'gender',
        'provinsi',
        'institusi',
        'photo',
        'document',
        'is_verified',
        'verified_at',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => ParticipantType::class,
            'birth_date' => 'date',
            'gender' => ParticipantGender::class,
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function contingent(): BelongsTo
    {
        return $this->belongsTo(Contingent::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function scopeAthletes($query)
    {
        return $query->where('type', ParticipantType::Athlete);
    }

    public function scopeCoaches($query)
    {
        return $query->where('type', ParticipantType::Coach);
    }

    public function scopeEligibleFor($query, SubCategory $subCategory)
    {
        $eventCategory = $subCategory->eventCategory;

        return $query->athletes()
            ->where('contingent_id', auth()->user()->contingent->id)
            ->where(function ($q) use ($subCategory) {
                if ($subCategory->gender === SubCategoryGender::Mixed) {
                    $q->whereNotNull('gender');
                } else {
                    $q->where('gender', $subCategory->gender->value);
                }
            })
            ->where('birth_date', '>=', $eventCategory->min_birth_date)
            ->where('birth_date', '<=', $eventCategory->max_birth_date)
            ->whereDoesntHave('registrations', function ($q) use ($subCategory) {
                $q->where('sub_category_id', $subCategory->id);
            });
    }

    protected $appends = ['photo_url'];

    public function getPhotoUrlAttribute(): string
    {
        if (!$this->photo) {
            return asset('assets/media/avatars/blank.png');
        }

        if (str_starts_with($this->photo, 'assets/')) {
            return asset($this->photo);
        }

        return \Illuminate\Support\Facades\Storage::url($this->photo);
    }
}
