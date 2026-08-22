# Implementation Steps — Karate Tournament System (Part 1/2)

> **Dokumen ini adalah panduan langkah-demi-langkah untuk mengimplementasikan sistem berdasarkan `rancangan2.md`.** Tidak berisi code, hanya panduan APA yang harus dikerjakan dan BAGAIMANA best practice-nya.

---

## PHASE 0: Project Setup & Database Foundation

### Step 0.1 — Inisialisasi Project Laravel

**Tugas:** Buat project Laravel baru dengan konfigurasi awal.

**Yang harus dikerjakan:**
1. Jalankan `composer create-project laravel/laravel karatae-regis`
2. Konfigurasi file `.env`: set `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` untuk MySQL
3. Install package yang dibutuhkan:
   - `livewire/livewire` (untuk frontend reaktif)
   - `spatie/laravel-activitylog` (opsional, untuk audit trail)
   - `barryvdh/laravel-dompdf` (untuk generate PDF Entry List)
4. Setup Tailwind CSS via npm

**Best Practices:**
- Jangan commit file `.env` ke Git. Gunakan `.env.example` sebagai template
- Buat file `.gitignore` yang benar sejak awal
- Gunakan PHP 8.1+ dan Laravel 10+

--### Step 0.2 — Buat Semua Migration (Urutan Penting!)

**Tugas:** Buat migration untuk SEMUA tabel sekaligus, dalam urutan yang benar karena ada Foreign Key dependencies.

**Urutan migration (WAJIB diikuti):**
1. `users` — sudah ada dari Laravel default
2. `contingents` — FK ke `users`
3. `participants` — FK ke `contingents`, FK `verified_by` ke `users`
4. `events` — tidak ada FK
5. `event_categories` — FK ke `events`
6. `sub_categories` — FK ke `event_categories`
7. `payments` — FK ke `contingents`, FK ke `events`, FK `verified_by` ke `users`
8. `registrations` — FK ke `participants`, `payments`, `sub_categories`. Pakai soft delete
9. `results` — FK ke `registrations`
10. `activity_logs` — FK ke `users` (nullable)

---

#### Daftar Kolom Lengkap Per Tabel

**Tabel 1: `users`** (modifikasi migration bawaan Laravel)

| Kolom | Tipe | Constraint | Keterangan |
|-------|------|------------|------------|
| `id` | BIGINT UNSIGNED | PK, AUTO INCREMENT | Primary key (sudah ada dari Laravel) |
| `email` | VARCHAR(255) | NOT NULL, UNIQUE | Email login |
| `password` | VARCHAR(255) | NOT NULL | Password ter-hash |
| `role` | ENUM('admin','user') | NOT NULL | **Kolom baru — tambahkan ini** |
| `created_at` | TIMESTAMP | NULLABLE | Otomatis dari `$table->timestamps()` |
| `updated_at` | TIMESTAMP | NULLABLE | Otomatis dari `$table->timestamps()` |

> Hapus kolom `name` bawaan Laravel (nama akan disimpan di tabel `contingents`). Atau biarkan jika ingin simpan nama user admin.

---

**Tabel 2: `contingents`**

| Kolom | Tipe | Constraint | Keterangan |
|-------|------|------------|------------|
| `id` | BIGINT UNSIGNED | PK, AUTO INCREMENT | |
| `user_id` | BIGINT UNSIGNED | NOT NULL, FK → `users.id` | Setiap kontingen WAJIB punya user. Relasi one-to-zero-or-one dari sisi users (admin tidak punya contingent) |
| `name` | VARCHAR(255) | NOT NULL | Nama kontingen umum/singkat |
| `official_name` | VARCHAR(255) | NOT NULL | Nama resmi kontingen |
| `phone` | VARCHAR(20) | NULLABLE | Nomor telepon kontak |
| `address` | TEXT | NULLABLE | Alamat kontingen |
| `created_at` | TIMESTAMP | NULLABLE | |
| `updated_at` | TIMESTAMP | NULLABLE | |

---

**Tabel 3: `participants`** (gabungan atlet & pelatih dalam 1 tabel)

| Kolom | Tipe | Constraint | Keterangan |
|-------|------|------------|------------|
| `id` | BIGINT UNSIGNED | PK, AUTO INCREMENT | |
| `contingent_id` | BIGINT UNSIGNED | NOT NULL, FK → `contingents.id` | Peserta milik kontingen mana |
| `type` | ENUM('athlete','coach') | NOT NULL | Membedakan atlet dan pelatih |
| `nik` | VARCHAR(16) | NULLABLE, UNIQUE | Wajib untuk atlet (16 digit angka), NULL untuk pelatih. UNIQUE hanya berlaku untuk nilai non-NULL |
| `name` | VARCHAR(255) | NOT NULL | Nama lengkap peserta |
| `birth_date` | DATE | NULLABLE | Wajib untuk atlet, NULL untuk pelatih |
| `gender` | ENUM('M','F') | NULLABLE | Wajib untuk atlet, NULL untuk pelatih. M=Laki-laki, F=Perempuan |
| `photo` | VARCHAR(255) | NOT NULL | Path file foto peserta |
| `document` | VARCHAR(255) | NULLABLE | Path file Akta/Ijazah. Wajib untuk atlet, NULL untuk pelatih |
| `is_verified` | BOOLEAN | NOT NULL, DEFAULT FALSE | Status verifikasi berkas oleh admin. Bersifat permanen tapi bisa di-revoke |
| `verified_at` | TIMESTAMP | NULLABLE | Kapan terakhir diverifikasi |
| `verified_by` | BIGINT UNSIGNED | NULLABLE, FK → `users.id` | Admin yang memverifikasi |
| `created_at` | TIMESTAMP | NULLABLE | |
| `updated_at` | TIMESTAMP | NULLABLE | |

