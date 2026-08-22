<?php

namespace App\Models;

use App\Enums\CertificateScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CertificateTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'scope',
        'image_path',
        'orientation',
        'name_x',
        'name_y',
        'name_font_size',
        'category_x',
        'category_y',
        'category_font_size',
        'status_x',
        'status_y',
        'status_font_size',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'event_id' => 'integer',
            'scope' => CertificateScope::class,
            'is_active' => 'boolean',
            'name_x' => 'decimal:2',
            'name_y' => 'decimal:2',
            'name_font_size' => 'decimal:2',
            'category_x' => 'decimal:2',
            'category_y' => 'decimal:2',
            'category_font_size' => 'decimal:2',
            'status_x' => 'decimal:2',
            'status_y' => 'decimal:2',
            'status_font_size' => 'decimal:2',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function getImageUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->image_path);
    }
}
