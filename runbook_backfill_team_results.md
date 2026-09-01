# RUNBOOK: Backfill Result Juara Beregu ke Semua Anggota Tim

> **Untuk operator (junior developer / AI agent):** runbook ini dijalankan **di server produksi** setelah deploy fitur "input juara beregu per tim". Command sudah tersedia di repo: `app/Console/Commands/BackfillTeamResults.php`.

---

## 1. Latar Belakang & Tujuan

Sebelum fitur "input juara beregu per tim", panitia memilih **satu registration perorangan** per slot juara. Akibatnya di kategori beregu (`sub_categories.category_type = 'beregu'`), juara hanya menempel di 1 anggota tim — anggota lain tidak punya `Result` → sertifikat mereka tercetak "PESERTA", bukan "JUARA".

Command ini mengisi `Result` yang kosong: untuk tiap tim yang punya ≥1 anggota ber-Result, **salin rank + medali ke anggota yang belum punya**.

**Sifat command (penting):**

- **Idempotent** — hanya mengisi yang kosong; tidak pernah mengubah/menghapus Result yang sudah ada. Jalan 2× aman.
- **Transaction** — semua insert satu transaksi; gagal di tengah = rollback semua.
- **`--dry-run`** — lihat rencana tanpa menulis apa pun.
- **Rollback marker** — output berisi daftar `id` Result baru; simpan untuk hapus manual kalau perlu.

---

## 2. Pra-eksekusi (WAJIB, jangan lompat)

1. **Backup tabel `results`** (atau minimal catat `MAX(id)`):

   ```bash
   php artisan tinker --execute="echo App\Models\Result::max('id');"
   ```

   Catat angkanya — semua Result buatan backfill punya `id` LEBIH BESAR dari angka ini.

2. **Pastikan deploy terbaru sudah live** (command ada di server):

   ```bash
   php artisan list | grep results:backfill-team
   ```

   Expected output memuat baris:

   ```
   results:backfill-team    Isi Result juara beregu untuk semua anggota tim (fix data input era perorangan)
   ```

---

## 3. Eksekusi

### Langkah 1 — Dry run (selalu lakukan dulu)

```bash
php artisan results:backfill-team --dry-run
```

Output contoh (angka menyesuaikan data server):

```
Tim A | sub #106 | Juara 1 Gold → isi 2 anggota
Tim C | sub #106 | Juara 2 Silver → isi 2 anggota
...
[DRY-RUN] Akan membuat 56 Result. Tidak ada data ditulis.
```

Periksa: setiap baris menampilkan nama tim, sub-kategori, rank+medali yang akan disalin, dan jumlah anggota yang akan diisi. Pastikan tidak ada yang aneh (mis. medali beda dalam satu tim — tidak mungkin terjadi; command hanya menyalin dari anggota pertama yang ber-Result).

### Langkah 2 — Eksekusi sungguhan

```bash
php artisan results:backfill-team
```

Output contoh:

```
Tim A | sub #106 | Juara 1 Gold → isi 2 anggota
...
Selesai: 56 Result dibuat (id: 223,224,225,...,278 — simpan untuk rollback).
```

**SIMPAN output ini** (screenshot / copy ke file) — berisi id Result baru untuk rollback.

---

## 4. Verifikasi Pasca-eksekusi

1. **Jumlah baris klasemen TIDAK berubah** — backfill tidak boleh menggeser klasemen (1 medali per tim via dedupe `COUNT(DISTINCT team_group_id)`). Bandingkan sebelum/sesudah:

   ```bash
   php artisan tinker --execute="print_r(array_map(fn(\$s) => [\$s->rank, \$s->name, \$s->gold, \$s->silver, \$s->bronze], app(App\Services\StandingsService::class)->forEvent(App\Models\Event::find(3))));"
   ```

   Ganti `3` dengan id event yang relevan. Rank/G/S/B tiap kontingen harus identik dengan sebelum backfill.

2. **Spot-check satu tim** — semua anggota kini ber-Result dengan rank+medali sama:

   ```bash
   php artisan tinker --execute="
   App\Models\TeamGroup::whereHas('subCategory', fn(\$q)=>\$q->where('category_type','beregu'))
       ->with(['registrations' => fn(\$q)=>\$q->whereNull('deleted_at')->with('result','participant')])
       ->get()
       ->each(fn(\$t) => print(\$t->team_name.': '.\$t->registrations->map(fn(\$r) => (\$r->result ? \$r->result->rank_name : 'KOSONG'))->implode(', ').PHP_EOL));
   "
   ```

   Expected: tidak ada lagi `KOSONG` di tim yang punya juara.

3. **Cetak uji sertifikat** — buka detail peserta salah satu anggota tim yang baru terisi (role panitia/kontingen/super-admin), pastikan statusnya "JUARA 1/Gold" sesuai timnya, bukan "PESERTA".

---

## 5. Rollback (hanya jika terjadi masalah)

Semua Result buatan backfill dapat dihapus via daftar id di output Langkah 2:

```bash
php artisan tinker --execute="App\Models\Result::whereIn('id', [IDS_DISINI])->delete();"
```

Ganti `[IDS_DISINI]` dengan id dari output (contoh: `[223,224,225]`). Alternatif kasar: hapus semua Result dengan `id > MAX_ID_PRA-BACKFILL` — HANYA aman kalau tidak ada input hasil baru setelah backfill:

```bash
php artisan tinker --execute="App\Models\Result::where('id', '>', 222)->delete();"
```

> 222 = contoh MAX(id) dari Langkah 2.1 pra-eksekusi. Sesuaikan.

---

## 6. Catatan

- Command aman dijalankan kapan pun — idempotent, dan hanya menyentuh tim beregu yang punya ≥1 Result.
- Input hasil baru (via halaman `/admin/events/{id}/results`) SUDAH otomatis membuat Result semua anggota tim — backfill ini hanya untuk data lama, sekali saja.
- Command lokal sudah diuji: 26 tim diproses, 56 Result dibuat, klasemen 23 baris identik sebelum/sesudah (INPRES BANGKA tetap G=17 S=10 B=30).
