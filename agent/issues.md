# Issue: Step 5.1 — Upload Bukti Transfer (User Side)

> **Prioritas:** 🔴 High
> **Modul:** Keuangan & Verifikasi (Phase 5)
> **Prasyarat:** Phase 4 (Engine Pendaftaran) sudah selesai — Payment & Registration records sudah bisa dibuat via invoice wizard.
> **Estimasi:** 3–5 jam kerja

---

## 📋 Ringkasan

User yang sudah membuat invoice pendaftaran harus bisa mengupload bukti transfer untuk pembayaran. Fitur ini mencakup:
- Menampilkan daftar payment milik kontingen user
- Form upload bukti transfer (hanya jika status `pending` atau `rejected`)
- Simpan file ke storage dan path ke database
- Re-upload jika ditolak (status kembali ke `pending`)

---

## 🗂️ Konteks Teknis Yang Sudah Ada

Sebelum mulai coding, **baca dan pahami** file-file berikut:

| File | Kegunaan |
|------|----------|
| `app/Models/Payment.php` | Model Payment — sudah ada method `canUploadProof()` dan `canBeCancelledByUser()` |
| `app/Enums/PaymentStatus.php` | Enum: `Pending`, `Verified`, `Rejected`, `Cancelled` |
| `database/migrations/2026_04_27_080500_create_payments_table.php` | Schema payments — kolom `transfer_proof` (string, nullable) sudah ada |
| `app/Livewire/EventRegistrationInvoice.php` | Contoh Livewire component yang sudah ada — gunakan sebagai referensi pattern |
| `routes/web.php` | Route definitions — cek route group yang sudah ada untuk user (baris 86–100) |
| `resources/views/layouts/partials/sidebar.blade.php` | Sidebar navigation — perlu ditambah menu baru |

### Method Yang Sudah Ada di Model `Payment`

```php
// app/Models/Payment.php — line 60-63
public function canUploadProof(): bool
{
    return in_array($this->status, [PaymentStatus::Pending, PaymentStatus::Rejected]);
}
```

> ⚠️ **JANGAN buat ulang method ini.** Gunakan yang sudah ada.

---

## 📝 Tahapan Implementasi

### Tahap 1: Buat Livewire Component

**File yang dibuat:**
- `app/Livewire/PaymentList.php`

**Langkah:**

1. Jalankan command artisan:
   ```bash
   php artisan make:livewire PaymentList
   ```
   Ini akan membuat 2 file:
   - `app/Livewire/PaymentList.php` (component logic)
   - `resources/views/livewire/payment-list.blade.php` (component view)

2. Di `PaymentList.php`, tambahkan:
   - Attribute `#[Layout('layouts.app')]` di atas class (supaya pakai layout utama)
   - Property `public $proofFile` (untuk handle file upload via Livewire)
   - Property `public ?int $uploadingPaymentId = null` (untuk track payment mana yang sedang di-upload)

3. Buat computed property `payments()` yang mengambil daftar payment milik kontingen user:
   ```php
   #[Computed]
   public function payments()
   {
       $contingent = auth()->user()->contingent;
       
       if (!$contingent) {
           return collect();
       }
       
       return Payment::where('contingent_id', $contingent->id)
           ->with(['event', 'registrations.participant', 'registrations.subCategory'])
           ->orderByDesc('created_at')
           ->get();
   }
   ```

4. Buat method `startUpload(int $paymentId)`:
   - Set `$this->uploadingPaymentId = $paymentId`
   - Reset `$this->proofFile = null`
   - Method ini dipanggil saat user klik tombol "Upload Bukti"

5. Buat method `cancelUpload()`:
   - Reset `$this->uploadingPaymentId = null`
   - Reset `$this->proofFile = null`

