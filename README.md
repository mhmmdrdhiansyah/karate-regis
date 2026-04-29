# Karatae Regis

Aplikasi manajemen registrasi karate berbasis Laravel untuk mengelola kontingen, peserta, event, kategori pertandingan, pembayaran, verifikasi berkas, dan hasil kejuaraan.

## Gambaran Singkat

Project ini memakai Laravel sebagai backend utama dengan Blade, Livewire, Tailwind, dan Vite untuk antarmuka. Akses fitur dikendalikan melalui role dan permission menggunakan Spatie Laravel Permission.

## Arsitektur Aplikasi

Struktur folder utama dibagi berdasarkan tanggung jawab fitur agar mudah dipelihara:

- `app/Http/Controllers` berisi controller utama untuk dashboard, profil, kontingen, peserta, laporan, dan admin event.
- `app/Modules/AuthManagement/Controllers` berisi controller khusus manajemen user, role, dan permission.
- `app/Services` berisi logika bisnis yang dipakai ulang, seperti pengelolaan peserta dan registrasi.
- `app/Models` berisi model Eloquent untuk entitas domain seperti `User`, `Contingent`, `Participant`, `Event`, `Payment`, `Registration`, dan `Result`.
- `app/Enums` berisi enum untuk status dan tipe data domain.
- `resources/views` berisi Blade view yang dipisah per modul seperti `dashboard`, `auth`, `admin`, `participants`, `kontingen`, `role`, `permission`, dan `reports`.
- `routes/web.php` berisi route aplikasi utama berbasis web.
- `routes/auth.php` berisi route autentikasi bawaan Laravel Breeze.
- `database/migrations` berisi definisi schema database.
- `tests` berisi test suite Pest untuk unit dan feature test.

Pola penamaan file mengikuti konvensi Laravel: controller memakai akhiran `Controller`, model memakai nama domain tunggal, migration memakai timestamp dan aksi tabel, dan view dipisah sesuai modul fitur.

## API / Route Yang Tersedia

Project ini tidak memiliki `routes/api.php` terpisah. Endpoint yang tersedia berada di route web dan dilindungi middleware sesuai kebutuhan.

### Autentikasi

- `GET /login` menampilkan form login.
- `POST /login` memproses login.
- `POST /logout` melakukan logout.
- `GET /verify-email` menampilkan halaman verifikasi email.
- `GET /verify-email/{id}/{hash}` memverifikasi email.
- `POST /email/verification-notification` mengirim ulang notifikasi verifikasi.
- `GET /confirm-password` menampilkan konfirmasi password.
- `POST /confirm-password` memproses konfirmasi password.
- `PUT /password` memperbarui password.

### Dashboard dan Profil

- `GET /dashboard` menampilkan dashboard sesuai role.
- `GET /profile` menampilkan form profil.
- `PATCH /profile` memperbarui profil.
- `PATCH /profile/kontingen` memperbarui data kontingen milik user.
- `DELETE /profile` menghapus akun.

### Manajemen Auth

- `GET /auth/users` sampai resource user penuh untuk manajemen user.
- `GET /auth/roles` sampai resource role penuh untuk super-admin.
- `GET /auth/permissions` sampai resource permission penuh untuk super-admin.

### Kontingen

- Resource `kontingen` tersedia untuk view, create, dan edit data kontingen.
- `DELETE /kontingen/{kontingen}` menghapus kontingen.

### Peserta

- Resource `participants` tersedia untuk view, create, dan edit data peserta.
- `DELETE /participants/{participant}` menghapus peserta.

### Admin Event

- Resource `admin/events` untuk CRUD event.
- `PATCH /admin/events/{event}/transition` mengubah status event.
- `POST /admin/events/{event}/categories` menambah kategori event.
- `GET /admin/event-categories/{eventCategory}` melihat detail kategori.
- `GET /admin/event-categories/{eventCategory}/edit` membuka form edit kategori.
- `PUT /admin/event-categories/{eventCategory}` memperbarui kategori.
- `DELETE /admin/event-categories/{eventCategory}` menghapus kategori.
- `POST /admin/event-categories/{eventCategory}/sub-categories` menambah sub-kategori.
- `GET /admin/sub-categories/{subCategory}/edit` membuka form edit sub-kategori.
- `PUT /admin/sub-categories/{subCategory}` memperbarui sub-kategori.
- `DELETE /admin/sub-categories/{subCategory}` menghapus sub-kategori.

### Laporan

- `GET /reports` menampilkan halaman laporan.

## Schema Database

### Tabel Inti

| Tabel | Kolom Utama | Relasi / Catatan |
| --- | --- | --- |
| `users` | `id`, `name`, `username`, `email`, `password`, `email_verified_at` | User login utama. `username` ditambahkan setelah tabel awal dibuat. |
| `contingents` | `id`, `user_id`, `name`, `official_name`, `phone`, `address` | Satu user memiliki satu kontingen. |
| `participants` | `id`, `contingent_id`, `type`, `nik`, `name`, `birth_date`, `gender`, `provinsi`, `institusi`, `photo`, `document`, `is_verified`, `verified_at`, `verified_by` | Data peserta/atlet/pelatih/ofisial milik kontingen. |
| `events` | `id`, `name`, `poster`, `event_date`, `registration_deadline`, `coach_fee`, `event_fee`, `status` | Menyimpan event dan status lifecycle event. |
| `event_categories` | `id`, `event_id`, `type`, `class_name`, `min_birth_date`, `max_birth_date` | Kategori per event, dibatasi rentang tanggal lahir. |
| `sub_categories` | `id`, `event_category_id`, `name`, `gender`, `price`, `min_participants`, `max_participants` | Sub kategori pertandingan, mendukung individu dan beregu. |
| `payments` | `id`, `contingent_id`, `event_id`, `total_amount`, `transfer_proof`, `status`, `rejection_reason`, `verified_at`, `verified_by` | Pembayaran registrasi event per kontingen. |
| `registrations` | `id`, `participant_id`, `payment_id`, `sub_category_id`, `status_berkas`, `rejection_reason`, `verified_at`, `verified_by`, `deleted_at` | Registrasi peserta ke sub-kategori tertentu. |
| `results` | `id`, `registration_id`, `medal_type` | Hasil akhir registrasi berupa medali. |
| `activity_logs` | `id`, `user_id`, `action`, `subject_type`, `subject_id`, `description`, `properties` | Audit log aktivitas aplikasi. |

