# Participant Cascade-Delete Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menghapus peserta dari Bank Peserta tanpa 500, dengan cascade force-delete semua data yang menyambung (registrasi, hasil/medal, draft-item) dan konfirmasi SweetAlert yang menampilkan history pertandingan sebelum hapus.

**Architecture:** Tambah `cascadeDelete()` + `getDeleteImpact()` di `ParticipantService` (satu sumber kebenaran). Controller `destroy()` memakai cascade dalam transaksi; endpoint `GET delete-preview` menyuplai rincian untuk konfirmasi. UI (list + detail) diubah ke pola `fetch + SweetAlert` yang sudah ada di codebase.

**Tech Stack:** Laravel 11, Livewire, Spatie Permission, Pest, SweetAlert2, MySQL (FK `restrictOnDelete`).

## Global Constraints

- **Test runner: Pest** dengan `RefreshDatabase` (lihat `tests/Feature/RegistrationDraftUniqueCodeTest.php`). Model tanpa factory (`Registration`, `Payment`, `Result`, `RegistrationDraft`, `RegistrationDraftItem`) dibuat via `Model::create([...])`. Factory yang tersedia: `User`, `Contingent`, `Event`, `EventCategory`, `SubCategory`, `Participant`.
- **Urutan hapus WAJIB** karena FK `restrictOnDelete`: `results` → `registrations` (force, incl. trashed) → `registration_draft_items` → file → `participant`.
- **Permission name:** `delete participants` (digunakan middleware route). Grant di test via `Permission::findOrCreate('delete participants')` + `$user->givePermissionTo(...)`.
- **Ownership check:** `authorizeParticipant()` mensyaratkan `$participant->contingent_id === $user->contingent?->id`. Buat contingent dengan `Contingent::factory()->create(['user_id' => $user->id])` lalu participant dengan `contingent_id` tersebut.
- **RTK:** prefix semua perintah git dengan `rtk` (aturan global user).
- **`docs/` di-gitignore:** commit spec/plan pakai `rtk git add -f <path>`.
- **Commit message** diakhiri `Co-Authored-By: Claude <noreply@anthropic.com>`.
- Enum values: `PaymentStatus::{Pending,Verified,Rejected,Cancelled}`, `RegistrationStatus::{Unsubmitted,PendingReview,Verified,Rejected}`, `MedalType::{Gold,Silver,Bronze}`.

---

## File Structure

| File | Tanggung jawab |
|------|----------------|
| `app/Services/ParticipantService.php` | `+cascadeDelete()` (urutan FK + transaksi), `+getDeleteImpact()` (JSON preview) |
| `app/Http/Controllers/ParticipantController.php` | `destroy()` pakai cascade; `+deletePreview()`; middleware `deletePreview` ikut permission |
| `routes/web.php` | `+GET participants/{participant}/delete-preview` |
| `resources/views/livewire/participant-list.blade.php` | `confirmDelete()` → fetch preview + Swal rincian |
| `resources/views/participants/show.blade.php` | tombol hapus tanpa gate `$canDelete`; Swal fetch; perbaiki copy banner |
| `tests/Feature/ParticipantCascadeDeleteTest.php` | test regresi (Pest, baru) |

---

## Task 1: `cascadeDelete()` + `getDeleteImpact()` di ParticipantService (TDD)

**Files:**
- Create: `tests/Feature/ParticipantCascadeDeleteTest.php`
- Modify: `app/Services/ParticipantService.php`

**Interfaces:**
- Produces: `ParticipantService::cascadeDelete(Participant $participant): void` dan `ParticipantService::getDeleteImpact(Participant $participant): array` (struktur: `['participant' => ['name','type'], 'counts' => ['registrations','results','draft_items'], 'details' => ['registrations' => [...], 'results' => [...]]]`). Dipakai Task 2 & 3 & 4.

- [ ] **Step 1: Tulis test gagal (Pest)** — buat `tests/Feature/ParticipantCascadeDeleteTest.php`:

```php
<?php

use App\Enums\MedalType;
use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use App\Models\Contingent;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Participant;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\RegistrationDraft;
use App\Models\RegistrationDraftItem;
use App\Models\Result;
use App\Models\SubCategory;
use App\Models\User;
use App\Services\ParticipantService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function setupWorld(): array
{
    $user = User::factory()->create();
    $contingent = Contingent::factory()->create(['user_id' => $user->id]);
    $event = Event::factory()->create();
    $category = EventCategory::factory()->create([
        'event_id' => $event->id,
        'min_birth_date' => now()->subYears(20),
        'max_birth_date' => now()->subYears(10),
    ]);
    $subCategory = SubCategory::factory()->create(['event_category_id' => $category->id]);

    return compact('user', 'contingent', 'event', 'category', 'subCategory');
}

it('force-deletes soft-deleted registrations without throwing (regresses the 500 bug)', function () {
    ['contingent' => $contingent, 'event' => $event, 'subCategory' => $subCategory] = setupWorld();

    $participant = Participant::factory()->create(['contingent_id' => $contingent->id]);
    $payment = Payment::create([
        'contingent_id' => $contingent->id,
        'event_id' => $event->id,
        'total_amount' => 100000,
        'status' => PaymentStatus::Cancelled,
    ]);
    $registration = Registration::create([
        'participant_id' => $participant->id,
        'payment_id' => $payment->id,
        'sub_category_id' => $subCategory->id,
        'status_berkas' => RegistrationStatus::PendingReview,
    ]);

    // Simulate the production trigger: PaymentList::cancelPayment() soft-deletes the registration.
    $registration->delete();
    expect(Registration::withTrashed()->count())->toBe(1);

    // Previously this would throw a 1451 FK violation. Cascade must succeed.
    app(ParticipantService::class)->cascadeDelete($participant);

    expect(Participant::find($participant->id))->toBeNull()
        ->and(Registration::withTrashed()->count())->toBe(0)
        ->and(Payment::find($payment->id))->not->toBeNull(); // shared payment survives
});

it('deletes registration draft items belonging to the participant', function () {
    ['contingent' => $contingent, 'event' => $event, 'subCategory' => $subCategory] = setupWorld();

    $participant = Participant::factory()->create(['contingent_id' => $contingent->id]);
    $draft = RegistrationDraft::create([
        'contingent_id' => $contingent->id,
        'event_id' => $event->id,
        'status' => 'draft',
    ]);
    RegistrationDraftItem::create([
        'registration_draft_id' => $draft->id,
        'participant_id' => $participant->id,
        'sub_category_id' => $subCategory->id,
    ]);

    app(ParticipantService::class)->cascadeDelete($participant);

    expect(Participant::find($participant->id))->toBeNull()
        ->and(RegistrationDraftItem::count())->toBe(0)
        ->and(RegistrationDraft::find($draft->id))->not->toBeNull(); // shared draft survives
});

it('deletes competition results/medals tied to the participant', function () {
    ['contingent' => $contingent, 'event' => $event, 'subCategory' => $subCategory] = setupWorld();

    $participant = Participant::factory()->create(['contingent_id' => $contingent->id]);
    $payment = Payment::create([
        'contingent_id' => $contingent->id,
        'event_id' => $event->id,
        'total_amount' => 100000,
        'status' => PaymentStatus::Verified,
    ]);
    $registration = Registration::create([
        'participant_id' => $participant->id,
        'payment_id' => $payment->id,
        'sub_category_id' => $subCategory->id,
        'status_berkas' => RegistrationStatus::Verified,
    ]);
    Result::create([
        'registration_id' => $registration->id,
        'medal_type' => MedalType::Gold,
    ]);

    app(ParticipantService::class)->cascadeDelete($participant);

    expect(Result::count())->toBe(0)
        ->and(Registration::withTrashed()->count())->toBe(0);
});

it('does not affect other participants or their data in the same contingent', function () {
    ['contingent' => $contingent, 'event' => $event, 'subCategory' => $subCategory] = setupWorld();

    $a = Participant::factory()->create(['contingent_id' => $contingent->id]);
    $b = Participant::factory()->create(['contingent_id' => $contingent->id]);
    $payment = Payment::create([
        'contingent_id' => $contingent->id,
        'event_id' => $event->id,
        'total_amount' => 200000,
        'status' => PaymentStatus::Verified,
    ]);
    Registration::create([
        'participant_id' => $a->id, 'payment_id' => $payment->id,
        'sub_category_id' => $subCategory->id, 'status_berkas' => RegistrationStatus::Verified,
    ]);
    Registration::create([
        'participant_id' => $b->id, 'payment_id' => $payment->id,
        'sub_category_id' => $subCategory->id, 'status_berkas' => RegistrationStatus::Verified,
    ]);

    app(ParticipantService::class)->cascadeDelete($a);

    expect(Participant::find($a->id))->toBeNull()
        ->and(Participant::find($b->id))->not->toBeNull()
        ->and(Registration::where('participant_id', $b->id)->count())->toBe(1)
        ->and(Payment::find($payment->id))->not->toBeNull();
});

it('reports delete impact including soft-deleted registrations', function () {
    ['contingent' => $contingent, 'event' => $event, 'subCategory' => $subCategory] = setupWorld();

    $participant = Participant::factory()->create([
        'contingent_id' => $contingent->id,
        'name' => 'Atlet Uji',
    ]);
    $payment = Payment::create([
        'contingent_id' => $contingent->id, 'event_id' => $event->id,
        'total_amount' => 100000, 'status' => PaymentStatus::Cancelled,
    ]);
    $registration = Registration::create([
        'participant_id' => $participant->id, 'payment_id' => $payment->id,
        'sub_category_id' => $subCategory->id, 'status_berkas' => RegistrationStatus::PendingReview,
    ]);
    $registration->delete(); // soft-deleted

    $impact = app(ParticipantService::class)->getDeleteImpact($participant);

    expect($impact['participant']['name'])->toBe('Atlet Uji')
        ->and($impact['counts']['registrations'])->toBe(1)
        ->and($impact['counts']['results'])->toBe(0)
        ->and($impact['counts']['draft_items'])->toBe(0)
        ->and($impact['details']['registrations'])->toHaveCount(1);
});
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test --filter=ParticipantCascadeDeleteTest`
Expected: FAIL — `Call to undefined method App\Services\ParticipantService::cascadeDelete()`.