6. Buat method `uploadProof()` — ini method utama:
   ```php
   public function uploadProof(): void
   {
       // 1. Validasi file
       $this->validate([
           'proofFile' => ['required', 'image', 'max:5120'], // 5MB
       ]);
       
       // 2. Ambil payment dan cek authorization
       $payment = Payment::where('id', $this->uploadingPaymentId)
           ->where('contingent_id', auth()->user()->contingent->id)
           ->firstOrFail();
       
       // 3. Cek apakah boleh upload (gunakan method yang sudah ada!)
       if (!$payment->canUploadProof()) {
           session()->flash('error', 'Tidak dapat mengupload bukti transfer untuk payment ini.');
           return;
       }
       
       // 4. Simpan file ke storage
       //    PENTING: JANGAN hapus file lama (untuk audit trail)
       $path = $this->proofFile->store('payments/proofs', 'public');
       
       // 5. Update payment
       $payment->update([
           'transfer_proof' => $path,
       ]);
       
       // 6. Jika status rejected, kembalikan ke pending dan clear rejection_reason
       if ($payment->status === PaymentStatus::Rejected) {
           $payment->update([
               'status' => PaymentStatus::Pending->value,
               'rejection_reason' => null,
           ]);
       }
       
       // 7. Reset state dan tampilkan notifikasi
       $this->uploadingPaymentId = null;
       $this->proofFile = null;
       unset($this->payments); // Clear computed cache
       
       session()->flash('success', 'Bukti transfer berhasil diupload.');
   }
   ```

**Import yang diperlukan:**
```php
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
```

> ⚠️ **JANGAN LUPA** tambahkan `use WithFileUploads;` di dalam class. Tanpa ini, Livewire tidak bisa handle file upload.

---

### Tahap 2: Buat Blade View

**File yang dibuat:**
- `resources/views/livewire/payment-list.blade.php`

**Struktur halaman:** Mengikuti pattern Metronic 8 (Bootstrap 5) yang sudah digunakan di project ini.

**Langkah:**

1. Buat layout halaman menggunakan card Metronic:
   ```html
   <div>
       @section('title', 'Daftar Pembayaran')
       
       <div class="container-xxl py-10">
           {{-- Flash Messages --}}
           @if (session()->has('success'))
               <div class="alert alert-success alert-dismissible fade show" role="alert">
                   {{ session('success') }}
                   <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
               </div>
           @endif
           @if (session()->has('error'))
               <div class="alert alert-danger alert-dismissible fade show" role="alert">
                   {{ session('error') }}
                   <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
               </div>
           @endif
           
           {{-- Header --}}
           <div class="card mb-5">
               <div class="card-header border-0 pt-6">
                   <h3 class="card-title align-items-start flex-column">
                       <span class="card-label fw-bolder fs-3 mb-1">Daftar Pembayaran</span>
                       <span class="text-muted mt-1 fw-bold fs-7">Kelola pembayaran pendaftaran kontingen Anda</span>
                   </h3>
               </div>
           </div>
           
           {{-- Daftar Payment Cards --}}
           @forelse($this->payments as $payment)
               {{-- Render per payment card --}}
           @empty
               <div class="card">
                   <div class="card-body text-center py-15">
                       <p class="text-muted fs-5">Belum ada pembayaran. Silakan buat pendaftaran terlebih dahulu.</p>
                   </div>
               </div>
           @endforelse
       </div>
   </div>
   ```

2. **Untuk setiap payment card**, tampilkan informasi:
   - Nama event (`$payment->event->name`)
   - Total tagihan (`Rp {{ number_format($payment->total_amount, 0, ',', '.') }}`)
   - Status badge dengan warna:
     ```html
     @switch($payment->status)
         @case(App\Enums\PaymentStatus::Pending)
             <span class="badge badge-light-warning">Pending</span>
             @break
         @case(App\Enums\PaymentStatus::Verified)
             <span class="badge badge-light-success">Verified</span>
             @break
         @case(App\Enums\PaymentStatus::Rejected)
             <span class="badge badge-light-danger">Rejected</span>
             @break
         @case(App\Enums\PaymentStatus::Cancelled)
             <span class="badge badge-light-dark">Cancelled</span>
             @break
     @endswitch
     ```
   - Tanggal dibuat (`$payment->created_at->translatedFormat('j F Y, H:i')`)
   - Jika ada `rejection_reason`, tampilkan dalam alert merah:
     ```html
     @if($payment->rejection_reason)
         <div class="alert alert-danger d-flex align-items-center p-5 mt-3">
             <i class="bi bi-exclamation-triangle-fill fs-2 text-danger me-4"></i>
             <div>
                 <h4 class="mb-1 text-danger">Alasan Penolakan:</h4>
                 <span>{{ $payment->rejection_reason }}</span>
             </div>
         </div>
     @endif
     ```

