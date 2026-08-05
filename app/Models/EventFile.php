<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'file_path',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
