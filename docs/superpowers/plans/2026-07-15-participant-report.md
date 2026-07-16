# Participant Report Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a Participant Report page showing all registered participants with filtering, pagination, and PDF/CSV export.

**Architecture:** Controller + View pattern (consistent with existing payment report), server-side filtering, client-side export via jsPDF.

**Tech Stack:** Laravel 8.x, Blade views, Metronic UI, jsPDF + autoTable (CDN), Carbon for date calculations.

---

### Task 1: Add Route for Participant Report

**Files:**
- Modify: `routes/web.php`

- [ ] **Step 1: Add route to web.php**

Add this route after the existing reports route (around line 109):

```php
// Laporan Peserta
Route::get('reports/participants', [ReportController::class, 'participants'])
    ->middleware(['permission:view reports'])
    ->name('reports.participants');
```

- [ ] **Step 2: Verify route is accessible**

Run: `php artisan route:list --name=reports.participants`
Expected output shows route `GET /reports/participants` with `reports.participants` name

- [ ] **Step 3: Commit**

```bash
git add routes/web.php
git commit -m "feat(reports): add participant report route

- Add GET /reports/participants route
- Protected by view reports permission middleware

Co-Authored-By: Claude Opus 4.7 <noreply@anthropic.com>"
```

---

### Task 2: Add `participants` Method to ReportController

**Files:**
- Modify: `app/Http/Controllers/ReportController.php`

- [ ] **Step 1: Write the participants method**

Add this method after the `index` method:

```php
public function participants(Request $request)
{
    // Build query with all necessary joins
    $query = Registration::with(['participant.contingent', 'subCategory.eventCategory.event', 'teamGroup'])
        ->whereNull('deleted_at')           // Exclude soft-deleted registrations
        ->whereNotNull('sub_category_id')   // Exclude coach registrations
        ->join('participants', 'participants.id', '=', 'registrations.participant_id')
        ->join('sub_categories', 'sub_categories.id', '=', 'registrations.sub_category_id')
        ->join('event_categories', 'event_categories.id', '=', 'sub_categories.event_category_id')
        ->join('events', 'events.id', '=', 'event_categories.event_id')
        ->join('contingents', 'contingents.id', '=', 'participants.contingent_id')
        ->select([
            'registrations.*',
            'participants.name as participant_name',
            'participants.institusi',
            'participants.birth_date',
            'participants.gender as participant_gender',
            'contingents.name as contingent_name',
            'sub_categories.name as sub_category_name',
            'sub_categories.category_type',
            'sub_categories.gender as sub_category_gender',
            'event_categories.name as event_category_name',
            'event_categories.min_birth_date',
            'events.name as event_name',
            'team_groups.team_name',
        ]);

    // Apply filters
    if ($request->filled('event')) {
        $query->where('events.id', $request->event);
    }

    if ($request->filled('contingent')) {
        $query->where('contingents.id', $request->contingent);
    }

    if ($request->filled('category_type')) {
        $query->where('sub_categories.category_type', $request->category_type);
    }

    if ($request->filled('gender')) {
        $query->where('sub_categories.gender', $request->gender);
    }

    if ($request->filled('status_berkas')) {
        $query->where('registrations.status_berkas', $request->status_berkas);
    }

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('participants.name', 'like', '%' . $search . '%')
              ->orWhere('contingents.name', 'like', '%' . $search . '%');
        });
    }

    // Default ordering: contingent name, then participant name
    $query->orderBy('contingents.name', 'asc')
          ->orderBy('participants.name', 'asc');

    // Get per page from request, default to 25
    $perPage = $request->get('per_page', 25);
    if (!in_array($perPage, [10, 25, 50, 100])) {
        $perPage = 25;
    }

    // Paginate with query string
    $registrations = $query->paginate($perPage)->withQueryString();

    // Get filter options
    $events = Event::orderBy('name')->get(['id', 'name']);
    $contingents = Contingent::orderBy('name')->get(['id', 'name']);
    $categoryTypes = SubCategory::distinct()->orderBy('category_type')->pluck('category_type');
    $genders = [
        'M' => 'Laki-laki',
        'F' => 'Perempuan',
        'Mixed' => 'Campuran',
    ];
    $statusBerkasOptions = [
        'pending' => 'Pending',
        'verified' => 'Verified',
        'rejected' => 'Rejected',
    ];

    // Calculate ages for each registration
    $registrations->getCollection()->transform(function ($registration) {
        // Participant age
        $registration->age = $registration->participant->birth_date
            ? \Carbon\Carbon::parse($registration->participant->birth_date)->age
            : '-';

        // Min age from event category
        $registration->min_age = $registration->subCategory->eventCategory->min_birth_date
            ? \Carbon\Carbon::parse($registration->subCategory->eventCategory->min_birth_date)->age
            : '-';

        // Kelas: event_category.name - sub_category.name
        $registration->kelas = $registration->subCategory->eventCategory->name . ' - ' . $registration->subCategory->name;

        // Team: "t" if beregu, else ""
        $registration->team = $registration->subCategory->category_type === 'beregu' ? 't' : '';

        return $registration;
    });

    return view('reports.participants', compact(
        'registrations',
        'events',
        'contingents',
        'categoryTypes',
        'genders',
        'statusBerkasOptions',
    ));
}
```