3. **Preview bukti transfer** (jika sudah diupload):
   ```html
   @if($payment->transfer_proof)
       <div class="mt-4">
           <h5 class="fw-bold mb-3">Bukti Transfer:</h5>
           <img src="{{ Storage::url($payment->transfer_proof) }}" 
                alt="Bukti Transfer" 
                class="img-fluid rounded border" 
                style="max-height: 300px; cursor: pointer;"
                data-bs-toggle="modal" 
                data-bs-target="#proofModal{{ $payment->id }}">
       </div>
       
       {{-- Modal fullsize view --}}
       <div class="modal fade" id="proofModal{{ $payment->id }}" tabindex="-1">
           <div class="modal-dialog modal-lg modal-dialog-centered">
               <div class="modal-content">
                   <div class="modal-header">
                       <h5 class="modal-title">Bukti Transfer</h5>
                       <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                   </div>
                   <div class="modal-body text-center">
                       <img src="{{ Storage::url($payment->transfer_proof) }}" 
                            alt="Bukti Transfer" class="img-fluid">
                   </div>
               </div>
           </div>
       </div>
   @endif
   ```

4. **Tombol dan form upload** (hanya jika `canUploadProof()` true):
   ```html
   @if($payment->canUploadProof())
       @if($uploadingPaymentId === $payment->id)
           {{-- Form Upload --}}
           <form wire:submit="uploadProof" class="mt-4">
               <div class="mb-3">
                   <label class="form-label fw-bold">Upload Bukti Transfer</label>
                   <input type="file" 
                          wire:model="proofFile" 
                          class="form-control @error('proofFile') is-invalid @enderror" 
                          accept="image/*">
                   @error('proofFile')
                       <div class="invalid-feedback">{{ $message }}</div>
                   @enderror
                   <div class="form-text">Format: JPG, PNG, GIF. Maksimal 5MB.</div>
               </div>
               
               {{-- Preview sebelum upload --}}
               @if($proofFile)
                   <div class="mb-3">
                       <img src="{{ $proofFile->temporaryUrl() }}" 
                            alt="Preview" 
                            class="img-fluid rounded border" 
                            style="max-height: 200px;">
                   </div>
               @endif
               
               <div class="d-flex gap-3">
                   <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                       <span wire:loading.remove wire:target="uploadProof">
                           <i class="bi bi-upload me-1"></i> Upload
                       </span>
                       <span wire:loading wire:target="uploadProof">
                           <span class="spinner-border spinner-border-sm me-1"></span> Mengupload...
                       </span>
                   </button>
                   <button type="button" wire:click="cancelUpload" class="btn btn-light">Batal</button>
               </div>
           </form>
       @else
           {{-- Tombol untuk mulai upload --}}
           <button wire:click="startUpload({{ $payment->id }})" class="btn btn-sm btn-primary mt-3">
               <i class="bi bi-upload me-1"></i>
               {{ $payment->transfer_proof ? 'Upload Ulang Bukti' : 'Upload Bukti Transfer' }}
           </button>
       @endif
   @endif
   ```

5. **Tampilkan daftar registrasi** yang terkait (collapsible):
   ```html
   <div class="mt-4">
       <a class="btn btn-sm btn-light-primary" 
          data-bs-toggle="collapse" 
          href="#registrations{{ $payment->id }}">
           <i class="bi bi-list-ul me-1"></i> Lihat Detail Pendaftaran ({{ $payment->registrations->count() }})
       </a>
       <div class="collapse mt-3" id="registrations{{ $payment->id }}">
           <div class="table-responsive">
               <table class="table table-row-bordered table-row-gray-200 align-middle gs-3 gy-3">
                   <thead>
                       <tr class="fw-bolder text-muted">
                           <th>Nama</th>
                           <th>Tipe</th>
                           <th>Sub-Kategori</th>
                       </tr>
                   </thead>
                   <tbody>
                       @foreach($payment->registrations as $reg)
                           <tr>
                               <td>{{ $reg->participant->name }}</td>
                               <td>
                                   @if($reg->sub_category_id)
                                       <span class="badge badge-light-info">Atlet</span>
                                   @else
                                       <span class="badge badge-light-primary">Pelatih</span>
                                   @endif
                               </td>
                               <td>{{ $reg->subCategory?->name ?? '-' }}</td>
                           </tr>
                       @endforeach
                   </tbody>
               </table>
           </div>
       </div>
   </div>
   ```