- [ ] **Step 3: Implementasi method di `app/Services/ParticipantService.php`**

Tambah dua `use` di atas file (setelah baris `use App\Models\Participant;`):
```php
use App\Models\Registration;
use App\Models\RegistrationDraftItem;
use App\Models\Result;
use Illuminate\Support\Facades\DB;
```

Tambah dua method ini ke dalam class `ParticipantService` (sebelum `autoVerifyIfNeeded`):

```php
    /**
     * Soft-deleted registrations + draft items still block the participant's
     * deletion at the DB level (FK restrictOnDelete), which is the source of
     * the 500 in production. Force-remove every child row in FK order, then
     * delete the participant — all inside a transaction.
     */
    public function cascadeDelete(Participant $participant): void
    {
        DB::transaction(function () use ($participant) {
            $registrationIds = $participant->registrations()
                ->withTrashed()
                ->pluck('id');

            // 1. Results first (FK results.registration_id = restrictOnDelete).
            Result::whereIn('registration_id', $registrationIds)->delete();

            // 2. Force-delete registrations incl. trashed (FK participant_id = restrictOnDelete).
            $participant->registrations()->withTrashed()->forceDelete();

            // 3. Draft items (FK registration_draft_items.participant_id = restrictOnDelete).
            $participant->draftItems()->delete();

            // 4. Uploaded files.
            $this->deleteFiles($participant);

            // 5. The participant.
            $participant->delete();
        });
    }

    /**
     * Structured preview of everything cascadeDelete() will remove.
     * Includes soft-deleted registrations so the warning matches reality.
     */
    public function getDeleteImpact(Participant $participant): array
    {
        $registrations = $participant->registrations()
            ->withTrashed()
            ->with(['payment.event', 'subCategory.eventCategory'])
            ->get();

        $results = Result::whereIn('registration_id', $registrations->pluck('id'))
            ->with('registration.payment.event')
            ->get();

        return [
            'participant' => [
                'name' => $participant->name,
                'type' => $participant->type?->value,
            ],
            'counts' => [
                'registrations' => $registrations->count(),
                'results' => $results->count(),
                'draft_items' => $participant->draftItems()->count(),
            ],
            'details' => [
                'registrations' => $registrations->map(fn ($r) => [
                    'event' => $r->payment?->event?->name ?? '-',
                    'category' => $r->subCategory?->eventCategory?->name ?? '-',
                    'status' => $r->status_berkas?->value,
                ])->values()->all(),
                'results' => $results->map(fn ($res) => [
                    'event' => $res->registration?->payment?->event?->name ?? '-',
                    'medal' => $res->medal_type?->value,
                ])->values()->all(),
            ],
        ];
    }
```

