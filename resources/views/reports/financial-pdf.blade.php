<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan — {{ $activeEvent ? $activeEvent->name : 'Semua Event' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 12mm 12mm 12mm;
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            background: #f4f6f9;
            font-family: 'Hanken Grotesk', Arial, sans-serif;
            color: #1e293b;
            font-size: 11px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .no-print-bar {
            background: #0f172a;
            color: #ffffff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .no-print-bar button {
            background: #16a34a;
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .no-print-bar button:hover {
            background: #15803d;
        }

        .sheet {
            background: #ffffff;
            width: 297mm;
            min-height: 210mm;
            margin: 15px auto;
            padding: 15mm;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border-radius: 4px;
        }

        @media print {
            body {
                background: #ffffff;
            }
            .no-print-bar {
                display: none !important;
            }
            .sheet {
                width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
                border-radius: 0;
            }
        }

        /* Header / Masthead */
        .report-header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 14px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .report-title-box h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .report-title-box h2 {
            margin: 4px 0 0 0;
            font-size: 13px;
            font-weight: 600;
            color: #16a34a;
        }

        .report-meta-box {
            text-align: right;
            font-size: 10px;
            color: #64748b;
        }

        .report-meta-box .date {
            font-weight: 700;
            color: #1e293b;
        }

        /* Filter Context Badges */
        .filter-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 10px;
        }

        .badge-item {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 3px 8px;
            border-radius: 4px;
            color: #334155;
        }

        .badge-item strong {
            color: #0f172a;
        }

        /* Financial Summary Widgets Top */
        .summary-widgets {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 16px;
        }

        .widget-card {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 12px;
            text-align: center;
        }

        .widget-card.green {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }

        .widget-card.amber {
            background: #fffbeb;
            border-color: #fde68a;
        }

        .widget-label {
            font-size: 9.5px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .widget-value {
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 3px;
        }

        .widget-card.green .widget-value {
            color: #15803d;
        }

        .widget-card.amber .widget-value {
            color: #b45309;
        }

        /* Data Table */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 10px;
        }

        .report-table th {
            background: #0f172a;
            color: #ffffff;
            text-align: left;
            padding: 7px 8px;
            font-weight: 700;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border: 1px solid #0f172a;
        }

        .report-table td {
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }

        .report-table tr:nth-child(even) {
            background: #f8fafc;
        }

        .report-table tfoot td {
            background: #e2e8f0;
            font-weight: 800;
            font-size: 10.5px;
            border-top: 2px solid #0f172a;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: 700; }

        .tag-status-verified {
            background: #dcfce7;
            color: #15803d;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .tag-status-pending {
            background: #fef3c7;
            color: #b45309;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .tag-status-rejected {
            background: #fee2e2;
            color: #b91c1c;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .tag-status-cancelled {
            background: #f1f5f9;
            color: #64748b;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 4px;
        }

        /* Signatures Footer */
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            page-break-inside: avoid;
        }

        .signature-box {
            width: 30%;
            text-align: center;
            font-size: 10px;
        }

        .signature-space {
            height: 50px;
        }

        .signature-line {
            font-weight: 700;
            border-bottom: 1px solid #0f172a;
            display: inline-block;
            min-width: 160px;
            padding-bottom: 2px;
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <div>
            <strong>Pratinjau Laporan Keuangan Event</strong>
            <span style="opacity: 0.7; font-size: 11px; margin-left: 10px;">(Format A4 Landscape)</span>
        </div>
        <div style="display: flex; gap: 10px;">
            <button onclick="window.print()">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                    <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm1 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-2z"/>
                </svg>
                Cetak / Simpan PDF
            </button>
            <button onclick="window.close()" style="background: #475569;">Tutup</button>
        </div>
    </div>

    <div class="sheet">
        <!-- Header Kop -->
        <div class="report-header">
            <div class="report-title-box">
                <h1>Laporan Keuangan & Rekapitulasi Pembayaran</h1>
                <h2>{{ $activeEvent ? $activeEvent->name : 'SEMUA EVENT PERTANDINGAN' }}</h2>
            </div>
            <div class="report-meta-box">
                <div>Tanggal Cetak: <span class="date">{{ $printedAt }}</span></div>
                <div>Status Dokumen: <span style="color: #16a34a; font-weight: 700;">Official Financial Report</span></div>
            </div>
        </div>

        <!-- Filter Context Badges -->
        <div class="filter-badges">
            <div class="badge-item">Event: <strong>{{ $activeEvent ? $activeEvent->name : 'Semua Event' }}</strong></div>
            <div class="badge-item">Kontingen: <strong>{{ $activeContingent ? $activeContingent->name : 'Semua Kontingen' }}</strong></div>
            @if(request('status'))
                <div class="badge-item">Status: <strong>{{ ucfirst(request('status')) }}</strong></div>
            @endif
            @if(request('start_date') || request('end_date'))
                <div class="badge-item">Periode: <strong>{{ request('start_date') ?: 'Awal' }} s/d {{ request('end_date') ?: 'Hari Ini' }}</strong></div>
            @endif
            @if(request('search'))
                <div class="badge-item">Pencarian: <strong>"{{ request('search') }}"</strong></div>
            @endif
        </div>

        <!-- Financial Summary Widgets -->
        <div class="summary-widgets">
            <div class="widget-card green">
                <div class="widget-label">Pemasukan Lunas (Verified)</div>
                <div class="widget-value">Rp {{ number_format($summary['total_verified_amount'], 0, ',', '.') }}</div>
            </div>
            <div class="widget-card amber">
                <div class="widget-label">Pending (Menunggu Pembayaran)</div>
                <div class="widget-value">Rp {{ number_format($summary['total_pending_amount'], 0, ',', '.') }}</div>
            </div>
            <div class="widget-card">
                <div class="widget-label">Total Potongan Diskon</div>
                <div class="widget-value">Rp {{ number_format($summary['total_discount_amount'], 0, ',', '.') }}</div>
            </div>
            <div class="widget-card">
                <div class="widget-label">Total Invoice & Entry</div>
                <div class="widget-value" style="font-size: 13px;">
                    {{ $summary['total_invoices'] }} Invoice ({{ $summary['total_athletes'] }} Atlet, {{ $summary['total_teams'] }} Tim)
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 30px;" class="text-center">No</th>
                    <th style="width: 100px;">No. Invoice</th>
                    <th style="width: 100px;" class="text-center">Tanggal</th>
                    <th style="width: 160px;">Kontingen</th>
                    <th>Event</th>
                    <th style="width: 140px;">Rincian Entry</th>
                    <th style="width: 100px;" class="text-right">Subtotal (Rp)</th>
                    <th style="width: 90px;" class="text-right">Diskon (Rp)</th>
                    <th style="width: 110px;" class="text-right">Total Bayar (Rp)</th>
                    <th style="width: 80px;" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $idx => $p)
                    <tr>
                        <td class="text-center font-bold">{{ $idx + 1 }}</td>
                        <td class="font-bold">{{ $p->invoice_number }}</td>
                        <td class="text-center">{{ $p->date_formatted }}</td>
                        <td><div class="font-bold">{{ $p->contingent_name }}</div></td>
                        <td>{{ $p->event_name }}</td>
                        <td>{{ $p->entry_summary }}</td>
                        <td class="text-right">{{ number_format($p->subtotal_amount, 0, ',', '.') }}</td>
                        <td class="text-right" style="color: #dc2626;">{{ $p->total_discount > 0 ? number_format($p->total_discount, 0, ',', '.') : '-' }}</td>
                        <td class="text-right font-bold">{{ number_format($p->total_amount, 0, ',', '.') }}</td>
                        <td class="text-center">
                            @if($p->status_raw === 'verified')
                                <span class="tag-status-verified">Verified</span>
                            @elseif($p->status_raw === 'pending')
                                <span class="tag-status-pending">Pending</span>
                            @elseif($p->status_raw === 'rejected')
                                <span class="tag-status-rejected">Rejected</span>
                            @else
                                <span class="tag-status-cancelled">Cancelled</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center" style="padding: 20px; color: #94a3b8;">
                            Tidak ada transaksi pembayaran yang ditemukan sesuai kriteria filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="text-right font-bold">TOTAL REKAPITULASI ({{ count($payments) }} Transaksi)</td>
                    <td class="text-right font-bold">Rp {{ number_format($summary['total_subtotal_amount'], 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: #dc2626;">Rp {{ number_format($summary['total_discount_amount'], 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: #16a34a;">Rp {{ number_format($summary['total_grand_amount'], 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <!-- Signatures Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div>Bendahara Panitia Pelaksana</div>
                <div class="signature-space"></div>
                <div class="signature-line">( ............................................................ )</div>
                <div style="font-size: 9px; color: #64748b; margin-top: 3px;">Tanda Tangan & Nama Terang</div>
            </div>

            <div class="signature-box">
                <div>Ketua Pelaksana Event</div>
                <div class="signature-space"></div>
                <div class="signature-line">( ............................................................ )</div>
                <div style="font-size: 9px; color: #64748b; margin-top: 3px;">Tanda Tangan & Nama Terang</div>
            </div>
        </div>
    </div>

</body>
</html>