> ⚠️ **Loading state:** Tambahkan `wire:loading` indicator saat upload file. Livewire upload file bisa lambat tergantung ukuran.

---

### Tahap 3: Tambahkan Route

**File yang diubah:**
- `routes/web.php`

**Langkah:**

1. Tambahkan route baru di dalam group middleware yang sudah ada (baris 86-100 di `web.php`):
   ```php
   // Di dalam group middleware 'permission:create registrations'
   Route::get('payments', \App\Livewire\PaymentList::class)
       ->name('payments.index');
   ```

2. Letakkan route ini **di dalam** group yang sama dengan route registration (setelah baris 99):
   ```php
   Route::middleware(['permission:create registrations', 'role:super-admin|panitia|kontingen'])->group(function () {
       // ... route registration yang sudah ada ...
       
       // Tambahkan ini:
       Route::get('payments', \App\Livewire\PaymentList::class)
           ->name('payments.index');
   });
   ```

---

### Tahap 4: Tambahkan Menu Sidebar

**File yang diubah:**
- `resources/views/layouts/partials/sidebar.blade.php`

**Langkah:**

1. Cari bagian menu "Pendaftaran" (sekitar baris 160-189)
2. Tambahkan menu item baru **di dalam** accordion menu "Pendaftaran", setelah "Pendaftaran Pelatih":
   ```html
   {{-- Tambahkan setelah menu Pendaftaran Pelatih (baris ~186) --}}
   <div class="menu-item">
       <a class="menu-link {{ request()->routeIs('payments.*') ? 'active' : '' }}"
           href="{{ route('payments.index') }}">
           <span class="menu-bullet">
               <span class="bullet bullet-dot"></span>
           </span>
           <span class="menu-title">Pembayaran</span>
       </a>
   </div>
   ```

---

### Tahap 5: Pastikan Storage Link Sudah Ada

**Command yang dijalankan:**
```bash
php artisan storage:link
```

> Ini membuat symlink dari `public/storage` → `storage/app/public`. Jika sudah pernah dijalankan, command ini akan memberikan pesan bahwa link sudah ada — itu normal, tidak perlu diulang.

---

## ✅ Checklist Verifikasi

Setelah implementasi selesai, **verifikasi semua item berikut:**

| # | Item | Cara Cek |
|---|------|----------|
| 1 | Halaman `/payments` bisa diakses oleh user kontingen | Login sebagai user kontingen → klik menu Pembayaran |
| 2 | Daftar payment tampil dengan status badge berwarna | Cek visual: pending=kuning, verified=hijau, rejected=merah |
| 3 | Tombol "Upload Bukti" muncul HANYA untuk status `pending` dan `rejected` | Cek payment verified/cancelled tidak punya tombol upload |
| 4 | File gambar bisa diupload dan tersimpan | Upload → cek folder `storage/app/public/payments/proofs/` |
| 5 | Preview gambar muncul sebelum dan sesudah upload | Setelah pilih file, preview muncul. Setelah upload, gambar bukti muncul |
| 6 | Validasi file: hanya image, max 5MB | Coba upload PDF atau file >5MB → harus error |
| 7 | Re-upload dari status `rejected` mengubah status ke `pending` | Reject payment → upload ulang → cek status berubah |
| 8 | Re-upload dari `rejected` meng-clear `rejection_reason` | Setelah upload ulang, alasan penolakan hilang |
| 9 | File bukti lama TIDAK dihapus saat upload ulang | Upload → upload lagi → cek 2 file ada di folder storage |
| 10 | User tidak bisa upload untuk payment kontingen lain | Coba manipulasi paymentId → harus error/not found |
| 11 | Menu "Pembayaran" muncul di sidebar | Cek sidebar setelah login |
| 12 | Modal preview gambar berfungsi | Klik gambar bukti → modal muncul dengan gambar fullsize |

---

## 🚨 Hal-Hal Yang JANGAN Dilakukan

1. **JANGAN** buat migration baru — kolom `transfer_proof` sudah ada di tabel `payments`
2. **JANGAN** buat ulang method `canUploadProof()` — sudah ada di model `Payment`
3. **JANGAN** hapus file bukti lama saat upload ulang — simpan untuk audit trail
4. **JANGAN** izinkan upload jika status `verified` atau `cancelled`
5. **JANGAN** lupa tambahkan `use WithFileUploads;` trait di Livewire component
6. **JANGAN** lupa menambahkan `use Illuminate\Support\Facades\Storage;` di blade jika pakai `Storage::url()`
7. **JANGAN** query semua payment dari database — filter by `contingent_id` user yang login

