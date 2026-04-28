<?php

namespace App\Models;

use App\Enums\MedalType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'medal_type',
    ];

    protected function casts(): array
    {
        return [
            'medal_type' => MedalType::class,
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
}
