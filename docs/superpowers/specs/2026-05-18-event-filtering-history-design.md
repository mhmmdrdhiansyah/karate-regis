# Event Filtering & History for Kontingen

**Tanggal:** 2026-05-18
**Status:** Proposed
**Author:** Claude Code
**Reviewers:** Pending

## Problem Statement

Kontingen melihat semua event di wizard pendaftaran, termasuk event yang sudah selesai. Hal ini menyebabkan:

1. Event yang sudah `Completed` masih muncul di dashboard kontingen
2. Tidak ada pemisahan antara event aktif vs event selesai
3. Kontingen tidak memiliki akses ke riwayat event dan pembayaran
4. User experience menjadi membingungkan ketika ada banyak event berbarengan

## Goals

1. Menyembunyikan event selesai dari dashboard aktif kontingen
2. Menyediakan halaman riwayat untuk event yang sudah `Completed`
3. Menambahkan halaman riwayat pembayaran untuk bukti dan audit
4. Menjaga data tetap ada di database untuk keperluan reporting

## Solution Overview

### Pendekatan: Filter Berdasarkan Status Event

Kami memilih **Opsi 1** sebagai solusi utama: filter event berdasarkan status lifecycle yang sudah ada.

**Status Event Lifecycle:**
```
Draft → RegistrationOpen → RegistrationClosed → Ongoing → Completed
```

**Kategorisasi Tampilan:**
- **Dashboard Aktif**: Event dengan status `RegistrationOpen`, `RegistrationClosed`, atau `Ongoing`
- **Halaman Riwayat**: Event dengan status `Completed`

### Alasan Memilih Opsi 1

1. **Konsisten** dengan lifecycle event yang sudah ada
2. **Deterministik** — tidak ada arbitrary threshold
3. **Clean** — tidak perlu soft delete untuk kasus yang bukan error
4. **Scalable** — mudah ditambah status baru jika dibutuhkan

## Architecture Design

### Component Overview

```
┌─────────────────────────────────────────────────────────────┐
│                      Frontend Layer                         │
├─────────────────────────────────────────────────────────────┤
│  Dashboard View  │  Event History View  │  Payment History  │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    Controller Layer                         │
├─────────────────────────────────────────────────────────────┤
│  DashboardController  │  EventHistoryController  │  ...     │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                     Service Layer                           │
├─────────────────────────────────────────────────────────────┤
│  RegistrationService                                        │
│  - getActiveEventsForContingent()  (NEW)                   │
│  - getCompletedEventsForContingent() (NEW)                 │
│  - getOpenEvents()                                          │
│  - isRegistrationOpen()                                     │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                      Model Layer                            │
├─────────────────────────────────────────────────────────────┤
│  Event  │  Payment  │  Registration  │  RegistrationDraft   │
└─────────────────────────────────────────────────────────────┘
```

### New Components

| Component | Type | Description |
|-----------|------|-------------|
| `EventHistoryController` | Controller | Handle riwayat event kontingen |
| `PaymentHistoryController` | Controller | Handle riwayat pembayaran (future) |
| `kontingen/history.blade.php` | View | Halaman riwayat event |
| `kontingen/payments.blade.php` | View | Halaman riwayat pembayaran (future) |

### Modified Components

| Component | Changes |
|-----------|---------|
| `DashboardController` | Pass active events ke view kontingen |
| `RegistrationService` | Add 2 new methods untuk filtering |

## API Design

### Service Layer Methods

```php
class RegistrationService
{
    /**
     * Ambil event aktif untuk kontingen (non-completed)
     *
     * Event dianggap aktif jika:
     * - Memiliki payment non-cancelled untuk kontingen
     * - Status: RegistrationOpen, RegistrationClosed, atau Ongoing
     */
    public function getActiveEventsForContingent(int $contingentId): Collection;

    /**
     * Ambil event selesai untuk kontingen
     *
     * Event dianggap selesai jika:
     * - Memiliki payment non-cancelled untuk kontingen
     * - Status: Completed
     */
    public function getCompletedEventsForContingent(int $contingentId): Collection;
}
```