---

## 🔗 Referensi Pattern

Gunakan file-file berikut sebagai referensi pattern coding:

- **Livewire component pattern:** `app/Livewire/EventRegistrationInvoice.php` — contoh computed properties, layout attribute, error handling
- **Blade view pattern:** `resources/views/livewire/event-registration-invoice.blade.php` — contoh card layout Metronic
- **Route pattern:** `routes/web.php` baris 86-100 — contoh route group dengan middleware
- **Sidebar pattern:** `resources/views/layouts/partials/sidebar.blade.php` baris 160-189 — contoh accordion menu item

---

## 📁 Daftar File Yang Dibuat / Diubah

| File | Aksi | Keterangan |
|------|------|------------|
| `app/Livewire/PaymentList.php` | ✨ BUAT BARU | Livewire component logic |
| `resources/views/livewire/payment-list.blade.php` | ✨ BUAT BARU | Blade view untuk daftar payment |
| `routes/web.php` | ✏️ EDIT | Tambah route `payments.index` |
| `resources/views/layouts/partials/sidebar.blade.php` | ✏️ EDIT | Tambah menu "Pembayaran" |

---

## 📐 Diagram Alur Upload

```
┌─────────────────────────────────────────────────────────────┐
│                    User Dashboard                            │
│                                                              │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Payment #1 — Event Karate Open 2026                  │   │
│  │ Total: Rp 1.500.000                                  │   │
│  │ Status: [🟡 Pending]                                 │   │
│  │                                                      │   │
│  │ [📤 Upload Bukti Transfer]                           │   │
│  │                                                      │   │
│  │  ┌─ Form Upload (muncul saat tombol diklik) ──────┐  │   │
│  │  │  [Choose File...] invoice_proof.jpg             │  │   │
│  │  │  Preview: [📷 thumbnail]                        │  │   │
│  │  │  [Upload] [Batal]                               │  │   │
│  │  └────────────────────────────────────────────────┘  │   │
│  └──────────────────────────────────────────────────────┘   │
│                                                              │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Payment #2 — Event Festival Karate 2026              │   │
│  │ Total: Rp 750.000                                    │   │
│  │ Status: [🔴 Rejected]                                │   │
│  │                                                      │   │
│  │ ⚠️ Alasan Penolakan:                                │   │
│  │ "Bukti transfer tidak jelas, mohon upload ulang"     │   │
│  │                                                      │   │
│  │ Bukti Sebelumnya: [📷 gambar lama]                   │   │
│  │ [📤 Upload Ulang Bukti]                              │   │
│  └──────────────────────────────────────────────────────┘   │
│                                                              │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Payment #3 — Event Piala Gubernur 2026               │   │
│  │ Total: Rp 2.000.000                                  │   │
│  │ Status: [🟢 Verified]                                │   │
│  │                                                      │   │
│  │ Bukti Transfer: [📷 gambar]                          │   │
│  │ (Tidak ada tombol upload — sudah verified)           │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔄 State Diagram Upload

```
                    ┌──────────┐
                    │  Invoice │
                    │  Dibuat  │
                    └────┬─────┘
                         │
                         ▼
                  ┌──────────────┐
           ┌──── │   PENDING    │ ◄──────────────────────┐
           │     │ (belum upload)│                        │
           │     └──────┬───────┘                        │
           │            │                                │
           │            │ User upload bukti              │
           │            ▼                                │
           │     ┌──────────────┐                        │
           │     │   PENDING    │                        │
           │     │(sudah upload)│                        │
           │     └──────┬───────┘                        │
           │            │                                │
           │      Admin review                           │
           │       ┌────┴────┐                           │
           │       ▼         ▼                           │
           │ ┌──────────┐ ┌──────────┐                   │
           │ │ VERIFIED │ │ REJECTED │──── User upload ──┘
           │ │    ✅    │ │    ❌    │     ulang → status
           │ └──────────┘ └──────────┘     kembali PENDING
           │
           │ User batalkan
           ▼
    ┌──────────────┐
    │  CANCELLED   │
    │     ⬛       │
    └──────────────┘
```
