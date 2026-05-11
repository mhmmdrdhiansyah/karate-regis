buatkan issues.md di dalam folder agent yang berisi perencanaan untuk nanti di implementasikan oleh junior programmer atau ai model yang lebih murah.

Isi dari planning nya adalah sebagai berikut :

### Step 5.1 — Upload Bukti Transfer (User Side)

**Tugas:** User upload bukti bayar untuk invoice yang sudah dibuat.

**Yang harus dikerjakan:**
1. Di dashboard user, tampilkan daftar payment milik kontingennya
2. Untuk payment status `pending` (belum ada bukti) atau `rejected`, tampilkan form upload
3. User upload file gambar bukti transfer
4. Simpan path file ke kolom `transfer_proof`
5. Jika status `rejected` dan user upload ulang: status kembali ke `pending`, `rejection_reason` di-clear

**Validasi:**
- File: required, image, max:5MB
- Hanya bisa upload jika status `pending` atau `rejected`

**Best Practices:**
- Simpan bukti transfer di folder terpisah: `storage/app/public/payments/proofs/`
- JANGAN hapus file bukti lama saat upload ulang (simpan untuk audit trail)
- Tampilkan status payment dengan warna: pending=kuning, verified=hijau, rejected=merah

Jelaskan tahapan-tahapan yang harus dilakukan untuk mengimplementasikan penyelesaian permasalahan ini, anggap nanti yang menggunakan implementasi adalah junior programmer atau model AI yang lebih murah

--------------------------------------------------------------------------------------

di sistem ini ada halaman laporan, nah disana ada table dengan masing masing kolom, saya ingin header kolom tersebut bisa di klik dan diurutkan berdasarkan kolom tersebut, jika di klik sekali maka diurutkan secara ascending, jika di klik dua kali maka diurutkan secara descending, jika di klik tiga kali maka diurutkan secara default. lalu setelah itu ada tombol export to pdf dan export to excel, nah tombol tersebut akan mengexport sesuai dengan posisi data yang sedang di tampilkan. posisinya sekarang kolom tanggal order dan total tagihan yang bisa di ascending dan descending, saya ingin kolom lain juga bisa di ascending dan descending, dan saya ingin tombol export to pdf dan export to excel juga bisa mengexport sesuai dengan posisi data yang sedang di tampilkan.
Jelaskan tahapan-tahapan yang harus dilakukan untuk mengimplementasikan fitur ini, anggap nanti yang menggunakan implementasi adalah junior programmer atau model AI yang lebih murah

kenapa yang berlaku hanya pada tab ringkasan harian saja, pada tab lain seperti tidak berfungsi sort nya, coba kamu contoh sort pada ringkasan harian yang berfungsi  ❯ ayo update semua tolong,
apakah semua ini sudah dikerjakan?
push ke remote di branch baru (aku minta saran darimu mana yang baik, buat brnch baru di lokal baru push atau push ke remote branch yang baru)
buat pull request