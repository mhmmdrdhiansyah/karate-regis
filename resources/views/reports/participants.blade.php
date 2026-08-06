<x-app-layout>
    @section('title', 'Laporan - Peserta Terdaftar')

    <div class="card">
        <div class="card-header border-0 pt-8 pb-6">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-4">
                <div>
                    <h2 class="fw-bold text-dark fs-2 mb-1">Laporan Peserta Terdaftar</h2>
                    <p class="text-muted fw-semibold fs-6 mb-0">Daftar lengkap peserta yang telah terdaftar dalam event</p>
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
            {{-- Filters Section --}}
            <div class="separator separator-content border-primary d-flex align-items-center mb-6">
                <span class="text-gray-500 fw-semibold fs-7">Filter Data</span>
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
                    <label class="form-label fs-7 text-gray-600 fw-semibold mb-1">Jenis</label>
                    <select name="category_type" id="filter-category-type" class="form-select form-select-solid">
                        <option value="">Semua Jenis</option>
                        @foreach($categoryTypes as $type)
                            <option value="{{ $type }}" {{ request('category_type') === $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4">
                    <label class="form-label fs-7 text-gray-600 fw-semibold mb-1">Gender</label>
                    <select name="gender" id="filter-gender" class="form-select form-select-solid">
                        <option value="">Semua</option>
                        @foreach($genders as $key => $label)
                            <option value="{{ $key }}" {{ request('gender') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4">
                    <label class="form-label fs-7 text-gray-600 fw-semibold mb-1">Status Berkas</label>
                    <select name="status_berkas" id="filter-status-berkas" class="form-select form-select-solid">
                        <option value="">Semua Status</option>
                        @foreach($statusBerkasOptions as $key => $label)
                            <option value="{{ $key }}" {{ request('status_berkas') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-4 col-md-6">
                    <label class="form-label fs-7 text-gray-600 fw-semibold mb-1">Cari Peserta</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body">
                            <i class="bi bi-search fs-6"></i>
                        </span>
                        <input type="text" name="search" id="filter-search" class="form-control form-control-solid" placeholder="Nama peserta atau kontingen..." value="{{ request('search') }}">
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
                    <a href="{{ route('reports.participants') }}" class="btn btn-light-danger btn-sm">
                        <i class="bi bi-arrow-clockwise fs-5 me-1"></i>
                        Reset Filter
                    </a>
                </div>
            </div>

            {{-- Desktop Table --}}
            <div class="table-responsive d-none d-lg-block" style="position: relative;">
                <table id="report-table" class="table table-row-bordered table-row-dashed align-middle fs-6 gy-4">
                    <thead>
                        <tr class="text-start text-gray-600 fw-bold fs-7 bg-light">
                            <th class="min-w-50px sticky-left" style="left: 0;">No</th>
                            <th class="min-w-160px sticky-left" style="left: 50px;">Full Name Kontingen</th>
                            <th class="min-w-140px">Short Name</th>
                            <th class="min-w-70px">Negara</th>
                            <th class="min-w-120px">First Name</th>
                            <th class="min-w-160px">Last Name</th>
                            <th class="min-w-60px">Sex</th>
                            <th class="min-w-100px">Age</th>
                            <th class="min-w-220px">Kelas</th>
                            <th class="min-w-100px">Cat. Gender</th>
                            <th class="min-w-60px">Team</th>
                            <th class="min-w-120px">Name Team</th>
                            <th class="min-w-80px">Min Age</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 fw-semibold">
                        @foreach($registrations as $idx => $registration)
                            <tr>
                                <td class="text-center sticky-left bg-body" style="left: 0;">{{ $registrations->firstItem() + $idx }}</td>
                                <td class="sticky-left bg-body" style="left: 50px;">
                                    <div class="text-dark fw-bold">{{ $registration->full_name_kontingen }}</div>
                                </td>
                                <td>{{ $registration->short_name_kontingen }}</td>
                                <td><span class="badge badge-light-info">{{ $registration->kode_negara }}</span></td>
                                <td>{{ $registration->first_name ?: '-' }}</td>
                                <td><div class="text-dark fw-semibold">{{ $registration->last_name }}</div></td>
                                <td>
                                    @if($registration->sex === 'M' || $registration->sex === 'L')
                                        <span class="badge badge-light-primary">M</span>
                                    @elseif($registration->sex === 'F' || $registration->sex === 'P')
                                        <span class="badge badge-light-danger">F</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td>{{ $registration->age }}</td>
                                <td>
                                    <div class="text-dark">{{ $registration->kelas }}</div>
                                </td>
                                <td>{{ $registration->category_gender ?: '-' }}</td>
                                <td>{{ $registration->team ?: '-' }}</td>
                                <td>{{ $registration->name_team ?: '-' }}</td>
                                <td>{{ $registration->min_age }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile Card Layout --}}
            <div class="d-block d-lg-none">
                @foreach($registrations as $idx => $registration)
                    <div class="card mb-3 border border-dashed border-gray-300 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start gap-3 mb-3">
                                <div class="symbol symbol-50px symbol-circle flex-shrink-0 bg-light-{{ $registration->sex === 'M' ? 'primary' : ($registration->sex === 'F' ? 'danger' : 'secondary') }}">
                                    <span class="symbol-label fs-6 fw-bold text-{{ $registration->sex === 'M' ? 'primary' : ($registration->sex === 'F' ? 'danger' : 'secondary') }}">
                                        {{ strtoupper(substr($registration->last_name, 0, 1)) }}
                                    </span>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <h5 class="card-title fw-bold text-dark mb-0 text-truncate">
                                            {{ $registration->last_name }}
                                        </h5>
                                        @if($registration->sex === 'M' || $registration->sex === 'L')
                                            <span class="badge badge-light-primary ms-2 flex-shrink-0">M</span>
                                        @elseif($registration->sex === 'F' || $registration->sex === 'P')
                                            <span class="badge badge-light-danger ms-2 flex-shrink-0">F</span>
                                        @endif
                                    </div>
                                    <p class="text-gray-500 fs-7 fw-semibold mb-0">
                                        {{ $registration->full_name_kontingen }} ({{ $registration->kode_negara }})
                                    </p>
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="bg-light rounded p-2 text-center">
                                        <div class="text-gray-500 fs-7 mb-1">Kelas</div>
                                        <div class="text-dark fw-bold fs-7 text-truncate">{{ $registration->kelas }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-light rounded p-2 text-center">
                                        <div class="text-gray-500 fs-7 mb-1">Age / Tim</div>
                                        <div class="text-dark fw-bold fs-7">{{ $registration->age }} / {{ $registration->name_team ?: '-' }}</div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-link btn-sm p-0 toggle-details fw-semibold" style="color: #009ef7;">
                                <i class="bi bi-chevron-down me-1 toggle-icon"></i>
                                <span class="toggle-text">Lihat Detail</span>
                            </button>

                            <div class="p-card-details" style="display: none; margin-top: 12px;">
                                <div class="bg-light rounded p-3">
                                    <div class="d-flex justify-content-between py-2 border-bottom border-gray-200">
                                        <span class="text-gray-500 fs-7">First Name (Perguruan)</span>
                                        <span class="text-dark fw-bold fs-7">{{ $registration->first_name ?: '-' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between py-2 border-bottom border-gray-200">
                                        <span class="text-gray-500 fs-7">Category Gender</span>
                                        <span class="text-dark fw-bold fs-7">{{ $registration->category_gender ?: '-' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between py-2">
                                        <span class="text-gray-500 fs-7">Min Age</span>
                                        <span class="text-dark fw-bold fs-7">{{ $registration->min_age }}</span>
                                    </div>
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
                <div class="text-center py-15">
                    <div class="mb-6">
                        <div class="symbol symbol-100px symbol-circle bg-light mb-4 mx-auto">
                            <span class="symbol-label">
                                <i class="bi bi-inbox fs-2x text-gray-400"></i>
                            </span>
                        </div>
                    </div>
                    <h4 class="text-gray-700 fw-bold mb-3">Tidak ada data peserta yang ditemukan</h4>
                    <p class="text-gray-500 fw-semibold fs-6 mb-6">Coba ubah filter atau kata kunci pencarian Anda</p>
                    <a href="{{ route('reports.participants') }}" class="btn btn-light-primary">
                        <i class="bi bi-arrow-counterclockwise fs-5 me-2"></i>
                        Reset Semua Filter
                    </a>
                </div>
            @endif
        </div>
    </div>

    @push('styles')
        <style>
            .sticky-left {
                position: sticky;
                z-index: 10;
            }
            .sticky-left::before {
                content: '';
                position: absolute;
                top: 0;
                right: 0;
                bottom: -1px;
                width: 4px;
                background: linear-gradient(to right, rgba(0,0,0,0.05), transparent);
            }
            @media (max-width: 991px) {
                .toggle-details:hover {
                    color: #007bb5 !important;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Restore sidebar state after page load
                restoreSidebarState();

                // Start watching sidebar state changes
                watchSidebarState();

                // Filter change handlers
                const filters = ['event', 'contingent', 'category_type', 'gender', 'status_berkas', 'search'];
                const urlParams = new URLSearchParams(window.location.search);

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

                // Save sidebar state before navigation
                function saveSidebarState() {
                    const body = document.body;
                    const toggleBtn = document.getElementById('kt_aside_toggle');
                    const hasClass = body.classList.contains('aside-minimize');
                    const toggleActive = toggleBtn && toggleBtn.classList.contains('active');
                    const isMinimized = toggleActive || hasClass;
                    const state = isMinimized ? 'minimized' : 'expanded';
                    localStorage.setItem('aside_minimize_state', state);
                }

                // Watch sidebar state changes continuously
                function watchSidebarState() {
                    const toggleBtn = document.getElementById('kt_aside_toggle');
                    if (!toggleBtn) return;

                    // Use MutationObserver to watch for class changes on body and aside
                    const observer = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                                const body = document.body;
                                const isMinimized = body.classList.contains('aside-minimize') ||
                                                  (toggleBtn && toggleBtn.classList.contains('active'));
                                const state = isMinimized ? 'minimized' : 'expanded';
                                localStorage.setItem('aside_minimize_state', state);
                            }
                        });
                    });

                    // Observe body element for class changes
                    observer.observe(document.body, { attributes: true, attributeFilter: ['class'] });

                    // Also observe aside element
                    const aside = document.getElementById('kt_aside');
                    if (aside) {
                        observer.observe(aside, { attributes: true, attributeFilter: ['class'] });
                    }
                }

                // Restore sidebar state after page load
                function restoreSidebarState() {
                    const ourState = localStorage.getItem('aside_minimize_state');

                    if (ourState === 'minimized') {
                        setTimeout(function() {
                            const toggleButton = document.getElementById('kt_aside_toggle');
                            const body = document.body;
                            const isCurrentlyMinimized = body.classList.contains('aside-minimize');
                            const toggleActive = toggleButton && toggleButton.classList.contains('active');

                            // Only click if sidebar is NOT currently minimized
                            if (toggleButton && !isCurrentlyMinimized && !toggleActive) {
                                toggleButton.click();
                            }
                        }, 300);
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
                        const card = this.closest('.card-body');
                        const details = card.querySelector('.p-card-details');
                        const icon = this.querySelector('.toggle-icon');
                        const text = this.querySelector('.toggle-text');

                        if (details.style.display === 'none') {
                            details.style.display = 'block';
                            icon.classList.remove('bi-chevron-down');
                            icon.classList.add('bi-chevron-up');
                            text.textContent = 'Tutup Detail';
                        } else {
                            details.style.display = 'none';
                            icon.classList.remove('bi-chevron-up');
                            icon.classList.add('bi-chevron-down');
                            text.textContent = 'Lihat Detail';
                        }
                    });
                });

                // Export CSV (Excel)
                document.getElementById('btn-export-excel').addEventListener('click', async function() {
                    try {
                        // Get current filter parameters
                        const params = new URLSearchParams(window.location.search);

                        // Fetch all filtered data
                        const response = await fetch('{{ route('reports.participants.export') }}?' + params.toString());
                        const data = await response.json();

                        if (!data || data.length === 0) {
                            alert('Tidak ada data untuk diekspor');
                            return;
                        }

                        // CSV headers exact specification
                        const headers = [
                            'Full Name Kontingen',
                            'Short Name Kontingen',
                            'Kode Negara',
                            'First Name',
                            'Last Name',
                            'Sex',
                            'Age',
                            'Kelas',
                            'Category Gender',
                            'Team',
                            'Name Team',
                            'Min Age'
                        ];

                        // Build CSV rows
                        const csvRows = [headers.join(',')];
                        data.forEach((reg) => {
                            const escapeCsv = (val) => `"${(val !== null && val !== undefined ? String(val) : '').replace(/"/g, '""')}"`;
                            const row = [
                                escapeCsv(reg.full_name_kontingen),
                                escapeCsv(reg.short_name_kontingen),
                                escapeCsv(reg.kode_negara || 'INA'),
                                escapeCsv(reg.first_name),
                                escapeCsv(reg.last_name),
                                escapeCsv(reg.sex),
                                escapeCsv(reg.age),
                                escapeCsv(reg.kelas),
                                escapeCsv(reg.category_gender),
                                escapeCsv(reg.team),
                                escapeCsv(reg.name_team),
                                escapeCsv(reg.min_age)
                            ];
                            csvRows.push(row.join(','));
                        });

                        // Download CSV with BOM for Excel compatibility
                        const blob = new Blob(['\uFEFF' + csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' });
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'laporan_peserta.csv';
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        URL.revokeObjectURL(url);
                    } catch (error) {
                        console.error('Export error:', error);
                        alert('Gagal mengekspor data');
                    }
                });

                // Export PDF: Open dedicated print-ready PDF report view in new tab
                document.getElementById('btn-export-pdf').addEventListener('click', function() {
                    const params = new URLSearchParams(window.location.search);
                    const pdfUrl = '{{ route('reports.participants.pdf') }}?' + params.toString();
                    window.open(pdfUrl, '_blank');
                });
            });
        </script>
    @endpush
</x-app-layout>