> **CHECK CONSTRAINT (WAJIB):** Jika `type = 'athlete'`, maka `birth_date`, `gender`, dan `nik` TIDAK BOLEH NULL. Ini mencegah data atlet yang tidak lengkap masuk ke database.

---

**Tabel 4: `events`**

| Kolom | Tipe | Constraint | Keterangan |
|-------|------|------------|------------|
| `id` | BIGINT UNSIGNED | PK, AUTO INCREMENT | |
| `name` | VARCHAR(255) | NOT NULL | Nama event lomba |
| `event_date` | DATE | NOT NULL | Tanggal pelaksanaan lomba |
| `registration_deadline` | DATETIME | NULLABLE | Batas waktu pendaftaran. NULL = tidak ada deadline otomatis (ikuti status event) |
| `coach_fee` | DECIMAL(12,2) | NOT NULL | Biaya per pelatih per event |
| `status` | ENUM('draft','registration_open','registration_closed','ongoing','completed') | NOT NULL, DEFAULT 'draft' | Status lifecycle event |
| `created_at` | TIMESTAMP | NULLABLE | |
| `updated_at` | TIMESTAMP | NULLABLE | |

---

**Tabel 5: `event_categories`** (kelas dalam event)

| Kolom | Tipe | Constraint | Keterangan |
|-------|------|------------|------------|
| `id` | BIGINT UNSIGNED | PK, AUTO INCREMENT | |
| `event_id` | BIGINT UNSIGNED | NOT NULL, FK → `events.id` | Event induk |
| `type` | ENUM('Open','Festival') | NOT NULL | Jenis lomba |
| `class_name` | VARCHAR(255) | NOT NULL | Nama kelas. Contoh: 'Junior', 'Senior', 'Cadet' |
| `min_birth_date` | DATE | NOT NULL | Batas tanggal lahir PALING TUA (terkecil). Contoh: 2008-01-01 |
| `max_birth_date` | DATE | NOT NULL | Batas tanggal lahir PALING MUDA (terbesar). Contoh: 2013-12-31 |
| `created_at` | TIMESTAMP | NULLABLE | |
| `updated_at` | TIMESTAMP | NULLABLE | |

> **PENTING:** Rentang tanggal lahir sengaja di level `event_categories` (bukan `sub_categories`) karena ditentukan per kelas. Semua sub-kategori dalam kelas yang sama berbagi rentang usia yang sama.

---

**Tabel 6: `sub_categories`** (sub-kategori pertandingan)

| Kolom | Tipe | Constraint | Keterangan |
|-------|------|------------|------------|
| `id` | BIGINT UNSIGNED | PK, AUTO INCREMENT | |
| `event_category_id` | BIGINT UNSIGNED | NOT NULL, FK → `event_categories.id` | Kategori induk |
| `name` | VARCHAR(255) | NOT NULL | Nama sub-kategori. Contoh: 'Kumite -55kg', 'Kata Individu', 'Kata Beregu' |
| `gender` | ENUM('M','F','Mixed') | NOT NULL | Gender yang boleh ikut. Mixed = boleh laki-laki dan perempuan |
| `price` | DECIMAL(12,2) | NOT NULL | Harga pendaftaran per peserta untuk sub-kategori ini |
| `min_participants` | INT | NOT NULL, DEFAULT 1 | Minimum peserta. 1 untuk individu, 3+ untuk beregu |
| `max_participants` | INT | NOT NULL, DEFAULT 1 | Maksimum peserta. 1 untuk individu, 5+ untuk beregu (termasuk cadangan) |
| `created_at` | TIMESTAMP | NULLABLE | |
| `updated_at` | TIMESTAMP | NULLABLE | |

---

**Tabel 7: `payments`** (invoice pembayaran)

| Kolom | Tipe | Constraint | Keterangan |
|-------|------|------------|------------|
| `id` | BIGINT UNSIGNED | PK, AUTO INCREMENT | |
| `contingent_id` | BIGINT UNSIGNED | NOT NULL, FK → `contingents.id` | Kontingen yang membayar |
| `event_id` | BIGINT UNSIGNED | NOT NULL, FK → `events.id` | Event yang dibayar |
| `total_amount` | DECIMAL(12,2) | NOT NULL | Dihitung otomatis oleh sistem (BR-16). JANGAN izinkan edit manual |
| `transfer_proof` | VARCHAR(255) | NULLABLE | Path file bukti transfer. NULL saat invoice baru dibuat |
| `status` | ENUM('pending','verified','rejected','cancelled') | NOT NULL, DEFAULT 'pending' | Status pembayaran |
| `rejection_reason` | TEXT | NULLABLE | Wajib diisi saat reject atau revoke |
| `verified_at` | TIMESTAMP | NULLABLE | Kapan payment diverifikasi |
| `verified_by` | BIGINT UNSIGNED | NULLABLE, FK → `users.id` | Admin yang memverifikasi |
| `created_at` | TIMESTAMP | NULLABLE | |
| `updated_at` | TIMESTAMP | NULLABLE | |

> **JANGAN buat UNIQUE constraint pada `(contingent_id, event_id)`** — Payment yang sudah `cancelled` harus mengizinkan kontingen membuat payment baru untuk event yang sama. Cegah duplikasi di application layer.