### Routes

```php
// Di routes/web.php, dalam group kontingen
Route::get('/event-history', [EventHistoryController::class, 'index'])
    ->name('kontingen.history');

Route::get('/payment-history', [PaymentHistoryController::class, 'index'])
    ->name('kontingen.payment-history');
```

## UI/UX Design

### Dashboard Kontingen (Updated)

Dashboard kontingen akan menampilkan:

1. **Statistik Peserta** — existing, tidak berubah
2. **Event Aktif** — list event yang sedang berjalan atau masih buka
3. **Shortcut ke Riwayat** — link ke halaman riwayat event

**Visual Layout:**
```
┌─────────────────────────────────────────────────────────┐
│ Dashboard Kontingen - [Nama Kontingen]                  │
├─────────────────────────────────────────────────────────┤
│                                                          │
│ Statistik Peserta                                       │
│ ┌─────┐ ┌─────┐ ┌─────┐ ┌─────┐                        │
│ │  15 │ │  3  │ │  2  │ │ 18/ │                        │
│ │Atlet│ │Pelat││Offi ││ 20  │                        │
│ └─────┘ └─────┘ └─────┘ └─────┘                        │
│                                                          │
│ Event Aktif (2)                            [Lihat Semua →]│
│ ┌─────────────────────────────────────────────────────┐ │
│ │ 🏆 Kejuaraan Karate Piala Presiden                  │ │
│ │ 📅 15 Mei 2026                                       │ │
│ │ Status: Ongoing | 💰 Rp 2.500.000                   │ │
│ │ 👥 12 atlet terdaftar                               │ │
│ │ [Lihat Detail] [Tambah Peserta]                     │ │
│ └─────────────────────────────────────────────────────┘ │
│                                                          │
│ Riwayat Event                                           │
│ [Lihat Riwayat →]                                       │
└─────────────────────────────────────────────────────────┘
```

### Halaman Riwayat Event

**URL:** `/kontingen/event-history`

**Layout:**
- Header: "Riwayat Event - [Nama Kontingen]"
- List card untuk setiap event completed
- Setiap card menampilkan:
  - Nama event
  - Tanggal event
  - Status (Completed)
  - Total peserta
  - Hasil/medali (jika ada)
  - Tombol: Lihat Detail, Lihat Hasil, Download Bukti Bayar

### Halaman Riwayat Pembayaran (Future)

**Scope:** Diimplementasikan di phase selanjutnya

**Fitur:**
- List semua pembayaran yang pernah dilakukan
- Download bukti transfer
- Filter berdasarkan event
- Filter berdasarkan status pembayaran

## Data Flow

### Query untuk Active Events

```php
Event::whereHas('payments', fn($q) => $q
        ->where('contingent_id', $contingentId)
        ->where('status', '!=', PaymentStatus::Cancelled)
    )
    ->whereIn('status', [
        EventStatus::RegistrationOpen,
        EventStatus::RegistrationClosed,
        EventStatus::Ongoing,
    ])
    ->with(['payments' => fn($q) => $q->where('contingent_id', $contingentId)])
    ->orderByDesc('created_at')
    ->get();
```

### Query untuk Completed Events

```php
Event::whereHas('payments', fn($q) => $q
        ->where('contingent_id', $contingentId)
        ->where('status', '!=', PaymentStatus::Cancelled)
    )
    ->where('status', EventStatus::Completed)
    ->with(['payments' => fn($q) => $q->where('contingent_id', $contingentId)])
    ->orderByDesc('event_date')
    ->get();
```

## Edge Cases & Error Handling

### Kasus yang Dihandle

| Scenario | Behavior |
|----------|----------|
| Kontingen tanpa event aktif | Tampilkan pesan "Belum ada event aktif" dengan tombol "Cari Event" |
| Kontingen baru (tanpa riwayat) | Halaman riwayat tampilkan "Belum ada riwayat event" |
| Event completed tanpa hasil | Tampilkan "Hasil belum diumumkan", sembunyikan tombol lihat hasil |
| Payment cancelled | Tidak muncul di active maupun completed events |
| User tanpa kontingen | Redirect ke profil atau tampilkan error |
| Panitia/super-admin akses riwayat | Redirect ke dashboard mereka |

