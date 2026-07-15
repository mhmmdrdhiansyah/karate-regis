<x-app-layout>
    @section('title', 'Laporan - Peserta Terdaftar')

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3 class="card-label fw-bold text-dark">Laporan Peserta Terdaftar</h3>
            </div>
            <div class="card-toolbar">
                <div class="d-flex justify-content-end align-items-center gap-3">
                    <button id="btn-export-pdf" class="btn btn-light-primary btn-sm">Export PDF</button>
                    <button id="btn-export-excel" class="btn btn-light-success btn-sm">Export Excel</button>
                </div>
            </div>
        </div>

        <div class="card-body py-4">
            {{-- Filters Section --}}
            <div class="row mb-4">
                <div class="col-md-2">
                    <select name="event" id="filter-event" class="form-select form-select-sm">
                        <option value="">Semua Event</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}" {{ request('event') == $event->id ? 'selected' : '' }}>
                                {{ $event->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="contingent" id="filter-contingent" class="form-select form-select-sm">
                        <option value="">Semua Kontingen</option>
                        @foreach($contingents as $contingent)
                            <option value="{{ $contingent->id }}" {{ request('contingent') == $contingent->id ? 'selected' : '' }}>
                                {{ $contingent->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="category_type" id="filter-category-type" class="form-select form-select-sm">
                        <option value="">Semua Jenis</option>
                        @foreach($categoryTypes as $type)
                            <option value="{{ $type }}" {{ request('category_type') === $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="gender" id="filter-gender" class="form-select form-select-sm">
                        <option value="">Semua Sex</option>
                        @foreach($genders as $key => $label)
                            <option value="{{ $key }}" {{ request('gender') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status_berkas" id="filter-status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        @foreach($statusBerkasOptions as $key => $label)
                            <option value="{{ $key }}" {{ request('status_berkas') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" name="search" id="filter-search" class="form-control form-control-sm" placeholder="Cari nama..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-10">
                    <div class="d-flex align-items-center gap-2">
                        <label class="fs-7 text-muted">Tampilkan:</label>
                        <select name="per_page" id="filter-per-page" class="form-select form-select-sm" style="width: auto;">
                            <option value="10" {{ request('per_page', 25) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page', 25) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page', 25) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        <span class="fs-7 text-muted">data per halaman</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('reports.participants') }}" class="btn btn-light-danger btn-sm w-100">Reset Filter</a>
                </div>
            </div>

            {{-- Desktop Table --}}
            <div class="table-responsive d-none d-lg-block">
                <table id="report-table" class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                            <th class="min-w-50px">No</th>
                            <th class="min-w-150px">Full Name Kontingen</th>
                            <th class="min-w-150px">Short Name Kontingen</th>
                            <th class="min-w-100px">Kode Negara</th>
                            <th class="min-w-150px">First Name</th>
                            <th class="min-w-150px">Last Name</th>
                            <th class="min-w-80px">Sex</th>
                            <th class="min-w-80px">Age</th>
                            <th class="min-w-200px">Kelas</th>
                            <th class="min-w-100px">Sex Category</th>
                            <th class="min-w-80px">Team</th>
                            <th class="min-w-150px">Name Team</th>
                            <th class="min-w-80px">Min Age</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-bold">
                        @foreach($registrations as $idx => $registration)
                            <tr>
                                <td class="text-gray-500 text-center">{{ $registrations->firstItem() + $idx }}</td>
                                <td>{{ $registration->contingent_name }}</td>
                                <td>{{ $registration->contingent_name }}</td>
                                <td>INA</td>
                                <td>{{ $registration->institusi }}</td>
                                <td>{{ $registration->participant_name }}</td>
                                <td>{{ $registration->participant_gender?->value ?? '-' }}</td>
                                <td>{{ $registration->age }}</td>
                                <td>{{ $registration->kelas }}</td>
                                <td>{{ $registration->sub_category_gender?->value ?? '-' }}</td>
                                <td>{{ $registration->team }}</td>
                                <td>{{ $registration->team_name ?? '-' }}</td>
                                <td>{{ $registration->min_age }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile Card Layout --}}
            <div class="d-block d-lg-none">
                @foreach($registrations as $idx => $registration)
                    <div class="p-card" style="background:#fff;border:1px dashed #e4e6ef;border-radius:8px;margin-bottom:10px;overflow:hidden">
                        <div class="p-card-hd" style="padding:12px 14px;display:flex;align-items:center;gap:10px;">
                            <div style="flex:1;min-width:0;font-weight:700;font-size:.88rem;color:#3f4254">
                                {{ $registration->participant_name }}
                            </div>
                            <div style="text-align:right;font-weight:600;font-size:.78rem">
                                {{ $registration->contingent_name }}
                            </div>
                        </div>
                        <div class="p-card-bd" style="padding:0 14px 12px">
                            <div style="padding:7px 0;border-bottom:1px solid #f3f6f9">
                                <span class="p-card-lbl" style="font-size:.72rem;color:#b5b5c3;font-weight:600">Event</span>
                                <div class="p-card-val" style="font-size:.78rem;color:#3f4254;font-weight:600;text-align:right">
                                    {{ $registration->event_name }}
                                </div>
                            </div>
                            <div style="padding:7px 0;border-bottom:1px solid #f3f6f9">
                                <span class="p-card-lbl" style="font-size:.72rem;color:#b5b5c3;font-weight:600">Kelas</span>
                                <div class="p-card-val" style="font-size:.78rem;color:#3f4254;font-weight:600;text-align:right">
                                    {{ $registration->kelas }}
                                </div>
                            </div>
                            <div style="padding:7px 0;border-bottom:1px solid #f3f6f9">
                                <span class="p-card-lbl" style="font-size:.72rem;color:#b5b5c3;font-weight:600">Age / Team</span>
                                <div class="p-card-val" style="font-size:.78rem;color:#3f4254;font-weight:600;text-align:right">
                                    {{ $registration->age }} / {{ $registration->team_name ?? '-' }}
                                </div>
                            </div>
                            <div style="padding:7px 0;">
                                <button type="button" class="btn btn-link btn-sm p-0 toggle-details" style="font-size:.72rem;color:#009ef7">
                                    Show Details
                                </button>
                            </div>
                            <div class="p-card-details" style="display:none;padding:10px;background:#f8f9fa;border-radius:4px;margin-top:5px;font-size:.75rem">
                                <div style="padding:3px 0;display:flex;justify-content:space-between">
                                    <span style="color:#b5b5c3">Kode Negara:</span>
                                    <span style="font-weight:600">INA</span>
                                </div>
                                <div style="padding:3px 0;display:flex;justify-content:space-between">
                                    <span style="color:#b5b5c3">First Name:</span>
                                    <span style="font-weight:600">{{ $registration->institusi }}</span>
                                </div>
                                <div style="padding:3px 0;display:flex;justify-content:space-between">
                                    <span style="color:#b5b5c3">Sex:</span>
                                    <span style="font-weight:600">{{ $registration->participant_gender?->value ?? '-' }}</span>
                                </div>
                                <div style="padding:3px 0;display:flex;justify-content:space-between">
                                    <span style="color:#b5b5c3">Sex Category:</span>
                                    <span style="font-weight:600">{{ $registration->sub_category_gender?->value ?? '-' }}</span>
                                </div>
                                <div style="padding:3px 0;display:flex;justify-content:space-between">
                                    <span style="color:#b5b5c3">Team:</span>
                                    <span style="font-weight:600">{{ $registration->team }}</span>
                                </div>
                                <div style="padding:3px 0;display:flex;justify-content:space-between">
                                    <span style="color:#b5b5c3">Min Age:</span>
                                    <span style="font-weight:600">{{ $registration->min_age }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($registrations->count() > 0)
                <div class="row">
                    <div class="col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start">
                        <div class="dataTables_info">
                            Menampilkan {{ $registrations->firstItem() }} sampai {{ $registrations->lastItem() }} dari {{ $registrations->total() }} data
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end">
                        {{ $registrations->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-10">
                    <div class="text-muted fs-5">Tidak ada data peserta yang ditemukan</div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Restore sidebar state after page load
                restoreSidebarState();

                // Filter change handlers
                const filters = ['event', 'contingent', 'category_type', 'gender', 'status_berkas', 'search'];
                const urlParams = new URLSearchParams(window.location.search);

                filters.forEach(filterName => {
                    const element = document.getElementById('filter-' + filterName.replace('_', '-'));
                    if (element) {
                        element.addEventListener('change', function() {
                            applyFilters();
                        });
                    }
                });

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

                // Save sidebar state before navigation
                function saveSidebarState() {
                    const body = document.body;
                    const isMinimized = body.classList.contains('aside-minimize');
                    localStorage.setItem('aside_minimize_state', isMinimized ? 'minimized' : 'expanded');
                }

                // Restore sidebar state after page load
                function restoreSidebarState() {
                    const savedState = localStorage.getItem('aside_minimize_state');
                    if (savedState === 'minimized') {
                        const body = document.body;
                        if (!body.classList.contains('aside-minimize')) {
                            body.classList.add('aside-minimize');
                        }
                    }
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

                    // Save sidebar state before navigation
                    saveSidebarState();

                    window.location.href = '{{ route('reports.participants') }}?' + params.toString();
                }

                // Mobile toggle details
                document.querySelectorAll('.toggle-details').forEach(button => {
                    button.addEventListener('click', function() {
                        const details = this.closest('.p-card-bd').querySelector('.p-card-details');
                        if (details.style.display === 'none') {
                            details.style.display = 'block';
                            this.textContent = 'Hide Details';
                        } else {
                            details.style.display = 'none';
                            this.textContent = 'Show Details';
                        }
                    });
                });

                // Export CSV (Excel)
                document.getElementById('btn-export-excel').addEventListener('click', function() {
                    const table = document.getElementById('report-table');
                    if (!table) {
                        alert('Tabel tidak ditemukan');
                        return;
                    }

                    const rows = Array.from(table.querySelectorAll('thead tr, tbody tr'));
                    const csv = [];

                    rows.forEach((row) => {
                        const cols = Array.from(row.querySelectorAll('th, td'));
                        const rowData = cols.map(col => '"' + col.innerText.replace(/"/g, '""') + '"');
                        csv.push(rowData.join(','));
                    });

                    const blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'laporan_peserta.csv';
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    URL.revokeObjectURL(url);
                });

                // Export PDF using jsPDF + autoTable
                document.getElementById('btn-export-pdf').addEventListener('click', function() {
                    const { jsPDF } = window.jspdf || {};
                    if (!jsPDF) {
                        alert('Library jsPDF belum tersedia.');
                        return;
                    }

                    const table = document.getElementById('report-table');
                    if (!table) {
                        alert('Tabel tidak ditemukan');
                        return;
                    }

                    const headers = Array.from(table.querySelectorAll('thead th')).map(h => h.innerText.trim());
                    const body = Array.from(table.querySelectorAll('tbody tr')).map(tr => {
                        return Array.from(tr.querySelectorAll('td')).map(td => td.innerText.trim());
                    });

                    const doc = new jsPDF('landscape');

                    doc.setFontSize(10);
                    doc.text('Laporan Peserta Terdaftar', 14, 15);

                    doc.autoTable({
                        startY: 20,
                        head: [headers],
                        body: body,
                        styles: {
                            fontSize: 7,
                            cellPadding: 2,
                        },
                        headStyles: {
                            fillColor: [66, 139, 202],
                            textColor: 255,
                            fontStyle: 'bold',
                        },
                    });

                    doc.save('laporan_peserta.pdf');
                });
            });
        </script>
    @endpush
</x-app-layout>