- [ ] **Step 4: Jalankan test, pastikan lulus**

Run: `php artisan test --filter=ParticipantCascadeDeleteTest`
Expected: PASS (5 tests).

- [ ] **Step 5: Commit**

```bash
rtk git add app/Services/ParticipantService.php tests/Feature/ParticipantCascadeDeleteTest.php
rtk git commit -m "feat(participant): cascade-delete service with delete-impact preview

Co-Authored-By: Claude <noreply@anthropic.com>"
```

---

## Task 2: Hubungkan controller + route (TDD)

**Files:**
- Modify: `app/Http/Controllers/ParticipantController.php` (constructor middleware, `destroy()`, `+deletePreview()`)
- Modify: `routes/web.php` (route `delete-preview`)
- Modify: `tests/Feature/ParticipantCascadeDeleteTest.php` (tambah 2 test HTTP)

**Interfaces:**
- Consumes: `ParticipantService::cascadeDelete()`, `ParticipantService::getDeleteImpact()` (dari Task 1).
- Produces: route `participants.delete-preview` (GET) dan `participants.destroy` (DELETE) yang kini selalu cascade. Dipakai Task 3 & 4 via `route('participants.delete-preview', $participant)`.

- [ ] **Step 1: Tambah 2 test HTTP ke akhir `tests/Feature/ParticipantCascadeDeleteTest.php`**

Tambah `use`:
```php
use Spatie\Permission\Models\Permission;
```
Lalu tempel dua test ini di akhir file:
```php
it('returns delete-preview as json for an authorized user', function () {
    ['user' => $user, 'contingent' => $contingent] = setupWorld();
    Permission::findOrCreate('delete participants');
    $user->givePermissionTo('delete participants');

    $participant = Participant::factory()->create(['contingent_id' => $contingent->id]);

    $this->actingAs($user)
        ->getJson(route('participants.delete-preview', $participant))
        ->assertOk()
        ->assertJsonStructure([
            'participant' => ['name', 'type'],
            'counts' => ['registrations', 'results', 'draft_items'],
            'details' => ['registrations', 'results'],
        ]);
});

it('deletes a participant with connected data over HTTP without a 500', function () {
    ['user' => $user, 'contingent' => $contingent, 'event' => $event, 'subCategory' => $subCategory] = setupWorld();
    Permission::findOrCreate('delete participants');
    $user->givePermissionTo('delete participants');

    $participant = Participant::factory()->create(['contingent_id' => $contingent->id]);
    $payment = Payment::create([
        'contingent_id' => $contingent->id, 'event_id' => $event->id,
        'total_amount' => 100000, 'status' => PaymentStatus::Cancelled,
    ]);
    $registration = Registration::create([
        'participant_id' => $participant->id, 'payment_id' => $payment->id,
        'sub_category_id' => $subCategory->id, 'status_berkas' => RegistrationStatus::PendingReview,
    ]);
    $registration->delete(); // reproduce the exact production 500 trigger

    $this->actingAs($user)
        ->delete(route('participants.destroy', $participant))
        ->assertRedirect(route('participants.index'));

    expect(Participant::find($participant->id))->toBeNull()
        ->and(Registration::withTrashed()->count())->toBe(0)
        ->and(Payment::find($payment->id))->not->toBeNull();
});
```

- [ ] **Step 2: Jalankan, pastikan gagal** (`deletePreview` belum ada & `destroy` masih gate `canDelete`)

Run: `php artisan test --filter=ParticipantCascadeDeleteTest`
Expected: 2 test baru FAIL (route `participants.delete-preview` tidak ada).

