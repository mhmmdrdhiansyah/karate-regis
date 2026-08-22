@php
    [$w, $h] = $template->orientation === 'portrait' ? [210, 297] : [297, 210]; // mm A4
    $pos = fn (float $pct, float $total) => ($pct / 100) * $total . 'mm';
@endphp
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4 {{ $template->orientation }}; margin: 0; }
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
        <div class="txt" style="left: {{ $pos($template->name_x, $w) }}; top: {{ $pos($template->name_y, $h) }};
             font-size: {{ ($template->name_font_size / 100) * $h }}mm; font-weight: bold;">
            {{ $participantName }}
        </div>
        <div class="txt" style="left: {{ $pos($template->category_x, $w) }}; top: {{ $pos($template->category_y, $h) }};
             font-size: {{ ($template->category_font_size / 100) * $h }}mm;">
            {{ $category }}
        </div>
        <div class="txt" style="left: {{ $pos($template->status_x, $w) }}; top: {{ $pos($template->status_y, $h) }};
             font-size: {{ ($template->status_font_size / 100) * $h }}mm; font-weight: bold;">
            {{ $status }}
        </div>
    </div>
</body>
</html>
