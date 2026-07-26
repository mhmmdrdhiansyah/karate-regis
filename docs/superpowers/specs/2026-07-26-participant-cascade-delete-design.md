# Participant Cascade-Delete — Design Spec

**Tanggal:** 2026-07-26
**Status:** Approved (pending spec review)
**Cabang:** `feat/add-institusi-dropdown`

## 1. Latar Belakang & Masalah

Pada halaman **Bank Peserta** (`participants.index` → `<livewire:participant-list />`) dan halaman detail peserta (`participants.show`), tombol hapus memanggil `ParticipantController::destroy`. Saat ini penghapusan sering memicu **HTTP 500** di sistem produksi.

### Root cause (telah ditelusuri)

`ParticipantService::canDelete()` dan constraint database **tidak sepakat** soal soft-delete:

1. Kontingen mendaftarkan atlet → dibuat baris `registrations` di bawah sebuah `payment`.
2. Kontingen **membatalkan pembayaran** via `PaymentList::cancelPayment()` yang menjalankan `$payment->registrations()->delete()` → registrasi menjadi **soft-delete** (baris fisik tetap ada, `deleted_at` terisi).
3. Kontingen kemudian menghapus atlet di Bank Peserta.
4. `canDelete()` memanggil `hasActiveRegistration()` = `registrations()->whereNull('deleted_at')->exists()` → **false** karena registrasi sudah soft-delete → `canDelete()` mengembalikan **true**.
5. `$participant->delete()` menerbitkan `DELETE FROM participants`.
6. DB menolak: FK `registrations.participant_id` bersifat `restrictOnDelete`, dan baris registrasi yang soft-delete **masih ada dan me-refer** peserta → `Integrity constraint violation: 1451 Cannot delete or update a parent row` → **500**.

Terdapat **jalur kedua** yang memicu 500: peserta yang masih ada di keranjang pendaftaran (`registration_draft_items`) — `canDelete()` tidak memeriksa tabel ini, padahal FK-nya juga `restrictOnDelete`.

**Inti:** pengecekan aplikasi hanya melihat registrasi "aktif" dan mengabaikan `registration_draft_items`, sementara DB memblokir selama ada **baris fisik** anak (termasuk yang soft-delete). Ketidakcocokan inilah sumber 500.

## 2. Tujuan

- Menghapus peserta **tanpa 500**, berapapun data yang menyambung.
- Saat menghapus, **semua data yang menyambung milik peserta tersebut ikut terhapus** (termasuk history pertandingan / medal).
- Sebelum hapus, **tampilkan dulu** daftar data yang akan dihapus (history pertandingan: event, kategori, status registrasi, jenis medal) dan **wajib dapat persetujuan eksplisit** dari user.

## 3. Keputusan Desain (locked)

| # | Keputusan | Pilihan |
|---|-----------|---------|
| D1 | Cakupan hapus | **Hapus SEMUA peserta tanpa terkecuali** — termasuk yang terverifikasi & sudah bertanding; `results`/medal ikut terhapus permanen. |
| D2 | Bebas 500 | Ya — via cascade force-delete sesuai urutan FK dalam transaksi. |
| D3 | Konfirmasi | **Lazy endpoint** (`GET delete-preview`) → fetch saat klik → SweetAlert menampilkan rincian asli. |
| D4 | Nominal pembayaran | **Tidak dihitung ulang** — dicatat sebagai keterbatasan (non-goal). |
| D5 | Data milik bersama | `payments` & `registration_drafts` **tidak dihapus** (milik kontingen). |

## 4. Arsitektur / Komponen

### 4.1 `ParticipantService` (inti perbaikan)

Dua method baru:

#### `cascadeDelete(Participant $participant): void`
Dibungkus `DB::transaction`. Urutan **wajib** sesuai `restrictOnDelete`:

1. Ambil semua `registration_id` milik peserta, **termasuk yang soft-delete** (`withTrashed`).
2. Hapus `results` dari registrasi tersebut: `Result::whereIn('registration_id', $ids)->delete()`.
3. **Force-delete** `registrations` peserta: `$participant->registrations()->withTrashed()->forceDelete()`.
4. Hapus `registration_draft_items`: `$participant->draftItems()->delete()`.
5. Hapus file: `deleteFiles($participant)`.
6. Hapus peserta: `$participant->delete()`.

Catatan urutan: `results` harus dihapus sebelum `registrations` (FK `results.registration_id` = `restrictOnDelete`); `registrations` & `registration_draft_items` harus dihapus sebelum `participants`.

#### `getDeleteImpact(Participant $participant): array`
Satu sumber kebenaran untuk konfirmasi. Mengembalikan struktur:
```php
[
    'participant' => ['name' => ..., 'type' => ...],
    'counts' => ['registrations' => int, 'results' => int, 'draft_items' => int],
    'details' => [
        'registrations' => [ ['event' => ..., 'category' => ..., 'status' => ...], ... ], // termasuk soft-deleted
        'results'        => [ ['event' => ..., 'medal' => 'Gold'|'Silver'|'Bronze'], ... ],
    ],
]
```
Menggunakan `withTrashed()` pada `registrations` agar akurat dengan apa yang benar-benar akan dihapus.

### 4.2 `ParticipantController`

- **`destroy(Participant $participant)`** — ganti blok `canDelete()` dengan `cascadeDelete()` di dalam `DB::transaction`. Tetap memanggil `authorizeParticipant()`. Permission tetap dijaga via route middleware (`permission:delete participants`) + `canBeDeletedBy()` (button visibility).
- **`deletePreview(Participant $participant)`** (baru) — mengembalikan `getDeleteImpact()` sebagai JSON. Middleware `permission:delete participants` + `authorizeParticipant()`.
- **`show(...)`** — tombol hapus ditampilkan selama user punya permission; tidak lagi gate pada `$canDelete`. Variabel `canDelete`/`deleteReason` di-view bisa dihapus/dineutralisir.

### 4.3 Route (`routes/web.php`)

Ditambahkan di dalam grup yang sudah ada:
```php
Route::get('participants/{participant}/delete-preview', [ParticipantController::class, 'deletePreview'])
    ->name('participants.delete-preview');
```
(middleware `permission:delete participants` mengikuti pola `destroy`).

### 4.4 Konfirmasi UI (SweetAlert, dua halaman)

Pola: ikuti pola `fetch + CSRF + Swal` yang sudah ada di `admin/document-verification/index.blade.php`.

**Alur klik hapus:**
1. `Swal` loading (`Swal.showLoading()`).
2. `fetch` ke `delete-preview` (GET, header `Accept: application/json`).
3. Render rincian sebagai HTML di `Swal`:
   - **History pertandingan**: daftar event + kategori + status registrasi; daftar medal (jenis).
   - Baris peringatan: *"Semua data di atas — termasuk history pertandingan — akan dihapus permanen."*
   - Catatan: *"Pembayaran & data kontingen lainnya TIDAK ikut dihapus."*
4. Tombol konfirmasi **"Ya, hapus permanen (termasuk history)"** (merah) / **Batal**.
5. Bila konfirmasi → submit form `DELETE` yang sudah ada (`delete-form-{id}` / `m-delete-{id}`).
6. Sukses → toast + redirect ke `participants.index` (sudah ada di controller).

**File view:**
- `resources/views/livewire/participant-list.blade.php` — ganti isi `confirmDelete()` ke pola fetch+Swal di atas. Form `DELETE` & id form tetap dipakai.
- `resources/views/participants/show.blade.php` — tombol hapus tampil saat `$hasDeletePermission` (tanpa gate `$canDelete`); gunakan SweetAlert dengan **pola fetch yang sama** ke `delete-preview` agar tampilan & data identik dengan halaman list (satu sumber kebenaran: `getDeleteImpact()`).