- [ ] **Step 3: Update controller**

Di `app/Http/Controllers/ParticipantController.php`, ubah baris middleware constructor:
```php
$this->middleware('permission:delete participants')->only(['destroy']);
```
→
```php
$this->middleware('permission:delete participants')->only(['destroy', 'deletePreview']);
```

Ganti seluruh method `destroy()` menjadi:
```php
    public function destroy(Participant $participant)
    {
        $this->authorizeParticipant($participant);

        $this->participantService->cascadeDelete($participant);

        return redirect()->route('participants.index')->with('success', 'Peserta berhasil dihapus.');
    }
```

Tambah method baru `deletePreview()` tepat setelah `destroy()`:
```php
    public function deletePreview(Participant $participant)
    {
        $this->authorizeParticipant($participant);

        return response()->json(
            $this->participantService->getDeleteImpact($participant)
        );
    }
```

- [ ] **Step 4: Tambah route**

Di `routes/web.php`, ubah blok grup permission `delete participants` (sekitar baris 63-65):
```php
    Route::middleware(['permission:delete participants'])->group(function () {
        Route::delete('participants/{participant}', [ParticipantController::class, 'destroy'])->name('participants.destroy');
    });
```
→
```php
    Route::middleware(['permission:delete participants'])->group(function () {
        Route::delete('participants/{participant}', [ParticipantController::class, 'destroy'])->name('participants.destroy');
        Route::get('participants/{participant}/delete-preview', [ParticipantController::class, 'deletePreview'])->name('participants.delete-preview');
    });
```

- [ ] **Step 5: Jalankan semua test ParticipantCascadeDelete, pastikan lulus**

Run: `php artisan test --filter=ParticipantCascadeDeleteTest`
Expected: PASS (7 tests).

- [ ] **Step 6: Commit**

```bash
rtk git add app/Http/Controllers/ParticipantController.php routes/web.php tests/Feature/ParticipantCascadeDeleteTest.php
rtk git commit -m "feat(participant): cascade-delete endpoint + delete-preview route

Co-Authored-By: Claude <noreply@anthropic.com>"
```

---

## Task 3: Konfirmasi SweetAlert di halaman Bank Peserta (list)

**Files:**
- Modify: `resources/views/livewire/participant-list.blade.php` (ganti blok `<script>` baris 247-266)

**Interfaces:**
- Consumes: `route('participants.delete-preview', ['participant' => id])` (GET, JSON) dari Task 2; form `delete-form-{id}` / `m-delete-{id}` yang sudah ada di view.

- [ ] **Step 1: Ganti seluruh blok `<script>...</script>`** (baris 247-266) dengan:

```html
    <script>
        window.participantDeletePreviewUrl = (id) =>
            "{{ route('participants.delete-preview', '__ID__') }}".replace('__ID__', id);

        function renderDeleteImpactHtml(impact) {
            const c = impact.counts || {};
            const regs = (impact.details && impact.details.registrations) || [];
            const medals = (impact.details && impact.details.results) || [];

            let html = '<div class="text-start">';
            html += '<p class="fw-bold text-danger mb-2">Data berikut akan dihapus permanen:</p>';
            html += '<ul class="small text-start mb-2">'
                + '<li><b>' + c.registrations + '</b> registrasi pendaftaran</li>'
                + '<li><b>' + c.results + '</b> hasil/medal pertandingan</li>'
                + '<li><b>' + c.draft_items + '</b> item keranjang draft</li>'
                + '</ul>';

            if (medals.length) {
                html += '<p class="small text-danger mb-1"><b>History pertandingan (medal):</b></p><ul class="small text-start">';
                medals.forEach(m => html += '<li>' + (m.event || '-') + ' — ' + (m.medal || '-') + '</li>');
                html += '</ul>';
            }
            if (regs.length) {
                html += '<p class="small text-muted mb-1"><b>Pendaftaran:</b></p><ul class="small text-start">';
                regs.forEach(r => html += '<li>' + (r.event || '-') + ' — ' + (r.category || '-') + '</li>');
                html += '</ul>';
            }

            html += '<p class="small text-muted mt-2 mb-0">Pembayaran &amp; data kontingen lainnya <b>tidak</b> ikut dihapus.</p>';
            html += '</div>';
            return html;
        }

        function confirmDelete(e, id) {
            e.preventDefault();
            Swal.fire({ title: 'Memuat data...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            fetch(window.participantDeletePreviewUrl(id), { headers: { 'Accept': 'application/json' } })
                .then(r => r.ok ? r.json() : Promise.reject(r))
                .then(impact => {
                    Swal.fire({
                        title: 'Hapus peserta ini?',
                        html: renderDeleteImpactHtml(impact),
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, hapus permanen',
                        cancelButtonText: 'Batal'
                    }).then(result => {
                        if (result.isConfirmed) {
                            (document.getElementById('delete-form-' + id) || document.getElementById('m-delete-' + id))?.submit();
                        }
                    });
                })
                .catch(() => Swal.fire('Gagal', 'Tidak dapat memuat data. Coba lagi.', 'error'));
        }
    </script>
```

