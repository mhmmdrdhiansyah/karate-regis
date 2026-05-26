# 📋 Panduan Perencanaan & Implementasi Fitur (Untuk Junior Programmer & Model AI)

Dokumen ini berisi panduan langkah-demi-langkah (technical implementation design) untuk dua fitur utama:
1. **Step 5.1 — Upload Bukti Transfer (User Side)**
2. **Laporan Halaman — Sorting & Exporting (Generic & Multi-Tab Friendly)**

---

## 🛠️ Bagian 1: Step 5.1 — Upload Bukti Transfer (Sisi User/Kontingen)

### 📌 Goal
User dari kontingen dapat mengunggah bukti transfer pembayaran untuk invoice pendaftaran mereka yang berstatus `pending` atau `rejected`.

### 🗂️ Check-list File & Arsitektur
- **Model:** `App\Models\Payment` (Sudah harus memiliki status enum, helper `canUploadProof()`, dan `canBeCancelledByUser()`).
- **Livewire Component:** `App\Livewire\PaymentList.php` (Komponen utama untuk menampilkan dan mengunggah).
- **Blade View:** `resources/views/livewire/payment-list.blade.php` (Frontend UI dengan template Metronic).
- **Routes:** `routes/web.php` (Registrasi route di bawah middleware kontingen).

---

### 🚶 Tahapan Implementasi Backend (Livewire Component)

#### 1. Setup Class & Library File Uploads
Gunakan trait `WithFileUploads` dari Livewire agar form dapat menerima file attachment:
```php
use Livewire\WithFileUploads;

class PaymentList extends Component
{
    use WithFileUploads;

    public $proofFile; // Menyimpan temporary file yang di-upload
    public ?int $uploadingPaymentId = null; // ID payment yang sedang di-upload buktinya
}
```

#### 2. Eager Loading Data Payments Kontingen
Ambil data pembayaran khusus milik kontingen user yang sedang login dengan relasi `event` dan `registrations`:
```php
#[Computed]
public function payments()
{
    $contingent = auth()->user()->contingent;
    if (!$contingent) return collect();

    return Payment::where('contingent_id', $contingent->id)
        ->with(['event', 'registrations.participant', 'registrations.subCategory'])
        ->orderByDesc('created_at')
        ->get();
}
```

#### 3. Logika Upload Bukti Transfer
Buat method `uploadProof()` dengan alur sebagai berikut:
- **Validasi Input:** Wajib diisi (`required`), tipe file gambar (`image`), ukuran maksimal 5MB (`max:5120`).
- **Validasi Status:** Pastikan status saat ini adalah `pending` atau `rejected` melalui model helper `$payment->canUploadProof()`.
- **Penyimpanan Berkas (Audit Trail):** Simpan file ke folder `payments/proofs` pada disk `public`. 
  > [!IMPORTANT]
  > **JANGAN** menghapus file bukti transfer lama agar kita memiliki riwayat audit trail (jejak audit) yang lengkap jika terjadi dispute pembayaran.
- **Update Database:** Simpan path baru ke kolom `transfer_proof`. Jika status saat ini `rejected` dan user melakukan upload ulang, ubah status kembali ke `pending` dan hapus isi kolom `rejection_reason` (`null`).

```php
public function uploadProof(): void
{
    $this->validate([
        'proofFile' => ['required', 'image', 'max:5120'],
    ]);

    $payment = Payment::where('id', $this->uploadingPaymentId)
        ->where('contingent_id', auth()->user()->contingent->id)
        ->firstOrFail();

    if (!$payment->canUploadProof()) {
        session()->flash('error', 'Tidak dapat mengunggah bukti untuk transaksi ini.');
        return;
    }

    // Simpan file
    $path = $this->proofFile->store('payments/proofs', 'public');

    // Update data
    $updateData = ['transfer_proof' => $path];
    if ($payment->status === PaymentStatus::Rejected) {
        $updateData['status'] = PaymentStatus::Pending->value;
        $updateData['rejection_reason'] = null;
    }
    
    $payment->update($updateData);

    // Reset state
    $this->uploadingPaymentId = null;
    $this->proofFile = null;
    unset($this->payments); // Hapus computed cache agar UI refresh

    session()->flash('success', 'Bukti transfer berhasil diunggah.');
}
```