### Tabel Role dan Permission

Project ini menggunakan package Spatie Laravel Permission, sehingga tersedia tabel:

- `roles`
- `permissions`
- `model_has_roles`
- `model_has_permissions`
- `role_has_permissions`

### Catatan Constraint Penting

- `event_categories` memiliki validasi database agar `min_birth_date <= max_birth_date`.
- `sub_categories` memiliki validasi database agar `min_participants <= max_participants`.
- Banyak foreign key memakai `restrictOnDelete` atau `nullOnDelete` untuk menjaga integritas data.
- `registrations` memakai soft delete.

## Setup Project

1. Install dependency PHP dan JavaScript.

```bash
composer install
npm install
```

2. Salin file environment dan generate application key.

```bash
copy .env.example .env
php artisan key:generate
```

3. Atur konfigurasi database di file `.env`.

4. Jalankan migration.

```bash
php artisan migrate
```

5. Jika aplikasi memakai storage untuk upload file, buat symlink storage.

```bash
php artisan storage:link
```

6. Jalankan seeder jika diperlukan.

```bash
php artisan db:seed
```

## Stack Yang Digunakan

- Backend: PHP 8.2
- Framework: Laravel 11.31
- Frontend rendering: Blade
- Interaksi UI: Livewire 3.x dan Alpine.js
- Build asset: Vite 6
- Styling: Tailwind CSS 3
- Permission system: Spatie Laravel Permission 6.24
- Testing: Pest 3.8

## Library Yang Digunakan

### Production

- `laravel/framework` ^11.31
- `livewire/livewire` ^3.4
- `spatie/laravel-permission` ^6.24
- `laravel/tinker` ^2.9

### Development

- `laravel/breeze` ^2.3
- `laravel/pail` ^1.1
- `laravel/pint` ^1.13
- `laravel/sail` ^1.26
- `fakerphp/faker` ^1.23
- `mockery/mockery` ^1.6
- `nunomaduro/collision` ^8.1
- `pestphp/pest` ^3.8
- `pestphp/pest-plugin-laravel` ^3.2
- `alpinejs` ^3.4.2
- `axios` ^1.7.4
- `concurrently` ^9.0.1
- `laravel-vite-plugin` ^1.2.0
- `postcss` ^8.4.31
- `tailwindcss` ^3.1.0
- `autoprefixer` ^10.4.2
- `vite` ^6.0.11
- `@tailwindcss/forms` ^0.5.2

## Cara Run Aplikasi

### Mode development

```bash
composer run dev
```

Perintah ini akan menjalankan beberapa proses sekaligus: `php artisan serve`, queue listener, log viewer (`pail`), dan Vite dev server.

### Alternatif manual

```bash
php artisan serve
npm run dev
```

## Cara Test Aplikasi

Jalankan seluruh test suite dengan:

```bash
php artisan test
```

Atau jika ingin langsung memakai Pest:

```bash
vendor/bin/pest
```

Konfigurasi test sudah memakai `RefreshDatabase` untuk feature test, jadi database akan disiapkan ulang selama pengujian.

## Versi Teknologi Dan Template

| Komponen | Versi |
| --- | --- |
| PHP | 8.2 |
| Laravel | 11.31 |
| Livewire | 3.x |
| Spatie Laravel Permission | 6.24 |
| Laravel Breeze | 2.3 |
| Laravel Pail | 1.1 |
| Laravel Pint | 1.13 |
| Laravel Sail | 1.26 |
| Pest | 3.8 |
| Pest Plugin Laravel | 3.2 |
| Vite | 6.0.11 |
| Tailwind CSS | 3.1.0 |
| Alpine.js | 3.4.2 |
| Axios | 1.7.4 |
| Concurrently | 9.0.1 |
| PostCSS | 8.4.31 |
| Autoprefixer | 10.4.2 |
| @tailwindcss/forms | 0.5.2 |

### Template / Starter Yang Dipakai

- Laravel application skeleton sebagai basis project.
- Laravel Breeze untuk scaffolding autentikasi.
- Livewire untuk komponen interaktif.

## Struktur File Singkat

```text
app/
	Http/Controllers/
	Modules/AuthManagement/Controllers/
	Models/
	Enums/
	Services/
resources/
	views/
database/
	migrations/
	seeders/
	factories/
routes/
	web.php
	auth.php
tests/
	Feature/
	Unit/
```

## Catatan Tambahan

- Aplikasi ini memakai role dan permission sehingga akses fitur sangat bergantung pada data seed role/permission.
- Endpoint event dan registrasi ditujukan untuk proses registrasi karate berbasis kontingen.
- Jika Anda menambahkan API JSON terpisah di masa depan, disarankan membuat `routes/api.php` dan mendokumentasikan endpoint tersebut di bagian terpisah.
