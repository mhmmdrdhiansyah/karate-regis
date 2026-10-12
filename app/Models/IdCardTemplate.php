<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdCardTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'image_path',
        'texts',
        'photo_box',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'event_id' => 'integer',
            'is_active' => 'boolean',
            'texts' => 'array',
            'photo_box' => 'array',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Blok teks default kartu — dipakai form admin saat template baru.
     */
    public const DEFAULT_TEXTS = [
        ['content' => '{nama}', 'x' => 50, 'y' => 40, 'font_size' => 7, 'bold' => true, 'font_family' => 'helvetica', 'color' => '#000000'],
        ['content' => '{kontingen}', 'x' => 50, 'y' => 55, 'font_size' => 5, 'bold' => false, 'font_family' => 'helvetica', 'color' => '#000000'],
        ['content' => '{kategori}', 'x' => 50, 'y' => 70, 'font_size' => 4, 'bold' => false, 'font_family' => 'helvetica', 'color' => '#000000'],
    ];

    public const DEFAULT_PHOTO_BOX = ['x' => 15, 'y' => 50, 'w' => 22, 'h' => 40];

    public function getTextsAttribute(): array
    {
        $raw = json_decode($this->attributes['texts'] ?? 'null', true);

        if (! $raw) {
            return static::DEFAULT_TEXTS;
        }

        return array_map(
            fn (array $text) => $text + ['font_family' => 'helvetica', 'color' => '#000000', 'bold' => false],
            $raw,
        );
    }

    public function getPhotoBoxAttribute(): array
    {
        $raw = json_decode($this->attributes['photo_box'] ?? 'null', true);

        return is_array($raw) ? $raw + static::DEFAULT_PHOTO_BOX : static::DEFAULT_PHOTO_BOX;
    }

    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . ltrim($this->image_path, '/'));
    }
}