- [ ] **Step 2: Verify the method compiles**

Run: `php artisan config:clear && php artisan route:clear`
Expected: No errors

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/ReportController.php
git commit -m "feat(reports): add participants method to ReportController

- Build query with joins to participants, sub_categories, events, contingents
- Apply server-side filters (event, contingent, category_type, gender, status_berkas, search)
- Default ordering: contingent name, then participant name
- Calculate ages and format display fields
- Support pagination with 10, 25, 50, 100 options
- Exclude soft-deleted and coach registrations

Co-Authored-By: Claude Opus 4.7 <noreply@anthropic.com>"
```

---

### Task 3: Create Participants View Blade File

**Files:**
- Create: `resources/views/reports/participants.blade.php`

- [ ] **Step 1: Create the view file structure**

Create the complete view file following the payment report pattern:

```blade
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
```

- [ ] **Step 2: Verify the view compiles**

Run: `php artisan view:clear`
Expected: No errors

- [ ] **Step 3: Commit**

```bash
git add resources/views/reports/participants.blade.php
git commit -m "feat(reports): create participants report view

- Add filter section with 6 dropdowns + search
- Display 12-column table on desktop
- Mobile card layout with expand/collapse details
- Export PDF and CSV/Excel functionality via jsPDF
- Pagination support with 10, 25, 50, 100 options
- Filter state preserved in URL query string

Co-Authored-By: Claude Opus 4.7 <noreply@anthropic.com>"
```

---

### Task 4: Add Sidebar Menu Item

**Files:**
- Modify: `resources/views/layouts/partials/sidebar.blade.php`

- [ ] **Step 1: Add menu item under existing Laporan section**

Find the existing "Laporan" menu item (around line 224-232) and modify to add the new menu item inside an accordion structure:

Replace the existing single Laporan menu with this accordion:

```blade
@can('view reports')
<div data-kt-menu-trigger="click"
    class="menu-item menu-accordion {{ request()->routeIs('reports.*') ? 'hover show' : '' }}">
    <span class="menu-link">
        <span class="menu-icon"><i class="bi bi-file-earmark fs-3"></i></span>
        <span class="menu-title">Laporan</span>
        <span class="menu-arrow"></span>
    </span>
    <div class="menu-sub menu-sub-accordion">
        <div class="menu-item">
            <a class="menu-link {{ request()->routeIs('reports.index') ? 'active' : '' }}"
                href="{{ route('reports.index') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">Laporan Keuangan</span>
            </a>
        </div>
        <div class="menu-item">
            <a class="menu-link {{ request()->routeIs('reports.participants') ? 'active' : '' }}"
                href="{{ route('reports.participants') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">Laporan Peserta</span>
            </a>
        </div>
    </div>
