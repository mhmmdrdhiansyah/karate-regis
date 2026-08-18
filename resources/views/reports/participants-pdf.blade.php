<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Peserta Terdaftar — {{ $activeEvent ? $activeEvent->name : 'Semua Event' }}</title>

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
            background: #2563eb;
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
            background: #1d4ed8;
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
            color: #2563eb;
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

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: 700; }

        .tag-gender-m {
            display: inline-block;
            background: #dbeafe;
            color: #1e40af;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .tag-gender-f {
            display: inline-block;
            background: #fce7f3;
            color: #9d174d;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .tag-status-verified {
            color: #15803d;
            font-weight: 700;
        }

        .tag-status-pending {
            color: #b45309;
            font-weight: 600;
        }

        .tag-status-rejected {
            color: #b91c1c;
            font-weight: 700;
        }

        /* Summary Box & Signatures */
        .footer-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-top: 16px;
            page-break-inside: avoid;
        }

        .summary-box {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 14px;
            width: 55%;
        }

        .summary-box h4 {
            margin: 0 0 8px 0;
            font-size: 11px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .summary-item {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 6px 10px;
            border-radius: 4px;
            text-align: center;
        }

        .summary-item .label {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
        }

        .summary-item .value {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 2px;
        }

        .signature-box {
            width: 35%;
            text-align: center;
            font-size: 10px;
        }

        .signature-space {
            height: 45px;
        }

        .signature-line {
            font-weight: 700;
            border-bottom: 1px solid #0f172a;
            display: inline-block;
            min-width: 150px;
            padding-bottom: 2px;
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <div>
            <strong>Pratinjau Dokumen Cetak Laporan Peserta</strong>
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
                <h1>Laporan Peserta Terdaftar</h1>
                <h2>{{ $activeEvent ? $activeEvent->name : 'SEMUA EVENT PERTANDINGAN' }}</h2>
            </div>
            <div class="report-meta-box">
                <div>Tanggal Cetak: <span class="date">{{ $printedAt }}</span></div>
                <div>Status Laporan: <span style="color: #16a34a; font-weight: 700;">Official Event Report</span></div>
            </div>
        </div>

        <!-- Filter Context Badges -->
        <div class="filter-badges">
            <div class="badge-item">Event: <strong>{{ $activeEvent ? $activeEvent->name : 'Semua Event' }}</strong></div>
            <div class="badge-item">Kontingen: <strong>{{ $activeContingent ? $activeContingent->name : 'Semua Kontingen' }}</strong></div>
            @if(request('category_type'))
                <div class="badge-item">Jenis: <strong>{{ ucfirst(request('category_type')) }}</strong></div>
            @endif
            @if(request('gender'))
                <div class="badge-item">Gender: <strong>{{ request('gender') == 'M' ? 'Putra' : (request('gender') == 'F' ? 'Putri' : 'Campuran') }}</strong></div>
            @endif
            @if(request('status_berkas'))
                <div class="badge-item">Status Berkas: <strong>{{ ucfirst(request('status_berkas')) }}</strong></div>
            @endif
            @if(request('search'))
                <div class="badge-item">Pencarian: <strong>"{{ request('search') }}"</strong></div>
            @endif
        </div>

        <!-- Data Table -->
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 30px;" class="text-center">No</th>
                    <th style="width: 170px;">Nama Peserta</th>
                    <th style="width: 110px;">Perguruan</th>
                    <th style="width: 160px;">Kontingen</th>
                    <th style="width: 40px;" class="text-center">L/P</th>
                    <th style="width: 90px;" class="text-center">Tgl Lahir / Age</th>
                    <th>Kelas Pertandingan</th>
                    <th style="width: 110px;">Tim / Nama Tim</th>
                    <th style="width: 80px;" class="text-center">Status Berkas</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registrations as $idx => $reg)
                    <tr>
                        <td class="text-center font-bold">{{ $idx + 1 }}</td>
                        <td>
                            <div class="font-bold">{{ $reg->last_name }}</div>
                        </td>
                        <td>{{ $reg->first_name ?: '-' }}</td>
                        <td>{{ $reg->full_name_kontingen }}</td>
                        <td class="text-center">
                            @if($reg->sex === 'M' || $reg->sex === 'L')
                                <span class="tag-gender-m">M</span>
                            @elseif($reg->sex === 'F' || $reg->sex === 'P')
                                <span class="tag-gender-f">F</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">{{ $reg->age }}</td>
                        <td>
                            <div class="font-bold" style="color: #0f172a;">{{ $reg->kelas }}</div>
                        </td>
                        <td>
                            @if($reg->team === 't')
                                <span class="font-bold" style="color: #2563eb;">[Tim] {{ $reg->name_team ?: '-' }}</span>
                            @else
                                <span style="color: #94a3b8;">Perorangan</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($reg->status_berkas === 'verified')
                                <span class="tag-status-verified">Verified</span>
                            @elseif($reg->status_berkas === 'rejected')
                                <span class="tag-status-rejected">Rejected</span>
                            @else
                                <span class="tag-status-pending">Pending</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 20px; color: #94a3b8;">
                            Tidak ada data peserta yang terdaftar sesuai kriteria filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Footer Section (Summary & Signatures) -->
        <div class="footer-section">
            <div class="summary-box">
                <h4>Ringkasan Pendaftaran</h4>
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="label">Total Pendaftaran</div>
                        <div class="value">{{ number_format($summary['total_registrations']) }}</div>
                    </div>
                    <div class="summary-item">
                        <div class="label">Atlet Putra (M)</div>
                        <div class="value" style="color: #1e40af;">{{ number_format($summary['total_male']) }}</div>
                    </div>
                    <div class="summary-item">
                        <div class="label">Atlet Putri (F)</div>
                        <div class="value" style="color: #9d174d;">{{ number_format($summary['total_female']) }}</div>
                    </div>
                    <div class="summary-item">
                        <div class="label">Nomor Beregu/Tim</div>
                        <div class="value" style="color: #2563eb;">{{ number_format($summary['total_team']) }}</div>
                    </div>
                    <div class="summary-item">
                        <div class="label">Total Kontingen</div>
                        <div class="value">{{ number_format($summary['total_contingents']) }}</div>
                    </div>
                </div>
            </div>

            <div class="signature-box">
                <div>Panitia Pelaksana / Technical Delegate</div>
                <div class="signature-space"></div>
                <div class="signature-line">( ............................................................ )</div>
                <div style="font-size: 9px; color: #64748b; margin-top: 3px;">Tanda Tangan & Nama Terang</div>
            </div>
        </div>
    </div>

</body>
</html>