---

**Tabel 8: `registrations`** (pendaftaran peserta ke sub-kategori)

| Kolom | Tipe | Constraint | Keterangan |
|-------|------|------------|------------|
| `id` | BIGINT UNSIGNED | PK, AUTO INCREMENT | |
| `participant_id` | BIGINT UNSIGNED | NOT NULL, FK → `participants.id` | Peserta yang didaftarkan |
| `payment_id` | BIGINT UNSIGNED | NOT NULL, FK → `payments.id` | Payment/invoice yang mencakup registrasi ini |
| `sub_category_id` | BIGINT UNSIGNED | **NULLABLE**, FK → `sub_categories.id` | Sub-kategori lomba. **NULL untuk pelatih** (BR-08) — ini by design, bukan bug |
| `status_berkas` | ENUM('unsubmitted','pending_review','verified','rejected') | NOT NULL, DEFAULT 'unsubmitted' | Status verifikasi berkas atlet |
| `rejection_reason` | TEXT | NULLABLE | Alasan penolakan berkas |
| `verified_at` | TIMESTAMP | NULLABLE | Kapan berkas diverifikasi |
| `verified_by` | BIGINT UNSIGNED | NULLABLE, FK → `users.id` | Admin yang memverifikasi berkas |
| `deleted_at` | TIMESTAMP | NULLABLE | Soft delete — gunakan `$table->softDeletes()` |
| `created_at` | TIMESTAMP | NULLABLE | |
| `updated_at` | TIMESTAMP | NULLABLE | |

> **JANGAN buat UNIQUE constraint pada `(participant_id, sub_category_id)`** — Soft-deleted records akan konflik dengan registrasi baru di MySQL. Cegah duplikasi di application layer.

---

**Tabel 9: `results`** (hasil pemenang lomba)

| Kolom | Tipe | Constraint | Keterangan |
|-------|------|------------|------------|
| `id` | BIGINT UNSIGNED | PK, AUTO INCREMENT | |
| `registration_id` | BIGINT UNSIGNED | NOT NULL, FK → `registrations.id` | Registrasi yang menang |
| `medal_type` | ENUM('Gold','Silver','Bronze') | NOT NULL | Jenis medali. Boleh ada 2 baris Bronze per sub-kategori (BR-12) |
| `created_at` | TIMESTAMP | NULLABLE | |
| `updated_at` | TIMESTAMP | NULLABLE | |

---

**Tabel 10: `activity_logs`** (audit trail)

| Kolom | Tipe | Constraint | Keterangan |
|-------|------|------------|------------|
| `id` | BIGINT UNSIGNED | PK, AUTO INCREMENT | |
| `user_id` | BIGINT UNSIGNED | **NULLABLE**, FK → `users.id` | User yang melakukan aksi. NULL untuk aksi otomatis oleh sistem |
| `action` | VARCHAR(255) | NOT NULL | Kode aksi. Contoh: 'payment.approved', 'payment.revoked', 'participant.verified', 'participant.verification_revoked', 'registration.cancelled', 'participant.updated' |
| `subject_type` | VARCHAR(255) | NOT NULL | Nama model target. Contoh: 'Payment', 'Participant', 'Registration' |
| `subject_id` | BIGINT UNSIGNED | NOT NULL | ID dari entitas yang terkena aksi |
| `description` | TEXT | NULLABLE | Deskripsi ringkas (opsional, untuk kemudahan baca) |
| `properties` | JSON | NULLABLE | Data before/after dalam format JSON. Contoh: `{"old": {"status": "pending"}, "new": {"status": "verified"}, "reason": "Bukti transfer valid"}` |
| `created_at` | TIMESTAMP | NULLABLE | |
| `updated_at` | TIMESTAMP | NULLABLE | |

---

#### Peringatan & Detail Penting Per Tabel

| Tabel | Hal yang JANGAN dilupakan |
|-------|--------------------------| 
| `users` | Tambahkan kolom `role` ENUM('admin','user') |
| `contingents` | `user_id` NOT NULL. Relasi one-to-zero-or-one dari users |
| `participants` | Tambahkan CHECK CONSTRAINT untuk atlet wajib punya birth_date, gender, nik. NIK UNIQUE (NULL-safe) |
| `events` | `registration_deadline` NULLABLE. `status` default 'draft' |
| `event_categories` | `min_birth_date` dan `max_birth_date` ada di level ini (bukan sub_categories) |
| `sub_categories` | `min_participants` dan `max_participants` default 1. `gender` ENUM('M','F','Mixed') |
| `payments` | JANGAN buat UNIQUE constraint pada (contingent_id, event_id). `status` default 'pending' |
| `registrations` | `sub_category_id` NULLABLE (untuk pelatih). Gunakan `$table->softDeletes()`. JANGAN buat UNIQUE constraint |
| `results` | Boleh ada 2 baris Bronze per sub-kategori |
| `activity_logs` | `user_id` NULLABLE. `properties` tipe JSON |

**Index tambahan yang WAJIB dibuat:**
- `participants`: composite index pada `(type, gender, birth_date)` — paling kritikal untuk filter atlet
- `registrations`: index pada `status_berkas`
- `payments`: index pada `status`
- `results`: index pada `medal_type`
- `activity_logs`: index pada `(subject_type, subject_id)` dan `user_id`