- [ ] **Step 2: Verifikasi manual**

- Jalankan `php artisan serve`, login sebagai user kontingen dengan permission hapus.
- Buka halaman Bank Peserta, klik tombol hapus pada peserta yang punya registrasi (atau registrasi soft-delete). Konfirmasi harus memuat rincian (jumlah + history) lalu menghapus tanpa 500.

- [ ] **Step 3: Commit**

```bash
rtk git add resources/views/livewire/participant-list.blade.php
rtk git commit -m "feat(participant): show delete-impact preview in Bank Peserta confirm

Co-Authored-By: Claude <noreply@anthropic.com>"
```

---

## Task 4: Konfirmasi SweetAlert di halaman detail + perbaikan copy banner

**Files:**
- Modify: `resources/views/participants/show.blade.php` (copy banner baris 12-13 & 26; blok tombol hapus baris 93-109; blok `@push('scripts')` baris 365-378)

**Interfaces:**
- Consumes: `route('participants.delete-preview', $participant)` (GET, JSON) dari Task 2. View composer `AppServiceProvider` tetap menyuntik `hasEditPermission` & `hasDeletePermission`.

- [ ] **Step 1: Perbaiki copy banner (hapus klaim "tidak dapat dihapus")**

Ganti baris 12-13:
```
                    NIK, tanggal lahir, dan jenis kelamin tidak dapat diubah. Peserta tidak dapat dihapus.
```
→
```
                    NIK, tanggal lahir, dan jenis kelamin tidak dapat diubah.
```

Ganti baris 26:
```
                    Semua field terkunci kecuali foto. Peserta tidak dapat dihapus.
```
→
```
                    Semua field terkunci kecuali foto.
```

- [ ] **Step 2: Ganti blok tombol hapus** (baris 93-109) dengan:

```blade
                            @if ($hasDeletePermission)
                                <button type="button" class="btn btn-light-danger btn-sm me-2" onclick="confirmDeleteParticipant()">
                                    <i class="bi bi-trash me-1"></i> Hapus
                                </button>
                                <form action="{{ route('participants.destroy', $participant) }}" method="POST"
                                    id="participant-delete-form" style="display:none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            @elseif(!$hasDeletePermission)
                                <span class="btn btn-light-danger btn-sm me-2 opacity-50 cursor-default"
                                    data-bs-toggle="tooltip" data-bs-placement="bottom"
                                    title="Anda tidak memiliki izin menghapus peserta ini"
                                    style="cursor: not-allowed;">
                                    <i class="bi bi-trash me-1"></i> Hapus
                                </span>
                            @endif
```

- [ ] **Step 3: Ganti blok `@push('scripts')`** (baris 365-378) dengan:

```blade
    @push('scripts')
        <script>
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
                new bootstrap.Tooltip(el);
            });

            @if (session('success'))
                toastr.success(@js(session('success')));
            @endif
            @if ($errors->has('delete'))
                toastr.error(@js($errors->first('delete')));
            @endif

            window.participantDeletePreviewUrl = "{{ route('participants.delete-preview', $participant) }}";

            function renderDeleteImpactHtml(impact) {
                const c = impact.counts || {};
                const regs = (impact.details && impact.details.registrations) || [];
                const medals = (impact.details && impact.details.results) || [];

                let html = '<div class="text-start">';
                html += '<p class="fw-bold text-danger mb-2">Data berikut akan dihapus permanen:</p>';
                html += '<ul class="small text-start mb-2">'
                    + '<li><b>' + c.registrations + '</b> registrasi pendaftaran</li>'
                    + '<li><b>' + c.results + '</b> hasil/medal pertandingan</li>'
                    + '<li><b>' + c.draft_items + '</b> item keranjang draft</li>'
                    + '</ul>';

                if (medals.length) {
                    html += '<p class="small text-danger mb-1"><b>History pertandingan (medal):</b></p><ul class="small text-start">';
                    medals.forEach(m => html += '<li>' + (m.event || '-') + ' — ' + (m.medal || '-') + '</li>');
                    html += '</ul>';
                }
                if (regs.length) {
                    html += '<p class="small text-muted mb-1"><b>Pendaftaran:</b></p><ul class="small text-start">';
                    regs.forEach(r => html += '<li>' + (r.event || '-') + ' — ' + (r.category || '-') + '</li>');
                    html += '</ul>';
                }

                html += '<p class="small text-muted mt-2 mb-0">Pembayaran &amp; data kontingen lainnya <b>tidak</b> ikut dihapus.</p>';
                html += '</div>';
                return html;
            }

            function confirmDeleteParticipant() {
                Swal.fire({ title: 'Memuat data...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                fetch(window.participantDeletePreviewUrl, { headers: { 'Accept': 'application/json' } })
                    .then(r => r.ok ? r.json() : Promise.reject(r))
                    .then(impact => {
                        Swal.fire({
                            title: 'Hapus peserta ini?',
                            html: renderDeleteImpactHtml(impact),
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Ya, hapus permanen',
                            cancelButtonText: 'Batal'
                        }).then(result => {
                            if (result.isConfirmed) {
                                document.getElementById('participant-delete-form')?.submit();
                            }
                        });
                    })
                    .catch(() => Swal.fire('Gagal', 'Tidak dapat memuat data. Coba lagi.', 'error'));
            }
        </script>
    @endpush
```

- [ ] **Step 4: Verifikasi manual**

- Buka `participants.show` untuk peserta yang punya registrasi/medal; klik Hapus → Swal menampilkan history → konfirmasi → redirect ke list tanpa 500.
- Pastikan tombol hapus muncul untuk user dengan permission, dan disabled (tooltip) untuk yang tanpa permission.

- [ ] **Step 5: Commit**

```bash
rtk git add resources/views/participants/show.blade.php
rtk git commit -m "feat(participant): delete-impact preview on detail page + fix banner copy

Co-Authored-By: Claude <noreply@anthropic.com>"
```

---

## Task 5: Verifikasi akhir seluruh suite

**Files:** —

- [ ] **Step 1: Jalankan seluruh test suite**

Run: `php artisan test`
Expected: semua lulus, tidak ada regresi (termasuk `MultiTeamRegistrationTest`).

- [ ] **Step 2: Verifikasi alur produksi end-to-end (manual)**

Reproduksi skenario 500 yang asli: kontingen daftar atlet → batalkan pembayaran (registrasi soft-delete) → hapus atlet → harus sukses, tidak 500, child hilang, pembayaran & peserta lain utuh.

---

## Self-Review (sudah dilakukan penulis plan)

- **Spec coverage:** D1 (hapus semua) → Task 1 `cascadeDelete` tanpa gate canDelete; D2 (bebas 500) → Task 1 urutan FK + Task 5 reproduksi; D3 (lazy preview) → Task 2 endpoint + Task 3/4 Swal; D4 (nominal tidak dihitung ulang) → non-goal, tidak ada task (sesuai); D5 (data shared tidak dihapus) → Task 1 test isolasi.
- **Placeholder scan:** tidak ada TBD/TODO; semua kode lengkap.
- **Type consistency:** `cascadeDelete(Participant): void` & `getDeleteImpact(Participant): array` konsisten dipakai Task 1-4. Nama JS function `renderDeleteImpactHtml` + `confirmDeleteParticipant`/`confirmDelete` konsisten. Route name `participants.delete-preview` konsisten.