#### 4. Logika Pembatalan Transaksi oleh Kontingen
Jika transaksi masih `pending` atau `rejected`, berikan opsi tombol **"Batalkan Pendaftaran"** dengan alur:
- Set status pembayaran menjadi `cancelled`.
- Lakukan **soft-delete** pada semua data `registrations` terkait pembayaran tersebut agar kuota atlet/pelatih dibebaskan kembali.
- Catat aktivitas pembatalan ini di dalam tabel `activity_logs`.
- Bungkus semua operasi di atas dalam satu **Database Transaction** (`DB::transaction`).

---

### 🎨 Tahapan Implementasi Frontend (Blade View)

1. **Gunakan Warna Status yang Sesuai (Metronic Badge System):**
   - `Pending` $\rightarrow$ Badge Kuning (`badge-light-warning`).
   - `Verified` $\rightarrow$ Badge Hijau (`badge-light-success`).
   - `Rejected` $\rightarrow$ Badge Merah (`badge-light-danger`).
   - `Cancelled` $\rightarrow$ Badge Gelap/Abu-abu (`badge-light-dark`).

2. **Form Upload Interaktif:**
   - Gunakan `wire:model.live="proofFile"` pada file input untuk mengaktifkan real-time validation.
   - Tambahkan preview gambar instan menggunakan `$proofFile->temporaryUrl()` sebelum tombol submit ditekan.
   - Tampilkan indikator loading menggunakan `wire:loading wire:target="proofFile"` agar user tahu file sedang diproses di server.

3. **Detail Invoice Terenkapsulasi:**
   - Gunakan elemen Bootstrap collapse (`data-bs-toggle="collapse"`) untuk membungkus daftar nama atlet/pelatih agar tampilan halaman tetap bersih dan tidak terlalu panjang (ramah perangkat mobile).

---
---

## 📈 Bagian 2: Laporan Halaman — Sorting & Exporting (Generic & Multi-Tab Friendly)

### 📌 Goal
Halaman laporan memiliki tabel transaksi yang dapat diurutkan (sorting) secara dinamis di sisi klien untuk semua kolom, dan diexport (PDF / Excel) sesuai dengan urutan baris data yang sedang tampil di layar.

---

### ⚠️ Masalah Utama pada Implementasi Sebelumnya (Penting untuk Junior / AI)
> [!WARNING]
> **Kenapa fitur sorting & export sebelumnya hanya bekerja di tab "Ringkasan Harian" saja?**
>
> Karena script JavaScript sebelumnya menargetkan tabel menggunakan **ID spesifik** (`#report-table`) dan tombol menggunakan ID (`#btn-export-excel` / `#btn-export-pdf`).
>
> Jika halaman memiliki beberapa tab (misal: "Ringkasan Harian", "Laporan Bulanan", "Laporan Tahunan") yang masing-masing memiliki tabel sendiri, maka hanya tabel pertama dengan ID `#report-table` yang akan berfungsi. Tabel di tab lain akan benar-benar mati/tidak merespon klik header.

---

### 💡 Solusi: Pendekatan Generic Class-Based

Agar fitur sorting dan export bekerja di **SEMUA TAB**, kita harus mengubah arsitektur penargetan elemen dari **ID-based** menjadi **Class-based / Container-aware**.

#### 🚀 Langkah 1: Struktur HTML Tabel yang Seragam
Berikan class `sortable-table` ke setiap tag `<table>` di semua tab. Di dalam header (`<th>`), beri class `sortable` dan tipe data menggunakan atribut `data-type`:
```html
<!-- Tabel di Tab Harian, Bulanan, maupun Tahunan menggunakan class yang sama -->
<table class="table sortable-table align-middle table-row-dashed fs-6 gy-5">
    <thead>
        <tr class="text-start text-muted fw-bolder fs-7 text-uppercase">
            <th>No</th>
            <th class="sortable" data-type="date">Tanggal Order <span class="sort-indicator"></span></th>
            <th class="sortable" data-type="string">Kontingen <span class="sort-indicator"></span></th>
            <th class="sortable" data-type="string">Event <span class="sort-indicator"></span></th>
            <th class="sortable text-end" data-type="number">Total Tagihan <span class="sort-indicator"></span></th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <!-- Data baris -->
    </tbody>
</table>
```

#### 🚀 Langkah 2: Logika JavaScript Sorting yang Generic
Ubah script sorting agar melakukan perulangan (looping) ke semua elemen dengan class `.sortable-table` secara independen:

```javascript
document.addEventListener('DOMContentLoaded', function() {
    // Cari semua tabel yang memiliki class .sortable-table di halaman
    const tables = document.querySelectorAll('table.sortable-table');

    tables.forEach((table) => {
        const headers = table.querySelectorAll('thead th.sortable');
        
        headers.forEach((th) => {
            th.dataset.sort = 'none';
            th.style.cursor = 'pointer';
            
            th.addEventListener('click', function() {
                const states = ['none', 'asc', 'desc'];
                const cur = this.dataset.sort;
                // Siklus sorting: none -> asc -> desc -> none
                const next = states[(states.indexOf(cur) + 1) % states.length];
                
                // Reset semua header di TABEL YANG SAMA
                headers.forEach(h => {
                    h.dataset.sort = 'none';
                    const indicator = h.querySelector('.sort-indicator');
                    if (indicator) indicator.innerText = '';
                });
                
                this.dataset.sort = next;
                const indicator = this.querySelector('.sort-indicator');
                if (indicator) {
                    indicator.innerText = next === 'asc' ? ' ↑' : (next === 'desc' ? ' ↓' : '');
                }
                
                if (next === 'none') {
                    // Kembalikan ke susunan awal bawaan server dengan reload
                    location.reload();
                    return;
                }
                
                sortTableByColumn(table, this, next);
            });
        });
    });

    // Fungsi pengurutan baris data
    function sortTableByColumn(table, th, dir) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const colIndex = Array.from(th.parentElement.children).indexOf(th);
        const type = th.dataset.type;

        rows.sort((a, b) => {
            const aText = a.children[colIndex]?.innerText.trim() ?? '';
            const bText = b.children[colIndex]?.innerText.trim() ?? '';
            let av = aText, bv = bText;

            if (type === 'number') {
                av = parseFloat(aText.replace(/[^0-9,.-]/g, '').replace(',', '.')) || 0;
                bv = parseFloat(bText.replace(/[^0-9,.-]/g, '').replace(',', '.')) || 0;
            } else if (type === 'date') {
                av = Date.parse(aText) || 0;
                bv = Date.parse(bText) || 0;
            } else {
                av = aText.toLowerCase();
                bv = bText.toLowerCase();
            }

            if (av < bv) return dir === 'asc' ? -1 : 1;
            if (av > bv) return dir === 'asc' ? 1 : -1;
            return 0;
        });

        // Masukkan kembali baris yang sudah terurut ke DOM
        rows.forEach(r => tbody.appendChild(r));
    }
});
```

#### 🚀 Langkah 3: Export Dinamis Berdasarkan Tab yang Aktif
Agar tombol export mengekspor tabel dari **tab yang sedang aktif saat ini**, kita harus mendeteksi tabel mana yang sedang terlihat (`visible`) di layar klien:

```javascript
// Helper untuk mendeteksi tabel di tab yang aktif/terlihat
function getActiveTable() {
    // Cari tabel dengan class .sortable-table yang posisinya tidak tersembunyi
    const visibleTable = Array.from(document.querySelectorAll('table.sortable-table'))
        .find(table => table.offsetParent !== null);
        
    return visibleTable || document.querySelector('table.sortable-table');
}

// Export CSV (Excel) dari tabel aktif
document.getElementById('btn-export-excel').addEventListener('click', function() {
    const activeTable = getActiveTable();
    if (!activeTable) return alert('Tidak ada data tabel yang aktif.');

    const rows = Array.from(activeTable.querySelectorAll('thead tr, tbody tr'));
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
    a.download = 'laporan_transaksi.csv';
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
});

// Export PDF dari tabel aktif (Menggunakan jsPDF + autoTable)
document.getElementById('btn-export-pdf').addEventListener('click', function() {
    const { jsPDF } = window.jspdf || {};
    if (!jsPDF) return alert('Library jsPDF belum tersedia.');

    const activeTable = getActiveTable();
    if (!activeTable) return alert('Tidak ada data tabel yang aktif.');

    const doc = new jsPDF('landscape');
    const headers = Array.from(activeTable.querySelectorAll('thead th')).map(h => h.innerText.trim());
    const body = Array.from(activeTable.querySelectorAll('tbody tr')).map(tr => {
        return Array.from(tr.querySelectorAll('td')).map(td => td.innerText.trim());
    });

    doc.autoTable({
        head: [headers],
        body: body,
        styles: { fontSize: 8 }
    });
    doc.save('laporan_transaksi.pdf');
});
```

Dengan penulisan kode generic seperti di atas, berapapun jumlah tab laporan yang ditambahkan oleh developer di masa mendatang, fitur sorting dan export akan **langsung berfungsi secara otomatis** tanpa perlu menulis ulang fungsi JavaScript baru!
