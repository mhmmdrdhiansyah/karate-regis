# Participant Report - Design Specification

**Date**: 2026-07-15
**Type**: New Feature
**Priority**: High
**Parent Issue**: #95

## Overview

Create a "Laporan Peserta Terdaftar" (Participant Report) page that displays all registered participants across events with filtering, pagination, and export capabilities.

## Architecture

**Pattern**: Controller + View (consistent with existing payment report)

**Components**:
- `ReportController@participants` — handles filtering, pagination, data loading
- `resources/views/reports/participants.blade.php` — main view with table, filters, export
- Route: `GET /reports/participants` with `permission:view reports` middleware

## Data Model

### Tables Involved
- `participants` — participant master data
- `registrations` — event registrations (soft-deletes)
- `sub_categories` — event sub-categories
- `event_categories` — event categories
- `events` — events
- `contingents` — contingents
- `team_groups` — team groups for beregu events
- `payments` — payment information

### Query Structure

```php
Registration::with(['participant.contingent', 'subCategory.eventCategory.event', 'teamGroup'])
    ->whereNull('deleted_at')           // Exclude soft-deleted registrations
    ->whereNotNull('sub_category_id')   // Exclude coach registrations
    ->join('participants', 'participants.id', '=', 'registrations.participant_id')
    ->join('sub_categories', 'sub_categories.id', '=', 'registrations.sub_category_id')
    ->join('event_categories', 'event_categories.id', '=', 'sub_categories.event_category_id')
    ->join('events', 'events.id', '=', 'event_categories.event_id')
    ->join('contingents', 'contingents.id', '=', 'participants.contingent_id')
```

### Edge Cases Excluded
- Soft-deleted registrations (`registrations.deleted_at IS NULL`)
- Coach registrations (`sub_category_id IS NOT NULL`)

## Report Columns (12 total)

| # | Column Name | Data Source | Calculation/Notes |
|---|-------------|-------------|-------------------|
| 1 | Full Name Kontingen | `contingents.name` | Direct |
| 2 | Short Name Kontingen | `contingents.name` | Same as Full Name |
| 3 | Kode Negara | Hardcoded | `"INA"` |
| 4 | First Name | `participants.institusi` | Direct |
| 5 | Last Name | `participants.name` | Direct |
| 6 | Sex | `participants.gender` | M/F enum |
| 7 | Age | `participants.birth_date` | `Carbon::parse(birth_date)->age` |
| 8 | Kelas | Combined | `event_category.name - sub_category.name` |
| 9 | Sex Category | `sub_categories.gender` | M/F/Mixed enum |
| 10 | Team | Conditional | `"t"` if beregu, else `""` |
| 11 | Name Team | `team_groups.team_name` | Direct (nullable) |
| 12 | Min Age | `event_category.min_birth_date` | `Carbon::parse(min_birth_date)->age` |

### Age Calculations

Both calculated in Controller:

**Age** (Participant's age):
```php
Carbon::parse($participant->birth_date)->age
```

**Min Age** (Category minimum age):
```php
Carbon::parse($eventCategory->min_birth_date)->age
```

## Filtering

Server-side filtering via query string parameters.

### Filter Options

1. **Event** (`event`) — Dropdown from `events` table (All + active events)
2. **Kontingen** (`contingent`) — Dropdown from `contingents` table (All + contingents)
3. **Jenis Kategori** (`category_type`) — Dynamic distinct values from `sub_categories.category_type` (All + beregu, individu, etc.)
4. **Sex** (`gender`) — Dropdown (All + M, F, Mixed)
5. **Status Verifikasi** (`status_berkas`) — Dropdown (All + pending, verified, rejected)
6. **Search** (`search`) — Text input (searches participant name and contingent name)

### Default Filter State
- All filters default to "All" (no filter applied)
- Shows all participants (paginated) on initial load

### Filter Implementation
- Filters applied via controller query builder
- Filter state preserved in URL query string
- "Reset Filter" button clears all filters and redirects to base URL

## Pagination & Sorting

### Pagination Options
- 10, 25, 50, 100 items per page (dropdown)
- Default: 25 items per page

### Default Ordering
- Primary: `contingents.name ASC`
- Secondary: `participants.name ASC`

### Sorting State
- Maintained in URL query string
- Reset only when explicitly changed

## User Interface

### Desktop Layout (≥992px)

**Filter Section**:
- Horizontal filter bar above table
- 6 dropdown filters + search input side-by-side
- Reset button on the right

**Table Section**:
- Full-width responsive table
- 12 columns as specified
- Sortable headers (click to sort, cycle asc/desc/none)
- Row hover effect

**Export Section**:
- Two buttons: "Export PDF" and "Export Excel"
- Located in card header (top-right)

**Pagination Section**:
- Bottom of table
- Per-page dropdown + pagination links
- "Showing X to Y of Z entries" info

### Mobile Layout (<992px)

**Card Layout**:
- Each participant shown as a card
- Initially collapsed showing key info (name, contingent, event)
- "Show Details" button expands to show all 12 columns
- Consistent with payment report mobile pattern

**Filter Section**:
- Stacked vertically
- Full-width inputs
- Reset button below filters

## Export Functionality

### Export Options
1. **PDF Export** — `laporan_peserta.pdf`
2. **CSV/Excel Export** — `laporan_peserta.csv`

### Export Scope
- Exports **all filtered data** (not just current page)
- Filtering determines the data set
- Pagination controls display only

### Export Implementation
- Client-side export using jsPDF + autoTable library
- Same pattern as existing payment report
- Export buttons trigger JavaScript to generate files

## Route & Navigation

### Route Definition

```php
// routes/web.php
Route::get('reports/participants', [ReportController::class, 'participants'])
    ->middleware(['permission:view reports'])
    ->name('reports.participants');
```

### Sidebar Menu

Add under "Operasional" section (accordion):

```blade
@can('view reports')
<div class="menu-item">
    <a class="menu-link {{ request()->routeIs('reports.participants') ? 'active' : '' }}"
        href="{{ route('reports.participants') }}">
        <span class="menu-icon"><i class="bi bi-file-earmark-person-fill fs-3"></i></span>
        <span class="menu-title">Laporan Peserta</span>
    </a>
</div>
@endcan
```

## Authorization

**Permission**: `view reports`
- Applied via route middleware
- Consistent with project convention (permission middleware, not role middleware)
- Returns 403 for unauthorized access

## Testing Checklist

### Functionality
- [ ] Page loads without error at `/reports/participants`
- [ ] Table displays registered participants correctly
- [ ] All 12 columns show correct data
- [ ] "Kode Negara" always shows "INA"
- [ ] "Age" column shows valid numbers
- [ ] "Kelas" column format: "class_name - sub_category_name"
- [ ] "Team" column shows "t" for beregu, empty for individual
- [ ] "Name Team" shows for beregu events
- [ ] "Min Age" column shows valid numbers

### Filters
- [ ] All 6 filters work correctly
- [ ] Filter combinations work properly
- [ ] "Reset Filter" button clears all filters
- [ ] Filter state preserved in URL
- [ ] Search filters by participant name and contingent name

### Pagination & Sortingnah 
- [ ] Pagination options (10, 25, 50, 100) work
- [ ] Default sort order applied (contingent, then name)
- [ ] Sorting headers work correctly
- [ ] Pagination state preserved in URL

### Export
- [ ] PDF export downloads `laporan_peserta.pdf`
- [ ] CSV export downloads `laporan_peserta.csv`
- [ ] Export includes all filtered data
- [ ] Export format is readable

### Responsive
- [ ] Mobile view shows cards at <992px
- [ ] "Show Details" expand/collapse works
- [ ] All columns visible when expanded

### Authorization
- [ ] User without `view reports` permission gets 403
- [ ] User with `view reports` permission can access

### Data Integrity
- [ ] Soft-deleted registrations do NOT appear
- [ ] Coach registrations (sub_category_id IS NULL) do NOT appear
- [ ] No broken relationships (orphaned data)

## Implementation Notes

1. **Follow existing patterns**: Use the same structure as `resources/views/reports/index.blade.php`
2. **Reuse Metronic styles**: Use existing card, table, and form classes
3. **JavaScript for export**: Use jsPDF library (already loaded in payment report)
4. **Age calculations**: Perform in controller, not view
5. **Permission check**: Use `permission:view reports` middleware (not role middleware)

## Dependencies

- Laravel 8.x+
- Livewire (not used for this feature, using Controller + View)
- Metronic UI theme
- jsPDF + autoTable (CDN)
