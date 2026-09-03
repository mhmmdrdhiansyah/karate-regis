<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Normalisasi gambar template ID card: center-crop ke rasio CR80
 * (85.6:54 = 1.5852), downscale maksimal 1075px lebar (≈300dpi),
 * rekompres agar PDF cetak ringan. Upload rasio/nolong apa pun
 * hasilnya konsisten — bukan stretch.
 */
class IdCardImageService
{
    /** Rasio kartu CR80 (landscape). */
    private const RATIO = 85.6 / 54;

    /** Lebar maksimum hasil (85.6mm @ 300dpi ≈ 1075px). */
    private const MAX_WIDTH = 1075;

    public function normalizeAndStore(UploadedFile $file, string $dir = 'id-card-templates'): string
    {
        $src = $this->open($file->getRealPath());
        $w = imagesx($src);
        $h = imagesy($src);

        // Orientasi dari gambar sumber: portrait (h>w) atau landscape.
        // Rasio CR80 tetap (sisi panjang / sisi pendek); orientasi hanya
        // menentukan sumbu mana yang panjang.
        $portrait = $h > $w;

        // Sumbur panjang/pendek sumber: landscape long=x, portrait long=y
        [$long, $short] = $portrait ? [$h, $w] : [$w, $h];

        // 1. Center-crop ke rasio CR80 (long/short = 1.585)
        if ($long / $short > self::RATIO) {
            // terlalu panjang → crop sisi panjang
            $newLong = (int) round($short * self::RATIO);
            $newShort = $short;
            $longOffset = intdiv($long - $newLong, 2);
            $shortOffset = 0;
        } else {
            // terlalu pendek / kotak → crop sisi pendek
            $newShort = (int) round($long / self::RATIO);
            $newLong = $long;
            $shortOffset = intdiv($short - $newShort, 2);
            $longOffset = 0;
        }

        // Kembali ke koordinat x/y sumber untuk imagecopyresampled
        [$sx, $sy, $sw, $sh] = $portrait
            ? [$shortOffset, $longOffset, $newShort, $newLong]
            : [$longOffset, $shortOffset, $newLong, $newShort];

        // 2. Downscale: sisi panjang maksimal 1075px (tidak pernah upscale)
        $longSide = max($sw, $sh);
        if ($longSide > self::MAX_WIDTH) {
            $scale = self::MAX_WIDTH / $longSide;
            $sw = (int) round($sw * $scale);
            $sh = (int) round($sh * $scale);
        }
        $outW = $sw;
        $outH = $sh;

        $dst = imagecreatetruecolor($outW, $outH);


        // PNG butuh alpha; JPEG cukup background putih
        $isPng = $file->getClientOriginalExtension() === 'png'
            || str_contains(strtolower($file->getMimeType()), 'png');
        if ($isPng) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefill($dst, 0, 0, $transparent);
        } else {
            $white = imagecolorallocate($dst, 255, 255, 255);
            imagefill($dst, 0, 0, $white);
        }

        imagecopyresampled($dst, $src, 0, 0, $sx, $sy, $outW, $outH, $sw, $sh);
        imagedestroy($src);

        // 3. Simpan rekompres via Storage disk public (fake-able di test)
        $ext = $isPng ? 'png' : 'jpg';
        $path = $dir . '/' . uniqid('idcard_') . '.' . $ext;

        ob_start();
        if ($isPng) {
            imagepng($dst, null, 6, PNG_ALL_FILTERS);
        } else {
            imagejpeg($dst, null, 85);
        }
        imagedestroy($dst);
        Storage::disk('public')->put($path, ob_get_clean());

        return $path;
    }

    /**
     * Dimensi kartu (px) dari file template tersimpan + orientasinya.
     * Return ['width' => int, 'height' => int, 'portrait' => bool].
     */
    public function dimensionsOf(string $relativePath): array
    {
        $full = storage_path('app/public/' . $relativePath);
        abort_unless(file_exists($full), 404, 'File template tidak ditemukan.');

        [$w, $h] = getimagesize($full);

        return ['width' => $w, 'height' => $h, 'portrait' => $h > $w];
    }

    /** @resource */
    private function open(string $realPath)
    {
        $info = getimagesize($realPath);
        abort_unless($info, 422, 'File bukan gambar valid.');

        return match ($info[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($realPath),
            IMAGETYPE_PNG => imagecreatefrompng($realPath),
            default => abort(422, 'Hanya JPEG/PNG yang didukung.'),
        };
    }
}
