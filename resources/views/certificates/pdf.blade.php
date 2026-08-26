@php
    [$w, $h] = $template->orientation === 'portrait' ? [210, 297] : [297, 210]; // mm A4
    $pos = fn (float $pct, float $total) => ($pct / 100) * $total . 'mm';
@endphp
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4 {{ $template->orientation }}; margin: 0; }
        @font-face { font-family: 'GreatVibes'; src: url('{{ public_path('fonts/certificate/GreatVibes-Regular.ttf') }}'); }
        @font-face { font-family: 'DancingScript'; src: url('{{ public_path('fonts/certificate/DancingScript.ttf') }}'); }
        @font-face { font-family: 'Caveat'; src: url('{{ public_path('fonts/certificate/Caveat.ttf') }}'); }
        body { margin: 0; padding: 0; }
        .page { position: relative; width: {{ $w }}mm; height: {{ $h }}mm; }
        .page img.bg { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
        .txt { position: absolute; transform: translate(-50%, -50%); white-space: nowrap;
               text-align: center; color: #000; font-family: sans-serif; }
    </style>
</head>
<body>
    <div class="page">
        <img class="bg" src="{{ $imageRealPath }}" alt="">
        @foreach ($template->texts as $t)
            @php
                // key sama dengan dropdown admin; handwriting = font TTF @font-face di atas
                $pdfFonts = ['times' => 'serif', 'helvetica' => 'sans-serif', 'arial' => 'sans-serif', 'courier' => 'monospace',
                             'greatvibes' => 'GreatVibes', 'dancingscript' => 'DancingScript', 'caveat' => 'Caveat'];
                $pdfFont = $pdfFonts[$t['font_family'] ?? 'times'] ?? 'serif';
            @endphp
            <div class="txt" style="left: {{ $pos($t['x'], $w) }}; top: {{ $pos($t['y'], $h) }};
                 font-size: {{ ($t['font_size'] / 100) * $h }}mm; font-family: {{ $pdfFont }};
                 color: {{ $t['color'] ?? '#000000' }};{{ !empty($t['bold']) ? ' font-weight: bold;' : '' }}">
                {{ strtr($t['content'], $replacements) }}
            </div>
        @endforeach
    </div>
</body>
</html>
