<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice — {{ $event->name }}</title>

    {{-- Brand fonts (Combat Pro). Loaded before print so they render in the PDF. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Hanken+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        @page { size: A4; margin: 0; }

        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }

        :root {
            --ink: #1b1c1c;
            --paper: #ffffff;
            --primary: #b9001c;
            --gold: #FFD700;
            --muted: #6b7280;
            --hair: #e7e5e4;
            --soft: #f7f5f4;
        }

        body {
            font-family: 'Hanken Grotesk', Arial, sans-serif;
            color: var(--ink);
            background: #d9d9d9;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* A4 sheet, centered on screen */
        .sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: var(--paper);
            position: relative;
            overflow: hidden;
        }

        @media print {
            body { background: #fff; }
            .sheet { margin: 0; box-shadow: none; }
            .no-print { display: none !important; }
        }

        /* ---------- Masthead ---------- */
        .masthead {
            background: var(--ink);
            color: #fff;
            padding: 20mm 18mm 12mm;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12mm;
        }
        .brand {
            font-family: 'Anton', sans-serif;
            font-size: 36px;
            line-height: 1;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }
        .brand .pro { color: var(--gold); }
        .brand-sub {
            margin-top: 7px;
            font-size: 9.5px;
            font-weight: 600;
            letter-spacing: 0.28em;
            color: #9ca3af;
            text-transform: uppercase;
        }
        .doc-meta { text-align: right; }
        .doc-meta .k {
            font-size: 9.5px;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: #9ca3af;
        }
        .doc-no {
            font-family: 'Anton', sans-serif;
            font-size: 22px;
            letter-spacing: 0.04em;
            margin-top: 2px;
        }
        .pill {
            display: inline-block;
            margin-top: 9px;
            background: var(--primary);
            color: #fff;
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            padding: 5px 10px;
        }

        /* ---------- Body ---------- */
        .body { padding: 12mm 18mm 10mm; }

        .parties {
            display: flex;
            justify-content: space-between;
            gap: 10mm;
            margin-bottom: 9mm;
            padding-bottom: 9mm;
            border-bottom: 2px solid var(--ink);
        }
        .party .k {
            font-size: 9px;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 4px;
        }
        .party .v { font-size: 14px; font-weight: 700; line-height: 1.25; }
        .party .sub { font-size: 11px; color: var(--muted); margin-top: 2px; }

        .sec-label {
            font-family: 'Anton', sans-serif;
            font-size: 13px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--primary);
            margin-bottom: 7px;
        }

        /* ---------- Line items ---------- */
        table.items { width: 100%; border-collapse: collapse; font-size: 11px; }
        table.items thead th {
            background: var(--ink);
            color: #fff;
            font-family: 'Anton', sans-serif;
            font-weight: 400;
            font-size: 10px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 9px 10px;
            text-align: left;
        }
        table.items thead th.num { text-align: right; }
        table.items td {
            padding: 9px 10px;
            border-bottom: 1px solid var(--hair);
            vertical-align: top;
        }
        table.items td.num {
            text-align: right;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }
        table.items tbody tr:nth-child(even) td { background: var(--soft); }
        .cat { font-weight: 700; }
        .who { color: var(--muted); font-size: 10px; margin-top: 2px; line-height: 1.35; }
        .tag {
            display: inline-block;
            border: 1px solid var(--hair);
            background: #fff;
            color: var(--muted);
            font-size: 7.5px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            padding: 2px 5px;
            margin-left: 5px;
            vertical-align: 2px;
        }

        /* ---------- Totals ---------- */
        .totals-wrap { display: flex; justify-content: flex-end; margin-top: 8mm; }
        .totals { width: 60%; }
        .totals .row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            padding: 6px 0;
            font-size: 12px;
            border-bottom: 1px dashed var(--hair);
        }
        .totals .row span:last-child { font-variant-numeric: tabular-nums; font-weight: 600; }
        .grand {
            margin-top: 9px;
            background: var(--primary);
            color: #fff;
            padding: 13px 16px;
            box-shadow: 6px 6px 0 var(--ink);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .grand .lbl {
            font-family: 'Anton', sans-serif;
            font-size: 14px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .grand .amt {
            font-family: 'Anton', sans-serif;
            font-size: 27px;
            letter-spacing: 0.02em;
            font-variant-numeric: tabular-nums;
        }

        /* ---------- Payment ---------- */
        .pay {
            margin-top: 9mm;
            border: 2px solid var(--ink);
            box-shadow: 6px 6px 0 var(--primary);
        }
        .pay .head {
            background: var(--ink);
            color: #fff;
            padding: 8px 14px;
            font-family: 'Anton', sans-serif;
            font-size: 13px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .pay .grid { display: flex; gap: 8mm; padding: 12px 14px; align-items: flex-end; }
        .pay .col { flex: 1; }
        .pay .col.k-acct { flex: 1.4; }
        .pay .col .k {
            font-size: 9px;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 3px;
        }
        .pay .bank { font-weight: 700; font-size: 14px; }
        .pay .acct {
            font-family: 'Anton', sans-serif;
            font-size: 22px;
            letter-spacing: 0.06em;
            color: var(--primary);
            font-variant-numeric: tabular-nums;
        }
        .pay .holder { font-size: 12px; font-weight: 600; }
        .pay .note {
            background: var(--soft);
            border-top: 1px solid var(--hair);
            padding: 8px 14px;
            font-size: 10px;
            line-height: 1.5;
            color: var(--ink);
        }
        .pay .note b { color: var(--primary); }

        /* ---------- Footer ---------- */
        .foot {
            margin-top: 10mm;
            padding: 8px 18mm 10mm;
            border-top: 2px solid var(--gold);
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            letter-spacing: 0.04em;
            color: var(--muted);
        }

        /* ---------- Screen-only print button ---------- */
        .print-btn {
            position: fixed;
            top: 22px;
            right: 22px;
            z-index: 99;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--ink);
            color: #fff;
            border: none;
            padding: 12px 20px;
            font-family: 'Hanken Grotesk', sans-serif;
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            cursor: pointer;
            box-shadow: 4px 4px 0 var(--primary);
        }
        .print-btn:hover { background: var(--primary); box-shadow: 4px 4px 0 var(--ink); }
        .print-btn svg { width: 16px; height: 16px; fill: currentColor; }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">
        <svg viewBox="0 0 24 24"><path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/></svg>
        Cetak / Simpan PDF
    </button>

    <div class="sheet">
        {{-- Masthead --}}
        <header class="masthead">
            <div>
                <div class="brand">COMBAT <span class="pro">PRO</span></div>
                <div class="brand-sub">Sistem Registrasi Beladiri</div>
            </div>
            <div class="doc-meta">
                <div class="k">Invoice</div>
                <div class="doc-no">#{{ str_pad($contingent->id, 6, '0', STR_PAD_LEFT) }}-{{ $event->id }}</div>
                <span class="pill">Menunggu Pembayaran</span>
            </div>
        </header>

        <main class="body">
            {{-- Billed-to + Event --}}
            <div class="parties">
                <div class="party">
                    <div class="k">Ditagihkan Kepada</div>
                    <div class="v">{{ $contingent->name }}</div>
                    <div class="sub">{{ $contingent->official_name }}</div>
                    @if ($contingent->phone)<div class="sub">{{ $contingent->phone }}</div>@endif
                </div>
                <div class="party" style="text-align: right;">
                    <div class="k">Event</div>
                    <div class="v">{{ $event->name }}</div>
                    <div class="sub">{{ $event->event_date->translatedFormat('d F Y') }}</div>
                </div>
            </div>

            {{-- Line items --}}
            <div class="sec-label">Rincian Tagihan</div>
            <table class="items">
                <thead>
                    <tr>
                        <th style="width: 40%;">Kategori</th>
                        <th style="width: 30%;">Peserta</th>
                        <th class="num" style="width: 8%;">Qty</th>
                        <th class="num" style="width: 16%;">Harga</th>
                        <th class="num" style="width: 16%;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($athleteSelections as $selection)
                        @php $sc = $selection['subCategory']; $aths = $selection['athletes']; @endphp
                        @if ($sc->isTeam())
                            @php $teams = collect($aths)->groupBy('team_group_id'); @endphp
                            @foreach ($teams as $teamId => $members)
                                @php
                                    $teamName = \App\Models\TeamGroup::find($teamId)?->team_name ?? 'Tim';
                                    $unit = (float) $sc->price;
                                    $names = collect($members)->pluck('participant.name')->implode(', ');
                                @endphp
                                <tr>
                                    <td class="cat">{{ $sc->name }} <span class="tag">Beregu</span></td>
                                    <td class="who">{{ $teamName }} — {{ $names }}</td>
                                    <td class="num">1</td>
                                    <td class="num">Rp {{ number_format($unit, 0, ',', '.') }}</td>
                                    <td class="num">Rp {{ number_format($unit, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @else
                            @php
                                $count = $aths->count();
                                $unit = (float) $sc->price;
                                $sub = $unit * $count;
                                $names = $aths->pluck('participant.name')->implode(', ');
                            @endphp
                            <tr>
                                <td class="cat">{{ $sc->name }}</td>
                                <td class="who">{{ $names }}</td>
                                <td class="num">{{ $count }}</td>
                                <td class="num">Rp {{ number_format($unit, 0, ',', '.') }}</td>
                                <td class="num">Rp {{ number_format($sub, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                    @endforeach

                    @if ($coaches->count() > 0)
                        <tr>
                            <td class="cat">Pelatih / Official <span class="tag">Official</span></td>
                            <td class="who">{{ $coaches->pluck('name')->implode(', ') }}</td>
                            <td class="num">{{ $coaches->count() }}</td>
                            <td class="num">Rp {{ number_format((float) $event->coach_fee, 0, ',', '.') }}</td>
                            <td class="num">Rp {{ number_format($totalCoachFee, 0, ',', '.') }}</td>
                        </tr>
                    @endif

                    @if ($athleteSelections->isEmpty() && $coaches->isEmpty())
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--muted); padding: 16px;">
                                Belum ada item pendaftaran pada invoice ini.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>

            {{-- Totals --}}
            <div class="totals-wrap">
                <div class="totals">
                    <div class="row"><span>Subtotal Atlet</span><span>Rp {{ number_format($totalAthleteFee, 0, ',', '.') }}</span></div>
                    @if ($coaches->count() > 0)
                        <div class="row"><span>Subtotal Pelatih</span><span>Rp {{ number_format($totalCoachFee, 0, ',', '.') }}</span></div>
                    @endif
                    <div class="row"><span>Biaya Event</span><span>Rp {{ number_format((float) $eventFee, 0, ',', '.') }}</span></div>
                    <div class="row"><span>Kode Unik (ID Transfer)</span><span>Rp {{ number_format($uniqueCode, 0, ',', '.') }}</span></div>
                    <div class="grand">
                        <span class="lbl">Total Tagihan</span>
                        <span class="amt">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- Payment --}}
            @if ($event->bank_name && $event->account_number)
                <div class="pay">
                    <div class="head">▸ Pembayaran Transfer</div>
                    <div class="grid">
                        <div class="col">
                            <div class="k">Bank</div>
                            <div class="bank">{{ $event->bank_name }}</div>
                        </div>
                        <div class="col k-acct">
                            <div class="k">Nomor Rekening</div>
                            <div class="acct">{{ $event->account_number }}</div>
                        </div>
                        <div class="col">
                            <div class="k">Atas Nama</div>
                            <div class="holder">{{ $event->account_holder }}</div>
                        </div>
                    </div>
                    <div class="note">
                        <b>Penting:</b> Transfer <b>tepat sesuai total tagihan</b>, termasuk kode unik
                        <b>{{ $uniqueCode }}</b> pada 3 digit terakhir, agar pembayaran dapat terverifikasi otomatis.
                    </div>
                </div>
            @endif
        </main>

        <footer class="foot">
            <span>Combat Pro — Invoice diterbitkan secara otomatis oleh sistem.</span>
            <span>Dicetak: {{ now()->translatedFormat('d M Y · H:i') }}</span>
        </footer>
    </div>
</body>
</html>