### Authorization

- Gunakan middleware auth untuk semua route
- Pastikan user punya contingent sebelum akses
- Consider using Policy untuk authorization yang lebih robust

## Testing Strategy

### Unit Tests

`tests/Unit/Services/RegistrationServiceTest.php`

```php
test('getActiveEventsForContingent hanya mengembalikan event non-completed')
test('getCompletedEventsForContingent hanya mengembalikan event completed')
test('event dengan cancelled payment tidak termasuk active maupun completed')
test('event tanpa payment tidak termasuk query apapun')
```

### Feature Tests

`tests/Feature/KontingenDashboardTest.php`

```php
test('kontingen bisa lihat event aktif di dashboard')
test('kontingen tidak bisa lihat event completed di dashboard')
test('kontingen tanpa event aktif melihat pesan yang tepat')
test('event dengan status RegistrationOpen muncul di dashboard')
test('event dengan status Ongoing muncul di dashboard')
test('event dengan status Completed tidak muncul di dashboard')
```

`tests/Feature/EventHistoryTest.php`

```php
test('kontingen bisa mengakses halaman riwayat')
test('riwayat menampilkan event completed dengan payment verified')
test('riwayat tidak menampilkan event dengan cancelled payment')
test('user tanpa kontingen tidak bisa akses riwayat')
test('panitia tidak bisa akses halaman riwayat kontingen')
test('event tanpa hasil menampilkan pesan yang tepat')
```

### Test Data Setup

Untuk testing, buat scenario:
- 1 event dengan status RegistrationOpen
- 1 event dengan status Ongoing
- 1 event dengan status Completed
- Payment untuk kontingen di semua event
- 1 payment cancelled untuk edge case

## Implementation Order

1. **Phase 1: Service Layer** — Tambah method ke `RegistrationService`
2. **Phase 2: Controller** — Update `DashboardController`, buat `EventHistoryController`
3. **Phase 3: Views** — Update dashboard view, buat history view
4. **Phase 4: Routes** — Tambah route untuk history
5. **Phase 5: Testing** — Write dan run tests
6. **Phase 6: Payment History** — (Future) Implement riwayat pembayaran

## Code Quality Improvements (Recommended)

Berikut adalah improvements yang disarankan untuk codebase secara umum:

1. **Extract Dashboard Logic**
   - `getEventChartData()` di DashboardController sebaiknya dipindah ke `DashboardService`
   - Dashboard logic cenderung kompleks dan berpotensi grow

2. **Fix Potential N+1 Query**
   - Di line 71 DashboardController: `with(['categories.subCategories.registrations' => ...])`
   - Verify apakah payments perlu di-eager-load juga

3. **Remove Magic Numbers**
   - `->take(5)`, `->take(10)` better extract ke constant atau config
   - Misal: `const DASHBOARD_RECENT_USERS_LIMIT = 5;`

4. **Consider Policy for Authorization**
   - Di `EventRegistrationWizard`, authorization check masih manual
   - Better gunakan Laravel Policy atau dedicated middleware

Catatan: Improvements di atas adalah **nice-to-have**, bukan critical untuk implementasi ini.

## Success Criteria

- [ ] Event dengan status `Completed` tidak muncul di dashboard kontingen
- [ ] Event dengan status aktif muncul di dashboard kontingen
- [ ] Halaman riwayat menampilkan event completed dengan benar
- [ ] Payment cancelled tidak muncul di manapun
- [ ] Semua test pass
- [ ] User bisa navigasi dengan mudah antara dashboard dan riwayat

## Open Questions

Tidak ada open questions untuk saat ini.

## References

- `app/Models/Event.php` — Event model dengan status lifecycle
- `app/Services/RegistrationService.php` — Service untuk business logic pendaftaran
- `app/Http/Controllers/DashboardController.php` — Dashboard controller
- `app/Enums/EventStatus.php` — Enum untuk status event
