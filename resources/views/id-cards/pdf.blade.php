@php
    // Kartu CR80 — ukuran dari controller (portrait atau landscape).
    $w = $cardW ?? 85.6;
    $h = $cardH ?? 54;
    $pos = fn (float $pct, float $total) => ($pct / 100) * $total . 'mm';
    $box = $template->photo_box ?? App\Models\IdCardTemplate::DEFAULT_PHOTO_BOX;
@endphp
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { size: {{ $w }}mm {{ $h }}mm; margin: 0; }
        @font-face { font-family: 'GreatVibes'; src: url('{{ public_path('fonts/certificate/GreatVibes-Regular.ttf') }}'); }
        body { margin: 0; padding: 0; }
        .page { position: relative; width: {{ $w }}mm; height: {{ $h }}mm; }
        .page img.bg { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
        .txt {
            position: absolute;
            transform: translate(-50%, -50%);
            white-space: nowrap;
            text-align: center;
            color: #000;
            font-family: sans-serif;
        }
        .photo {
            position: absolute;
            border: 0.3mm solid #999;
            overflow: hidden;
        }
        .photo img { width: 100%; height: 100%; object-fit: cover; }
        .photo .placeholder {
            width: 100%;
            height: 100%;
            background: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 3mm;
        }
    </style>
</head>
<body>
    <div class="page">
        <img class="bg" src="{{ $imageRealPath }}" alt="">

        <div class="photo" style="left: {{ $pos($box['x'], $w) }}; top: {{ $pos($box['y'], $h) }}; width: {{ $pos($box['w'], $w) }}; height: {{ $pos($box['h'], $h) }};">
            @if ($photoRealPath)
                <img src="{{ $photoRealPath }}" alt="">
            @else
                <div class="placeholder">FOTO</div>
            @endif
        </div>

        @foreach ($template->texts ?? [] as $t)
            @php
                $pdfFonts = [
                    'times' => 'serif',
                    'helvetica' => 'sans-serif',
                    'arial' => 'sans-serif',
                    'courier' => 'monospace',
                    'greatvibes' => 'GreatVibes',
                ];
                $pdfFont = $pdfFonts[$t['font_family'] ?? 'times'] ?? 'serif';
            @endphp
            <div class="txt" style="left: {{ $pos($t['x'], $w) }}; top: {{ $pos($t['y'], $h) }}; font-size: {{ ($t['font_size'] / 100) * $h }}mm; font-family: {{ $pdfFont }}; color: {{ $t['color'] ?? '#000000' }};{{ !empty($t['bold']) ? ' font-weight: bold;' : '' }}">
                {{ strtr($t['content'], [
                    '{nama}' => $participant->name,
                    '{nik}' => $participant->nik,
                    '{kontingen}' => $participant->contingent?->name ?? '',
                    '{event}' => $card['event']->name,
                    '{kategori}' => $card['categories'],
                ]) }}
            </div>
        @endforeach
    </div>
</body>
</html>