**Best Practices:**
- Selalu gunakan `$table->timestamps()` di SEMUA migration
- Gunakan `$table->foreignId('xxx')->constrained()->onDelete('restrict')` untuk FK
- Untuk kolom `verified_by`, gunakan `$table->foreignId('verified_by')->nullable()->constrained('users')`
- Gunakan `DECIMAL(12,2)` untuk semua kolom uang (price, total_amount, coach_fee)
- Test migration dengan `php artisan migrate:fresh` sebelum lanjutrate:fresh` sebelum lanjut

---

### Step 0.3 — Buat Semua Eloquent Model

**Tugas:** Buat Model untuk setiap tabel dengan relasi dan konfigurasi yang benar.

**Daftar Model dan relasinya:**

1. **User** — `hasOne(Contingent)`. Tambahkan method `isAdmin()` yang cek `role === 'admin'`
2. **Contingent** — `belongsTo(User)`, `hasMany(Participant)`, `hasMany(Payment)`
3. **Participant** — `belongsTo(Contingent)`, `hasMany(Registration)`. Tambahkan scope `athletes()` dan `coaches()`
4. **Event** — `hasMany(EventCategory)`, `hasMany(Payment)`
5. **EventCategory** — `belongsTo(Event)`, `hasMany(SubCategory)`
6. **SubCategory** — `belongsTo(EventCategory)`, `hasMany(Registration)`
7. **Payment** — `belongsTo(Contingent)`, `belongsTo(Event)`, `hasMany(Registration)`
8. **Registration** — `belongsTo(Participant)`, `belongsTo(Payment)`, `belongsTo(SubCategory)`, `hasOne(Result)`. Gunakan trait `SoftDeletes`
9. **Result** — `belongsTo(Registration)`
10. **ActivityLog** — `belongsTo(User)`

**Best Practices:**
- Definisikan `$fillable` atau `$guarded` di setiap model
- Untuk ENUM, buat PHP Enum class terpisah (misal: `App\Enums\PaymentStatus`)
- Gunakan `$casts` untuk casting otomatis: `birth_date` → `date`, `properties` → `array`, `is_verified` → `boolean`
- Buat Eloquent Scope di model Participant: `scopeAthletes($q)` → `$q->where('type','athlete')` dan `scopeCoaches($q)` → `$q->where('type','coach')`

---

## PHASE 1: Modul 1 — Auth & Profil Kontingen

### Step 1.1 — Setup Authentication

**Tugas:** Implementasikan sistem login dan register dengan dua role: admin dan user.

**Yang harus dikerjakan:**
1. Gunakan Laravel Breeze atau Fortify untuk scaffolding auth dasar
2. Modifikasi halaman register: tambahkan field untuk data kontingen (nama kontingen, nama resmi, phone, address)
3. Saat register berhasil, buat DUA record dalam satu transaction: User + Contingent
4. Buat middleware `AdminMiddleware` yang cek `auth()->user()->role === 'admin'`
5. Buat middleware `UserMiddleware` yang cek `auth()->user()->role === 'user'`

**Validasi saat Register:**
- Email: required, email format, unique di tabel users
- Password: required, min 8 karakter, confirmed
- Nama Kontingen: required, string, max 255
- Phone: nullable, max 20 karakter

**Best Practices:**
- Gunakan `DB::transaction()` saat membuat User + Contingent agar atomik
- Hash password dengan `Hash::make()` (sudah otomatis di Laravel Breeze)
- Setelah register, redirect user ke dashboard kontingen
- Admin account dibuat via seeder, BUKAN via form register

### Step 1.2 — Routing & Layout

**Tugas:** Setup route groups dan layout untuk admin dan user.

**Yang harus dikerjakan:**
1. Buat route group `/admin/*` dengan middleware `auth` + `admin`
2. Buat route group `/dashboard/*` dengan middleware `auth` + `user`
3. Buat layout Blade terpisah untuk admin dan user (sidebar, navbar, dll)
4. Buat halaman Dashboard sederhana untuk masing-masing role

**Best Practices:**
- Gunakan `Route::prefix('admin')->middleware(['auth','admin'])->group(...)` 
- Pisahkan route file jika perlu: `routes/admin.php` dan `routes/user.php`
- Buat component Blade reusable untuk sidebar, navbar, alert messages

### Step 1.3 — Profil Kontingen (CRUD Sederhana)

**Tugas:** User bisa melihat dan mengedit profil kontingen mereka.

**Yang harus dikerjakan:**
1. Halaman "Profil Kontingen" yang menampilkan data kontingen
2. Form edit untuk: nama, nama resmi, phone, address
3. Validasi input di server-side

**Best Practices:**
- Ambil kontingen via `auth()->user()->contingent` (relasi hasOne)
- Gunakan Form Request class untuk validasi (bukan validate di controller)

---

## PHASE 2: Modul 2 — Bank Peserta (Participant Pool)

### Step 2.1 — CRUD Peserta (Atlet & Pelatih)

**Tugas:** User bisa mengelola daftar atlet dan pelatih milik kontingennya.

**Yang harus dikerjakan:**
1. Halaman daftar peserta dengan tab/filter: Semua | Atlet | Pelatih
2. Form tambah peserta dengan field berbeda berdasarkan type:
   - **Atlet:** name (wajib), nik (wajib, 16 digit), birth_date (wajib), gender (wajib), photo (wajib), document (wajib)
   - **Pelatih:** name (wajib), photo (wajib). Field lain nullable
3. Form edit peserta (dengan proteksi BR-14, lihat Step 2.3)
4. Tombol hapus peserta (dengan proteksi BR-14)
5. Upload foto dan dokumen (Akta/Ijazah)

**Validasi:**
- NIK: `required_if:type,athlete`, `digits:16`, `unique:participants,nik` (kecuali saat edit diri sendiri)
- birth_date: `required_if:type,athlete`, `date`, `before:today`
- gender: `required_if:type,athlete`, `in:M,F`
- photo: `required`, `image`, `max:2048` (2MB)
- document: `required_if:type,athlete`, `file`, `mimes:jpg,jpeg,png,pdf`, `max:5120` (5MB)

**Best Practices:**
- Simpan file ke `storage/app/public/participants/photos/` dan `storage/app/public/participants/documents/`
- Gunakan UUID atau hash untuk nama file, JANGAN gunakan nama asli file
- Pastikan user hanya bisa akses peserta milik kontingennya sendiri (authorization)
- Gunakan `$this->authorize()` atau Policy class untuk cek ownership

### Step 2.2 — Upload & Preview File

**Tugas:** Implementasi upload foto dan dokumen dengan preview.

**Yang harus dikerjakan:**
1. Preview foto sebelum upload (client-side JavaScript)
2. Setelah upload, simpan path file di kolom `photo` / `document`
3. Tampilkan foto peserta di halaman daftar dan detail
4. Untuk dokumen, tampilkan link download (bukan preview inline)

**Best Practices:**
- Jalankan `php artisan storage:link` untuk membuat symlink ke public
- Validasi tipe file di SERVER-SIDE, jangan hanya di client
- Compress/resize foto sebelum simpan jika ukuran terlalu besar

### Step 2.3 — Proteksi Edit & Hapus (BR-14) ⚠️ KRITIKAL

**Tugas:** Implementasikan dua level lock pada data peserta.

**Aturan yang WAJIB diimplementasikan di Service Layer:**

**Lock Level 1 — Peserta punya registrasi aktif (non-cancelled):**
- Cek: apakah ada `Registration` untuk participant ini yang BELUM di-soft-delete?
- Jika YA: field `birth_date`, `gender`, `nik` TIDAK BOLEH diedit
- Field `name`, `photo`, `document` MASIH BOLEH diedit
- Peserta TIDAK BOLEH dihapus

**Lock Level 2 — Peserta sudah verified (`is_verified = true`):**
- SEMUA field terkunci KECUALI `photo`
- Peserta TIDAK BOLEH dihapus

**Yang harus dikerjakan:**
1. Buat service class `ParticipantService` dengan method `canEditField($participant, $fieldName): bool`
2. Buat method `canDelete($participant): bool`
3. Di form edit, disable field yang terkunci (UI) DAN validasi di server (backend)
4. Tampilkan pesan jelas ke user kenapa field terkunci

**Best Practices:**
- JANGAN hanya disable di frontend. Validasi WAJIB juga di backend (service layer)
- Buat method helper di Model atau Service yang bisa di-reuse
- Return error message yang informatif: "Field ini terkunci karena atlet sudah terdaftar di event X"

---

## PHASE 3: Modul 3 — Manajemen Event (Admin Only)

### Step 3.1 — CRUD Event

**Tugas:** Admin bisa membuat dan mengelola event lomba.

**Yang harus dikerjakan:**
1. Halaman daftar event dengan status badge (draft, registration_open, dll)
2. Form buat event: name, event_date, registration_deadline (opsional), coach_fee, status
3. Form edit event
4. Tombol untuk mengubah status event (state transition)

**Status transitions yang VALID:**
- `draft` → `registration_open`
- `registration_open` → `registration_closed`
- `registration_closed` → `ongoing`
- `ongoing` → `completed`

**Validasi:**
- name: required, string, max 255
- event_date: required, date, after_or_equal:today (saat create)
- registration_deadline: nullable, datetime, before:event_date
- coach_fee: required, numeric, min:0

**Best Practices:**
- Gunakan state machine pattern: buat method di Model atau Service yang mengecek transisi valid
- JANGAN izinkan edit field penting (event_date, coach_fee) jika event sudah `ongoing` atau `completed`
- Tampilkan konfirmasi sebelum mengubah status

### Step 3.2 — CRUD Event Categories (Kelas)

**Tugas:** Admin bisa menambahkan kategori kelas ke sebuah event.

**Yang harus dikerjakan:**
1. Di halaman detail event, tampilkan daftar kategori
2. Form tambah kategori: type (Open/Festival), class_name (Junior/Senior/dll), min_birth_date, max_birth_date
3. Edit dan hapus kategori

**Validasi:**
- type: required, in:Open,Festival
- class_name: required, string
- min_birth_date: required, date
- max_birth_date: required, date, after_or_equal:min_birth_date
- **PENTING:** `min_birth_date` adalah tanggal lahir PALING TUA (paling kecil), `max_birth_date` adalah tanggal lahir PALING MUDA (paling besar). Contoh: Junior lahir antara 2008-01-01 dan 2013-12-31

**Best Practices:**
- Jangan izinkan hapus kategori jika sudah ada sub-kategori yang memiliki registrasi aktif
- Tampilkan rentang umur dalam format yang mudah dibaca: "Lahir: 1 Jan 2008 – 31 Des 2013"

### Step 3.3 — CRUD Sub-Categories

**Tugas:** Admin bisa menambahkan sub-kategori ke sebuah event category.

**Yang harus dikerjakan:**
1. Di halaman detail event category, tampilkan daftar sub-kategori
2. Form tambah: name, gender (M/F/Mixed), price, min_participants, max_participants
3. Edit dan hapus sub-kategori

**Validasi:**
- name: required, string (contoh: "Kumite -55kg", "Kata Beregu")
- gender: required, in:M,F,Mixed
- price: required, numeric, min:0
- min_participants: required, integer, min:1, default 1
- max_participants: required, integer, min:min_participants, default 1

**Untuk kategori beregu:**
- Admin set min_participants = 3, max_participants = 5 (contoh)
- Untuk individu: biarkan default 1 dan 1

**Best Practices:**
- Jangan izinkan hapus sub-kategori jika sudah ada registrasi aktif
- Jangan izinkan edit harga jika sudah ada payment yang terbuat (karena invoice sudah di-snapshot — BR-16)
- Tampilkan label "Individu" atau "Beregu" otomatis berdasarkan max_participants > 1

# Implementation Steps — Karate Tournament System (Part 2/2)

> Lanjutan dari Part 1. Pastikan Phase 0–3 sudah selesai sebelum mulai Phase 4.

---

## PHASE 4: Modul 4 — Engine Pendaftaran

### Step 4.1 — Halaman Pilih Event & Kategori

**Tugas:** User memilih event → kategori → sub-kategori secara bertahap.

**Yang harus dikerjakan:**
1. Halaman daftar event yang statusnya `registration_open` saja
2. Cek deadline: jika `registration_deadline` tidak NULL dan sudah lewat, tampilkan "Pendaftaran Ditutup"
3. Jika `registration_deadline` NULL, ikuti status event (BR-13)
4. Setelah pilih event, tampilkan daftar event_categories (Open/Festival + kelas)
5. Setelah pilih kategori, tampilkan daftar sub_categories

**Best Practices:**
- Gunakan Livewire untuk navigasi bertahap tanpa full-page reload
- Tampilkan info harga di setiap sub-kategori agar user tahu biayanya
- Tampilkan badge "Individu" atau "Beregu (min 3, max 5)" di sub-kategori

### Step 4.2 — Filter Atlet Otomatis (BR-07) ⚠️ KRITIKAL

**Tugas:** Saat user memilih sub-kategori, tampilkan HANYA atlet yang memenuhi syarat.

**Logika filter (WAJIB semua diterapkan):**
1. `participants.type = 'athlete'` (bukan pelatih)
2. `participants.contingent_id = [kontingen user yang login]` (hanya atlet milik sendiri)
3. `participants.gender = sub_categories.gender` ATAU `sub_categories.gender = 'Mixed'`
4. `participants.birth_date >= event_categories.min_birth_date`
5. `participants.birth_date <= event_categories.max_birth_date`
6. Atlet BELUM terdaftar di sub-kategori yang sama (cek registrasi aktif non-soft-deleted, BR-09)

**Yang harus dikerjakan:**
1. Buat Eloquent Scope di model Participant: `scopeEligibleFor($query, $subCategory)`
2. Scope ini harus join ke `event_categories` untuk ambil rentang tanggal lahir
3. Tampilkan daftar atlet eligible sebagai checkbox list
4. Tampilkan info kenapa atlet tertentu TIDAK eligible (opsional tapi sangat membantu UX)

**Best Practices:**
- Index `idx_participants_type_gender_dob` sangat penting di sini. Pastikan sudah dibuat
- Gunakan Livewire component agar list atlet ter-update real-time saat pilih sub-kategori
- JANGAN load semua atlet lalu filter di PHP. Filter di DATABASE query

### Step 4.3 — Validasi Peserta Beregu (BR-15)

**Tugas:** Untuk sub-kategori beregu, validasi jumlah atlet yang dipilih.

**Yang harus dikerjakan:**
1. Ambil `min_participants` dan `max_participants` dari sub-kategori
2. Hitung jumlah atlet yang dipilih user
3. Validasi: `jumlah >= min_participants AND jumlah <= max_participants`
4. Tampilkan pesan error jelas: "Kata Beregu membutuhkan minimal 3 dan maksimal 5 atlet"

**Best Practices:**
- Validasi di frontend (disable tombol submit jika belum cukup) DAN di backend
- Untuk kategori individu (min=1, max=1), user hanya bisa pilih tepat 1 atlet

### Step 4.4 — Pendaftaran Pelatih (BR-08)

**Tugas:** User mendaftarkan pelatih untuk event (bukan per sub-kategori).

**Yang harus dikerjakan:**
1. Di halaman pendaftaran event, tampilkan section terpisah "Daftarkan Pelatih"
2. Tampilkan daftar pelatih dari Bank Peserta (`type = 'coach'`)
3. User pilih pelatih mana yang akan didaftarkan
4. Saat disimpan, buat `Registration` dengan `sub_category_id = NULL`
5. Validasi: pelatih tidak boleh didaftar 2 kali di event yang sama (cek via payment aktif)

**Best Practices:**
- Pelatih tidak perlu filter umur/gender
- `sub_category_id` NULL untuk pelatih adalah BY DESIGN, bukan bug
- Status berkas pelatih otomatis `verified` saat registrasi dibuat (pelatih tidak perlu verifikasi dokumen)

### Step 4.5 — Kalkulasi Invoice Otomatis (BR-16) ⚠️ KRITIKAL

**Tugas:** Hitung total biaya dan buat Payment record.

**Formula:**
```
total_amount = SUM(harga sub-kategori semua atlet) + (jumlah pelatih × coach_fee event)
```

**Yang harus dikerjakan:**
1. Setelah user selesai memilih semua atlet dan pelatih, hitung total
2. Tampilkan rincian invoice: daftar atlet + sub-kategori + harga, daftar pelatih + biaya
3. User konfirmasi → sistem buat 1 record `Payment` + semua record `Registration`
4. Semua dalam SATU database transaction

**Validasi sebelum buat invoice:**
- Cek belum ada payment aktif (non-cancelled) untuk kontingen+event ini (BR-03)
- Jika sudah ada, tampilkan error "Anda sudah memiliki invoice untuk event ini"

**Best Practices:**
- `total_amount` di-SNAPSHOT saat invoice dibuat. Jika admin ubah harga sub-kategori setelahnya, invoice yang sudah ada TIDAK berubah
- Gunakan `DB::transaction()` untuk atomicity
- Simpan semua registrasi dalam batch insert jika memungkinkan

---

## PHASE 5: Modul 5 — Keuangan & Verifikasi

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

### Step 5.2 — Pembatalan Payment oleh User (BR-10)

**Tugas:** User bisa membatalkan payment selama status masih `pending` atau `rejected`.

**Yang harus dikerjakan:**
1. Tampilkan tombol "Batalkan Pendaftaran" HANYA jika status `pending` atau `rejected`
2. Konfirmasi: "Apakah Anda yakin? Semua pendaftaran atlet dan pelatih akan dibatalkan"
3. Dalam SATU transaction:
   - Set `payments.status = 'cancelled'`
   - Soft-delete SEMUA `registrations` yang terkait payment ini (set `deleted_at`)
4. Catat aksi di `activity_logs`

**Best Practices:**
- Gunakan `DB::transaction()` — ini WAJIB
- Setelah cancelled, kontingen bisa membuat payment baru untuk event yang sama
- Data TIDAK dihapus permanen. Soft delete menjaga audit trail

### Step 5.3 — Verifikasi Payment oleh Admin (BR-05)

**Tugas:** Admin bisa approve atau reject bukti transfer.

**Yang harus dikerjakan:**
1. Halaman admin: daftar payment dengan filter status (semua / pending / verified / rejected)
2. Admin bisa lihat bukti transfer (tampilkan gambar)
3. Tombol "Approve": dalam transaction, set `payments.status = 'verified'`, set `verified_at` dan `verified_by`, update SEMUA `registrations.status_berkas` dari `unsubmitted` → `pending_review`
4. Tombol "Reject": wajib isi alasan penolakan, set `payments.status = 'rejected'`

**Best Practices:**
- SELALU gunakan `DB::transaction()` untuk approve (karena update banyak tabel)
- `rejection_reason` WAJIB diisi saat reject (validasi required)
- Tampilkan jumlah payment pending di sidebar admin sebagai badge notification

### Step 5.4 — Revoke Payment oleh Admin (BR-11)

**Tugas:** Admin bisa me-revoke payment yang sudah verified.

**Yang harus dikerjakan:**
1. Di halaman detail payment yang sudah `verified`, tampilkan tombol "Revoke Approval"
2. Admin WAJIB isi alasan revoke
3. Dalam SATU transaction:
   - Set `payments.status = 'pending'`
   - Set `rejection_reason` dengan alasan revoke
   - Clear `verified_at` dan `verified_by`
   - Semua `registrations.status_berkas` yang `pending_review` → kembali ke `unsubmitted`
4. Catat di `activity_logs` dengan properties: old status, new status, alasan

**Best Practices:**
- Fitur ini untuk kasus darurat (bukti palsu terdeteksi belakangan)
- `rejection_reason` WAJIB — jangan izinkan revoke tanpa alasan
- Registrasi yang sudah `verified` (berkas sudah dicek) TIDAK berubah statusnya

### Step 5.5 — Verifikasi Berkas Peserta oleh Admin

**Tugas:** Admin memverifikasi dokumen (Akta/Ijazah) atlet satu per satu.

**Yang harus dikerjakan:**
1. Halaman admin: daftar registrasi dengan filter `status_berkas`
2. Admin lihat dokumen atlet (gambar/PDF)
3. Tombol "Verify": set `registrations.status_berkas = 'verified'`, set `verified_at`, `verified_by`
4. **PENTING:** Jika ini verifikasi PERTAMA untuk atlet ini (cek `participants.is_verified`), set juga `participants.is_verified = true`, `participants.verified_at`, `participants.verified_by`
5. Tombol "Reject": wajib isi alasan, set `status_berkas = 'rejected'`
6. Setelah reject, atlet bisa upload ulang dokumen → status kembali ke `pending_review`

**Best Practices:**
- Pelatih tidak perlu verifikasi berkas (sudah otomatis `verified` saat registrasi)
- `is_verified` pada `participants` bersifat PERMANEN — berlaku untuk semua event ke depan
- Catat semua aksi verifikasi di `activity_logs`

### Step 5.6 — Revoke Verifikasi Peserta (BR-06)

**Tugas:** Admin bisa mencabut status `is_verified` dari peserta.

**Yang harus dikerjakan:**
1. Di halaman detail peserta (admin view), jika `is_verified = true`, tampilkan "Revoke Verifikasi"
2. Admin WAJIB isi alasan
3. Clear `participants.is_verified`, `verified_at`, `verified_by`
4. Catat di `activity_logs`
5. **PENTING:** Registrasi yang sudah `verified` di event SEBELUMNYA TIDAK BERUBAH

**Best Practices:**
- Efek revoke hanya untuk event KE DEPAN
- Ini artinya atlet harus diverifikasi ulang di event berikutnya

---

## PHASE 6: Modul 6 — Landing Page & Reporting

### Step 6.1 — Landing Page Publik

**Tugas:** Halaman depan yang bisa diakses tanpa login, menampilkan event aktif.

**Yang harus dikerjakan:**
1. Tampilkan event dengan status `registration_open`, `registration_closed`, atau `ongoing` DAN `event_date >= today`
2. Event `draft` dan `completed` TIDAK ditampilkan
3. Tampilkan info: nama event, tanggal, status, jumlah kontingen terdaftar

**Best Practices:**
- Halaman ini harus cepat. Gunakan cache (lihat Step 6.2)
- Desain yang menarik dan responsif — ini "wajah" sistem

### Step 6.2 — Klasemen Real-Time dengan Cache

**Tugas:** Tampilkan klasemen medali per kontingen, ter-update otomatis.

**Yang harus dikerjakan:**
1. Buat Livewire component `StandingsComponent` dengan polling 60 detik
2. Query klasemen: JOIN contingents → participants → registrations → results, filter by event_id, exclude soft-deleted registrations
3. Urutkan: Gold DESC → Silver DESC → Bronze DESC
4. Cache hasil query selama 60 detik: `Cache::remember('standings_event_'.$eventId, 60, ...)`
5. Tampilkan tabel: Peringkat, Nama Kontingen, Emas, Perak, Perunggu, Total

**Referensi query:** Lihat rancangan2.md bagian §10 poin 8 untuk contoh Eloquent query lengkap.

**Best Practices:**
- Jangan query database setiap request. Cache 60 detik sudah cukup
- Admin bisa clear cache manual via panel admin jika butuh update instan
- Gunakan Laravel File Cache (sudah built-in, tidak perlu Redis)

### Step 6.3 — Entry List (PDF/Tabel Web)

**Tugas:** Generate daftar peserta per sub-kategori.

**Yang harus dikerjakan:**
1. Halaman web: tampilkan daftar atlet per sub-kategori (nama, kontingen, gender)
2. Tombol "Download PDF" yang generate PDF menggunakan DomPDF
3. Filter by event → kategori → sub-kategori
4. Hanya tampilkan registrasi yang payment-nya `verified` dan NOT soft-deleted

**Best Practices:**
- PDF layout harus rapi dan siap cetak (ukuran A4)
- Sertakan header: nama event, nama sub-kategori, tanggal
- Urutkan peserta alfabetis atau per kontingen

### Step 6.4 — Rekap Keuangan (Admin)

**Tugas:** Admin bisa melihat rekap keuangan per event.

**Yang harus dikerjakan:**
1. Tabel: nama kontingen, total invoice, status payment
2. Total keseluruhan di bawah tabel
3. Filter by event dan status payment

**Best Practices:**
- Gunakan `SUM()` di database, jangan hitung di PHP
- Tampilkan jumlah kontingen yang sudah bayar vs belum

### Step 6.5 — Input Hasil Lomba (Admin)

**Tugas:** Admin menginput pemenang per sub-kategori.

**Yang harus dikerjakan:**
1. Admin pilih event → sub-kategori
2. Tampilkan daftar peserta terdaftar di sub-kategori itu
3. Admin pilih: pemenang Gold (1 orang/tim), Silver (1), Bronze (1 atau 2 — BR-12)
4. Simpan ke tabel `results`: 1 baris per medali
5. Untuk 2 Bronze: insert 2 baris dengan `medal_type = 'Bronze'`

**Best Practices:**
- Validasi: Gold dan Silver masing-masing max 1 per sub-kategori. Bronze max 2
- Setelah input hasil, clear cache klasemen agar langsung ter-update
- Tampilkan konfirmasi sebelum simpan

---

## Checklist Final Sebelum Deploy

| # | Item | Status |
|---|------|--------|
| 1 | Semua migration berjalan tanpa error (`migrate:fresh`) | ☐ |
| 2 | Seeder untuk admin account sudah dibuat | ☐ |
| 3 | Semua index database sudah dibuat | ☐ |
| 4 | BR-14 (proteksi edit/hapus) sudah ditest | ☐ |
| 5 | Semua state transition payment sudah ditest | ☐ |
| 6 | Soft delete registrations berfungsi dengan benar | ☐ |
| 7 | Filter atlet by umur+gender berfungsi | ☐ |
| 8 | Invoice calculation benar (atlet + pelatih) | ☐ |
| 9 | Duplikasi atlet per sub-kategori dicegah | ☐ |
| 10 | Duplikasi pelatih per event dicegah | ☐ |
| 11 | Duplikasi payment per kontingen per event dicegah | ☐ |
| 12 | Activity logs mencatat semua aksi penting | ☐ |
| 13 | Cache klasemen berfungsi | ☐ |
| 14 | PDF Entry List bisa di-generate | ☐ |
| 15 | `php artisan storage:link` sudah dijalankan | ☐ |

---

## Dependency Diagram Antar Modul

```
Phase 0 (Setup & DB)
    │
    ├── Phase 1 (M1: Auth)
    │       │
    │       ├── Phase 2 (M2: Bank Peserta)
    │       │       │
    │       │       └── Phase 4 (M4: Engine Pendaftaran) ← perlu M3 juga
    │       │               │
    │       │               └── Phase 5 (M5: Keuangan & Verifikasi)
    │       │                       │
    │       │                       └── Phase 6 (M6: Landing & Reporting)
    │       │
    │       └── Phase 3 (M3: Manajemen Event)
    │               │
    │               └── Phase 4 (M4: Engine Pendaftaran)
```

> **PENTING:** Jangan loncat phase. Setiap phase bergantung pada phase sebelumnya.