</div>
@endcan
```

- [ ] **Step 2: Verify sidebar compiles**

Run: `php artisan view:clear`
Expected: No errors

- [ ] **Step 3: Commit**

```bash
git add resources/views/layouts/partials/sidebar.blade.php
git add -f docs/superpowers/plans/2026-07-15-participant-report.md
git commit -m "feat(reports): add participant report menu to sidebar

- Convert Laporan to accordion with sub-menu
- Add Laporan Peserta menu item
- Rename existing report to Laporan Keuangan
- Both items under view reports permission

Co-Authored-By: Claude Opus 4.7 <noreply@anthropic.com>"
```

---

### Task 5: Manual Testing & Verification

**Files:**
- No files created/modified (manual testing)

- [ ] **Step 1: Test page loads without error**

1. Login as user with `view reports` permission
2. Navigate to `/reports/participants`
3. Expected: Page loads, table displays (empty or with data)

- [ ] **Step 2: Test filters work**

1. Select Event filter → page reloads with filtered data
2. Select Kontingen filter → combined with event filter
3. Select Search filter → searches by name
4. Click "Reset Filter" → returns to unfiltered state

- [ ] **Step 3: Test pagination**

1. Change "Tampilkan" dropdown to 10, 50, 100
2. Expected: Page reloads with correct per-page count

- [ ] **Step 4: Test export functionality**

1. Click "Export Excel" → Downloads `laporan_peserta.csv`
2. Click "Export PDF" → Downloads `laporan_peserta.pdf`
3. Open both files → Verify data matches table

- [ ] **Step 5: Test mobile view**

1. Resize browser to <992px or test on mobile device
2. Expected: Card layout appears
3. Click "Show Details" → All 12 columns visible

- [ ] **Step 6: Test authorization**

1. Login as user WITHOUT `view reports` permission
2. Navigate to `/reports/participants`
3. Expected: 403 Forbidden error

- [ ] **Step 7: Test data integrity**

1. Verify soft-deleted registrations do NOT appear
2. Verify coach registrations do NOT appear
3. Verify all 12 columns show correct data

- [ ] **Step 8: Commit any fixes**

If any bugs found during testing:

```bash
git add .
git commit -m "fix(reports): address issues found during testing

- [Specific fixes]

Co-Authored-By: Claude Opus 4.7 <noreply@anthropic.com>"
```

---

## Plan Self-Review Results

**Spec Coverage:** All requirements from spec implemented
- 12 columns defined and displayed ✓
- 6 filters with server-side processing ✓
- Pagination with 10, 25, 50, 100 options ✓
- Export PDF and CSV ✓
- Mobile card layout ✓
- Permission middleware ✓
- Excludes soft-deleted and coach registrations ✓

**Placeholder Scan:** No TBD, TODO, or incomplete steps

**Type Consistency:** All variable names, relationships, and column names match the data model

---

## Testing Checklist

After implementation, verify:

- [ ] Route accessible: `/reports/participants`
- [ ] All 12 columns display correctly
- [ ] "Kode Negara" shows "INA"
- [ ] "Age" calculated from birth_date
- [ ] "Min Age" calculated from min_birth_date
- [ ] "Kelas" format: "class - sub_category"
- [ ] "Team" shows "t" for beregu
- [ ] All 6 filters work
- [ ] "Reset Filter" clears all filters
- [ ] Pagination dropdown works
- [ ] Export PDF downloads file
- [ ] Export CSV downloads file
- [ ] Mobile view (<992px) shows cards
- [ ] "Show Details" expands/collapses
- [ ] Unauthorized access returns 403
- [ ] Soft-deleted registrations excluded
- [ ] Coach registrations excluded