## 5. Data Flow

```
[klik Hapus]
   │
   ▼
fetch GET /participants/{id}/delete-preview  ──► deletePreview() ──► getDeleteImpact()
   │                                                                      │
   ▼                                                                      ▼
Swal(rincian + peringatan) ◄────────────────────── JSON {counts, details}
   │ user klik "Ya, hapus permanen"
   ▼
submit form DELETE /participants/{id}  ──► destroy() ──► cascadeDelete() [transaction]
   │                                                ├─ hapus results
   │                                                ├─ forceDelete registrations (withTrashed)
   │                                                ├─ hapus registration_draft_items
   │                                                ├─ hapus files
   │                                                └─ hapus participant
   ▼
redirect participants.index + toast sukses
```

## 6. Constraint & Integritas

- Semua operasi hapus dalam **satu transaksi** → bila salah langkah gagal, semua di-rollback (tidak ada kondisi setengah-hapus).
- `payments` & `registration_drafts` **tidak tersentuh** → peserta lain di kontingen tetap utuh.
- `verified_by` (FK ke `users`) tidak diubah; user tidak terpengaruh.

## 7. Testing (TDD)

Test fitur baru ditulis **lebih dulu (failing)**, lalu hijau setelah implementasi. Disarankan di `tests/Feature/ParticipantCascadeDeleteTest.php`:

1. **Reproduksi bug utama:** participant dengan **registrasi soft-delete** (simulasi `cancelPayment`) → `DELETE` → respons sukses (bukan 500); baris `registrations` benar-benar hilang; participant hilang.
2. **Jalur draft:** participant dengan `registration_draft_items` → hapus → sukses; draft-item hilang; `registration_draft` tetap ada.
3. **History/medal:** participant dengan `results` (medal) → hapus → medal terhapus.
4. **Isolasi:** participant lain di kontingen yang sama + `payment` milik bersama **tetap utuh** setelah hapus satu participant.
5. **Permission:** user tanpa permission `delete participants` → 403.
6. **Preview endpoint:** `GET delete-preview` mengembalikan struktur JSON yang benar (termasuk registrasi soft-deleted).

## 8. Non-goal / Keterbatasan

- **Nominal pembayaran tidak dihitung ulang.** Bila kontingen sudah membayar lalu satu atlet dihapus, `total_amount` pada `payment` dapat tidak match lagi dengan jumlah peserta. Penanganan ini di luar scope. (Dapat ditindaklanjuti terpisah bila diperlukan.)
- Tidak ada soft-delete pada `Participant`; penghapusan bersifat **permanen** (sesuai wording "permanen" yang sudah ada di UI dan keputusan D1).

## 9. File yang Diubah

| File | Perubahan |
|------|-----------|
| `app/Services/ParticipantService.php` | `+cascadeDelete()`, `+getDeleteImpact()` |
| `app/Http/Controllers/ParticipantController.php` | `destroy()` pakai cascade + transaksi; `+deletePreview()`; `show()` hapus gate `$canDelete` |
| `routes/web.php` | `+GET participants/{participant}/delete-preview` |
| `resources/views/livewire/participant-list.blade.php` | `confirmDelete()` → pola fetch+Swal dengan rincian |
| `resources/views/participants/show.blade.php` | tombol hapus tanpa gate `$canDelete`; SweetAlert |
| `tests/Feature/ParticipantCascadeDeleteTest.php` | test regresi (baru) |

## 10. Risiko

- **Kehilangan data permanen** (medal/history) — dimitigasi via konfirmasi eksplisit yang menampilkan history sebelum hapus (D3).
- **Konsistensi transaksi** — dimitigasi via `DB::transaction`.
- **Permission bypass** — dimitigasi: endpoint & route tetap di middleware `permission:delete participants` + `authorizeParticipant()`.
