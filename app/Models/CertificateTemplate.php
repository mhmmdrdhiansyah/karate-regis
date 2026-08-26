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
        'texts',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'event_id' => 'integer',
            'scope' => CertificateScope::class,
            'is_active' => 'boolean',
            'texts' => 'array',
        ];
    }

    public const DEFAULT_TEXTS = [
        ['content' => '{nama}', 'x' => 50, 'y' => 45, 'font_size' => 5, 'bold' => true, 'font_family' => 'times', 'color' => '#000000'],
        ['content' => '{kategori}', 'x' => 50, 'y' => 58, 'font_size' => 2.8, 'bold' => false, 'font_family' => 'helvetica', 'color' => '#000000'],
        ['content' => '{status}', 'x' => 50, 'y' => 65, 'font_size' => 3.5, 'bold' => true, 'font_family' => 'times', 'color' => '#000000'],
    ];

    public function getTextsAttribute(): array
    {
        $raw = json_decode($this->attributes['texts'] ?? 'null', true);
        if (! $raw) {
            return static::DEFAULT_TEXTS;
        }

        // Isi default utk baris lama yang belum punya font/warna
        return array_map(
            fn (array $t) => $t + ['font_family' => 'times', 'color' => '#000000'],
            $raw,
        );
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . ltrim($this->image_path, '/'));
    }
}
