<x-app-layout>
    @section('title', 'Laporan - Keuangan Transaksi')

    <div class="card">
        <div class="card-header border-0 pt-8 pb-6">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-4 w-100">
                <div>
                    <h2 class="fw-bold text-dark fs-2 mb-1">Laporan Keuangan & Transaksi</h2>
                    <p class="text-muted fw-semibold fs-6 mb-0">Rekapitulasi pembayaran pendaftaran event, diskon, dan pemasukan</p>
                </div>
                <div class="d-flex gap-2">
                    <button id="btn-export-excel" class="btn btn-light-success">
                        <span class="svg-icon svg-icon-2">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M14 3H6C5.4 3 5 3.4 5 4V20C5 20.6 5.4 21 6 21H18C18.6 21 19 20.6 19 20V8L14 3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M14 3V8H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M8 13L12 17L16 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 8V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        Export Excel
                    </button>
                    <button id="btn-export-pdf" class="btn btn-light-primary">
                        <span class="svg-icon svg-icon-2">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M14 3H6C5.4 3 5 3.4 5 4V20C5 20.6 5.4 21 6 21H18C18.6 21 19 20.6 19 20V8L14 3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M14 3V8H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9 12H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9 16H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        Export PDF
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body pt-2">
            {{-- Financial Summary Cards Row --}}
            <div class="row g-4 mb-8">
                <div class="col-xl-3 col-sm-6">
                    <div class="card card-dashed bg-light-success p-6 rounded-3 h-100">
                        <div class="d-flex align-items-center mb-2">
                            <div class="symbol symbol-45px symbol-circle bg-success me-4">
                                <span class="symbol-label">
                                    <i class="bi bi-wallet2 text-white fs-3"></i>
                                </span>
                            </div>
                            <div>
                                <div class="fs-7 fw-semibold text-gray-600 uppercase">Penerimaan Lunas</div>
                                <div class="fs-3x fw-bolder text-success">
                                    Rp {{ number_format($summary['total_verified_amount'], 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                        <div class="fs-7 text-gray-500 fw-semibold mt-2">
                            {{ $summary['total_verified_count'] }} Transaksi Terverifikasi
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6">
                    <div class="card card-dashed bg-light-warning p-6 rounded-3 h-100">
                        <div class="d-flex align-items-center mb-2">
                            <div class="symbol symbol-45px symbol-circle bg-warning me-4">
                                <span class="symbol-label">
                                    <i class="bi bi-clock-history text-white fs-3"></i>
                                </span>
                            </div>
                            <div>
                                <div class="fs-7 fw-semibold text-gray-600 uppercase">Pending Pembayaran</div>
                                <div class="fs-3x fw-bolder text-warning">
                                    Rp {{ number_format($summary['total_pending_amount'], 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                        <div class="fs-7 text-gray-500 fw-semibold mt-2">
                            Menunggu Konfirmasi Transfer
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6">
                    <div class="card card-dashed bg-light-primary p-6 rounded-3 h-100">
                        <div class="d-flex align-items-center mb-2">
                            <div class="symbol symbol-45px symbol-circle bg-primary me-4">
                                <span class="symbol-label">
                                    <i class="bi bi-percent text-white fs-3"></i>
                                </span>
                            </div>
                            <div>
                                <div class="fs-7 fw-semibold text-gray-600 uppercase">Total Diskon Diberikan</div>
                                <div class="fs-3x fw-bolder text-primary">
                                    Rp {{ number_format($summary['total_discount_amount'], 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                        <div class="fs-7 text-gray-500 fw-semibold mt-2">
                            Potongan Diskon Kategori
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6">
                    <div class="card card-dashed bg-light-info p-6 rounded-3 h-100">
                        <div class="d-flex align-items-center mb-2">
                            <div class="symbol symbol-45px symbol-circle bg-info me-4">
                                <span class="symbol-label">
                                    <i class="bi bi-receipt text-white fs-3"></i>
                                </span>
                            </div>
                            <div>
                                <div class="fs-7 fw-semibold text-gray-600 uppercase">Total Transaksi</div>
                                <div class="fs-3x fw-bolder text-info">
                                    {{ number_format($summary['total_invoices']) }} <span class="fs-6 fw-bold">Invoice</span>
                                </div>
                            </div>
                        </div>
                        <div class="fs-7 text-gray-500 fw-semibold mt-2">
                            {{ $summary['total_athletes'] }} Atlet, {{ $summary['total_teams'] }} Tim
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filters Section --}}
            <div class="separator separator-content border-primary d-flex align-items-center mb-6">
                <span class="text-gray-500 fw-semibold fs-7">Filter Laporan Transaksi</span>
            </div>
            <div class="row g-3 mb-6">
                <div class="col-lg-3 col-md-4">
                    <label class="form-label fs-7 text-gray-600 fw-semibold mb-1">Event</label>
                    <select name="event" id="filter-event" class="form-select form-select-solid" data-control="select2" data-placeholder="Semua Event" data-allow-clear="true">
                        <option value="">Semua Event</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}" {{ request('event') == $event->id ? 'selected' : '' }}>
                                {{ $event->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3 col-md-4">
                    <label class="form-label fs-7 text-gray-600 fw-semibold mb-1">Kontingen</label>
                    <select name="contingent" id="filter-contingent" class="form-select form-select-solid" data-control="select2" data-placeholder="Semua Kontingen" data-allow-clear="true">
                        <option value="">Semua Kontingen</option>
                        @foreach($contingents as $contingent)
                            <option value="{{ $contingent->id }}" {{ request('contingent') == $contingent->id ? 'selected' : '' }}>
                                {{ $contingent->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 col-md-4">
                    <label class="form-label fs-7 text-gray-600 fw-semibold mb-1">Status Pembayaran</label>
                    <select name="status" id="filter-status" class="form-select form-select-solid">
                        <option value="">Semua Status</option>
                        @foreach($statusOptions as $key => $label)
                            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 col-md-4">
                    <label class="form-label fs-7 text-gray-600 fw-semibold mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="filter-start-date" class="form-control form-control-solid" value="{{ request('start_date') }}">
                </div>

                <div class="col-lg-2 col-md-4">
                    <label class="form-label fs-7 text-gray-600 fw-semibold mb-1">Tanggal Selesai</label>
                    <input type="date" name="end_date" id="filter-end-date" class="form-control form-control-solid" value="{{ request('end_date') }}">
                </div>

                <div class="col-lg-4 col-md-6">
                    <label class="form-label fs-7 text-gray-600 fw-semibold mb-1">Cari Transaksi</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body">
                            <i class="bi bi-search fs-6"></i>
                        </span>
                        <input type="text" name="search" id="filter-search" class="form-control form-control-solid" placeholder="No Invoice atau Nama Kontingen..." value="{{ request('search') }}">
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-4 mb-8">
                <div class="d-flex align-items-center gap-3">
                    <span class="fs-7 text-gray-600 fw-semibold">Tampilkan per halaman:</span>
                    <select name="per_page" id="filter-per-page" class="form-select form-select-solid form-select-sm fw-bold" style="width: 80px;">
                        <option value="10" {{ request('per_page', 25) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page', 25) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page', 25) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                <div>
                    <a href="{{ route('reports.index') }}" class="btn btn-light-danger btn-sm">
                        <i class="bi bi-arrow-clockwise fs-5 me-1"></i>
                        Reset Filter
                    </a>
                </div>
            </div>

            {{-- Desktop Table --}}
            <div class="table-responsive d-none d-lg-block">
                <table id="report-table" class="table table-row-bordered table-row-dashed align-middle fs-6 gy-4">
                    <thead>
                        <tr class="text-start text-gray-600 fw-bold fs-7 bg-light">
                            <th class="min-w-50px text-center">No</th>
                            <th class="min-w-120px">No. Invoice</th>
                            <th class="min-w-120px">Tanggal</th>
                            <th class="min-w-180px">Kontingen</th>
                            <th class="min-w-180px">Event</th>
                            <th class="min-w-150px">Rincian Entry</th>
                            <th class="min-w-120px text-end">Subtotal (Rp)</th>
                            <th class="min-w-100px text-end">Diskon (Rp)</th>
                            <th class="min-w-140px text-end">Total Bayar (Rp)</th>
                            <th class="min-w-100px text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 fw-semibold">
                        @foreach($payments as $idx => $p)
                            <tr>
                                <td class="text-center">{{ $payments->firstItem() + $idx }}</td>
                                <td>
                                    <div class="text-dark fw-bold">{{ $p->invoice_number }}</div>
                                </td>
                                <td>{{ $p->date_formatted }}</td>
                                <td>
                                    <div class="text-dark fw-bold">{{ $p->contingent_name }}</div>
                                </td>
                                <td>{{ $p->event_name }}</td>
                                <td>
                                    <span class="badge badge-light-primary fw-semibold">{{ $p->entry_summary }}</span>
                                </td>
                                <td class="text-end">Rp {{ number_format($p->subtotal_amount, 0, ',', '.') }}</td>
                                <td class="text-end text-danger">
                                    {{ $p->total_discount > 0 ? 'Rp ' . number_format($p->total_discount, 0, ',', '.') : '-' }}
                                </td>
                                <td class="text-end text-dark fw-bolder fs-6">
                                    Rp {{ number_format($p->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @if($p->status_raw === 'verified')
                                        <span class="badge badge-light-success fw-bold px-3 py-2">Verified</span>
                                    @elseif($p->status_raw === 'pending')
                                        <span class="badge badge-light-warning fw-bold px-3 py-2">Pending</span>
                                    @elseif($p->status_raw === 'rejected')
                                        <span class="badge badge-light-danger fw-bold px-3 py-2">Rejected</span>
                                    @else
                                        <span class="badge badge-light-secondary fw-bold px-3 py-2">Cancelled</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile Layout --}}
            <div class="d-block d-lg-none">
                @foreach($payments as $idx => $p)
                    <div class="card mb-3 border border-dashed border-gray-300 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-dark fs-6">{{ $p->invoice_number }}</span>
                                @if($p->status_raw === 'verified')
                                    <span class="badge badge-light-success fw-bold">Verified</span>
                                @elseif($p->status_raw === 'pending')
                                    <span class="badge badge-light-warning fw-bold">Pending</span>
                                @elseif($p->status_raw === 'rejected')
                                    <span class="badge badge-light-danger fw-bold">Rejected</span>
                                @else
                                    <span class="badge badge-light-secondary fw-bold">Cancelled</span>
                                @endif
                            </div>
                            <div class="fw-bold text-dark fs-5 mb-1">{{ $p->contingent_name }}</div>
                            <div class="text-gray-500 fs-7 mb-3">{{ $p->event_name }} &bull; {{ $p->date_formatted }}</div>

                            <div class="bg-light rounded p-3 mb-3">
                                <div class="d-flex justify-content-between fs-7 py-1">
                                    <span class="text-gray-500">Entry:</span>
                                    <span class="fw-bold text-dark">{{ $p->entry_summary }}</span>
                                </div>
                                <div class="d-flex justify-content-between fs-7 py-1">
                                    <span class="text-gray-500">Subtotal:</span>
                                    <span>Rp {{ number_format($p->subtotal_amount, 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between fs-7 py-1">
                                    <span class="text-gray-500">Diskon:</span>
                                    <span class="text-danger">{{ $p->total_discount > 0 ? 'Rp ' . number_format($p->total_discount, 0, ',', '.') : '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between fs-6 fw-bolder pt-2 border-top border-gray-200">
                                    <span class="text-dark">Total Bayar:</span>
                                    <span class="text-success">Rp {{ number_format($p->total_amount, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($payments->count() > 0)
                <div class="row">
                    <div class="col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start">
                        <div class="dataTables_info">
                            Menampilkan {{ $payments->firstItem() }} sampai {{ $payments->lastItem() }} dari {{ $payments->total() }} data
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end">
                        {{ $payments->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-15">
                    <div class="mb-6">
                        <div class="symbol symbol-100px symbol-circle bg-light mb-4 mx-auto">
                            <span class="symbol-label">
                                <i class="bi bi-inbox fs-2x text-gray-400"></i>
                            </span>
                        </div>
                    </div>
                    <h4 class="text-gray-700 fw-bold mb-3">Tidak ada data transaksi pembayaran yang ditemukan</h4>
                    <p class="text-gray-500 fw-semibold fs-6 mb-6">Coba ubah filter atau kata kunci pencarian Anda</p>
                    <a href="{{ route('reports.index') }}" class="btn btn-light-primary">
                        <i class="bi bi-arrow-counterclockwise fs-5 me-2"></i>
                        Reset Semua Filter
                    </a>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Filter change handlers
                const filters = ['event', 'contingent', 'status', 'start_date', 'end_date', 'search'];

                filters.forEach(filterName => {
                    const elementId = 'filter-' + filterName.replace('_', '-');
                    const element = document.getElementById(elementId);
                    if (element) {
                        element.addEventListener('change', function() {
                            applyFilters();
                        });
                    }
                });

                // Select2 change event listener
                if (window.jQuery) {
                    $('#filter-event, #filter-contingent').on('change', function() {
                        applyFilters();
                    });
                }

                // Search on Enter key
                const searchInput = document.getElementById('filter-search');
                if (searchInput) {
                    searchInput.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            applyFilters();
                        }
                    });
                }

                // Per page handler
                const perPageSelect = document.getElementById('filter-per-page');
                if (perPageSelect) {
                    perPageSelect.addEventListener('change', function() {
                        applyFilters();
                    });
                }

                function applyFilters() {
                    const params = new URLSearchParams();

                    filters.forEach(filterName => {
                        const element = document.getElementById('filter-' + filterName.replace('_', '-'));
                        if (element && element.value) {
                            params.set(filterName, element.value);
                        }
                    });

                    // Per page
                    const perPage = document.getElementById('filter-per-page');
                    if (perPage && perPage.value !== '25') {
                        params.set('per_page', perPage.value);
                    }

                    window.location.href = '{{ route('reports.index') }}?' + params.toString();
                }

                // Export CSV (Excel)
                document.getElementById('btn-export-excel').addEventListener('click', async function() {
                    try {
                        const params = new URLSearchParams(window.location.search);
                        const response = await fetch('{{ route('reports.financial.export') }}?' + params.toString());
                        const data = await response.json();

                        if (!data || data.length === 0) {
                            alert('Tidak ada data transaksi untuk diekspor');
                            return;
                        }

                        // CSV headers exact specification
                        const headers = [
                            'No Invoice',
                            'Tanggal Transaksi',
                            'Nama Kontingen',
                            'Nama Event',
                            'Jumlah Atlet Perorangan',
                            'Jumlah Tim Beregu',
                            'Jumlah Official',
                            'Subtotal (Rp)',
                            'Total Diskon (Rp)',
                            'Grand Total (Rp)',
                            'Status Pembayaran',
                            'Tanggal Verifikasi',
                            'Diverifikasi Oleh'
                        ];

                        const csvRows = [headers.join(',')];
                        data.forEach((p) => {
                            const escapeCsv = (val) => `"${String(val ?? '')
                                .replace(/[^\p{L}\p{N}\s-]/gu, ' ')
                                .replace(/\s+/g, ' ')
                                .trim()
                                .toLowerCase()
                                .replace(/"/g, '""')}"`;
                            const row = [
                                escapeCsv(p.invoice_number),
                                escapeCsv(p.date_formatted),
                                escapeCsv(p.contingent_name),
                                escapeCsv(p.event_name),
                                escapeCsv(p.athlete_count),
                                escapeCsv(p.team_count),
                                escapeCsv(p.coach_count),
                                escapeCsv(p.subtotal_amount),
                                escapeCsv(p.total_discount),
                                escapeCsv(p.total_amount),
                                escapeCsv(p.status_raw),
                                escapeCsv(p.verified_at ? p.verified_at : '-'),
                                escapeCsv(p.verified_by_name)
                            ];
                            csvRows.push(row.join(','));
                        });

                        // Download CSV with BOM for Excel compatibility
                        const blob = new Blob(['\uFEFF' + csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' });
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'laporan_keuangan.csv';
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        URL.revokeObjectURL(url);
                    } catch (error) {
                        console.error('Export error:', error);
                        alert('Gagal mengekspor data transaksi');
                    }
                });

                // Export PDF (opens dedicated financial PDF print view in new tab)
                document.getElementById('btn-export-pdf').addEventListener('click', function() {
                    const params = new URLSearchParams(window.location.search);
                    const pdfUrl = '{{ route('reports.financial.pdf') }}?' + params.toString();
                    window.open(pdfUrl, '_blank');
                });
            });
        </script>
    @endpush
</x-app-layout>
