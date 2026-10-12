<?php

namespace Tests\Feature;

use App\Services\IdCardImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IdCardImageNormalizationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function upload_dipersentasi_dicrop_ke_rasio_cr80(): void
    {
        Storage::fake('public');

        // Gambar persegi 2000x2000 (rasio 1:1) — harus di-crop ke rasio CR80
        $file = UploadedFile::fake()->image('template.png', 2000, 2000);

        $path = app(IdCardImageService::class)->normalizeAndStore($file);

        Storage::disk('public')->assertExists($path);

        $info = getimagesize(Storage::disk('public')->path($path));
        $this->assertNotNull($info, 'File hasil bukan gambar valid.');

        [$w, $h] = $info;
        // Rasio CR80 = 85.6/54 = 1.5852 — toleransi pembulatan crop genap
        $this->assertEqualsWithDelta(85.6 / 54, $w / $h, 0.01,
            "Rasio hasil {$w}x{$h} bukan CR80.");
    }

    #[Test]
    public function upload_portrait_dihasilkan_kartu_portrait(): void
    {
        Storage::fake('public');

        // Kartu vertikal 543x888 (desain lanyard) — harus JANGAN di-crop jadi strip
        $file = UploadedFile::fake()->image('kartu.png', 543, 888);

        $path = app(IdCardImageService::class)->normalizeAndStore($file);

        [$w, $h] = getimagesize(Storage::disk('public')->path($path));
        // Portrait: tinggi > lebar, rasio tetap CR80 (54 : 85.6)
        $this->assertGreaterThan($w, $h, 'Hasil harus portrait (tinggi > lebar).');
        $this->assertEqualsWithDelta(85.6 / 54, $h / $w, 0.01,
            "Rasio portrait {$w}x{$h} bukan CR80.");
    }

    #[Test]
    public function upload_besar_didownscale_maksimal_1075px(): void
    {
        Storage::fake('public');

        // 4000x3000 — rasio 1.333, crop ke 1.585 lalu scale max lebar 1075
        $file = UploadedFile::fake()->image('besar.jpg', 4000, 3000);

        $path = app(IdCardImageService::class)->normalizeAndStore($file);

        [$w, $h] = getimagesize(Storage::disk('public')->path($path));
        $this->assertLessThanOrEqual(1075, $w, "Lebar {$w}px melebihi 1075.");
        $this->assertGreaterThan(0, $h);
    }

    #[Test]
    public function upload_kecil_tidak_diupscale(): void
    {
        Storage::fake('public');

        // 600x400 (rasio 1.5, kecil) — crop ringan ke 1.585, TIDAK boleh dinaikkan
        $file = UploadedFile::fake()->image('kecil.png', 600, 400);

        $path = app(IdCardImageService::class)->normalizeAndStore($file);

        [$w, $h] = getimagesize(Storage::disk('public')->path($path));
        $this->assertLessThanOrEqual(600, $w, "Gambar kecil di-upscale ke {$w}px.");
    }

    #[Test]
    public function hasil_normalisasi_ringan(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('besar.jpg', 4000, 3000);

        $path = app(IdCardImageService::class)->normalizeAndStore($file);

        $size = Storage::disk('public')->size($path);
        $this->assertLessThan(1024 * 1024, $size, "Hasil {$size} bytes (>1MB) — rekompres tidak jalan.");
    }
}
