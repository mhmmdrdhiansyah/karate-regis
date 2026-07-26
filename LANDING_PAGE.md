# Landing Page - Documentation

Landing page untuk aplikasi Combat Pro sudah berhasil dibuat!

## 📋 Fitur

- **Hero Section**: Header yang menarik dengan CTA buttons
- **Features Grid**: 3 fitur utama platform (Quick Notifications, Easy Registration, Live Brackets)
- **Upcoming Events**: Menampilkan 3 event mendatang dari database
- **About Section**: Informasi tentang platform
- **Contact Section**: Informasi kontak
- **Status Check Form**: Form untuk cek status pendaftaran

## 🎨 Design

Landing page menggunakan design yang sama dengan `agent/frontend/landing.html` dengan:
- **Tailwind CSS**: Styling modern dan responsive
- **Color Scheme**: Primary (red), Accent (gold), dengan tema martial arts
- **Typography**: Anton (headlines), Hanken Grotesk (body text)
- **Icons**: Material Symbols Outlined

## 🗄️ Database Structure

### Events Table

Landing page mengambil data dari tabel `events` yang sudah ada:

**Fields yang digunakan untuk landing page:**
- `name` - Judul event
- `event_date` - Tanggal pelaksanaan event
- `poster` - URL/path gambar poster event
- `status` - Status event (hanya yang `RegistrationOpen` yang ditampilkan)

**Mapping ke landing page:**
```
name → title
event_date → date (formatted: 'd M Y')
poster → image (via image_url accessor)
status → hanya RegistrationOpen yang ditampilkan
```

**Fields yang belum ada di Event model:**
- `location` - Lokasi event (saat ini hardcode 'TBD')
- `event_type` - Tipe event (KUMITE/KATA/MIXED, saat ini hardcode 'MIXED')

## 📁 File Structure

```
├── app/
│   ├── Http/Controllers/
│   │   └── LandingController.php      # Controller untuk landing page
│   └── Models/
│       └── Event.php                    # Model Event dengan accessors
├── database/
│   ├── migrations/
│   │   └── 2026_04_27_080200_create_events_table.php
│   └── seeders/
│       └── EventSeeder.php             # Seeder untuk data dummy events
├── resources/
│   └── views/
│       └── welcome.blade.php           # Landing page template
└── routes/
    └── web.php                         # Routes definition
```

## 🚀 Cara Menggunakan

### 1. Run Database Seeder

Untuk mengisi data events dummy:

```bash
php artisan db:seed --class=EventSeeder
```

### 2. Update Event Data

Data events dapat diupdate melalui:
- **Admin Panel**: `/admin/events` (jika ada)
- **Tinker**: `php artisan tinker`

```php
// Update event di tinker
$event = App\Models\Event::find(1);
$event->update([
    'name' => 'Kejuaraan Karate Nasional 2026',
    'event_date' => '2026-10-12',
    'poster' => 'https://example.com/poster.jpg',
    'status' => 'registration_open'
]);
```

### 3. Add Location & Event Type (Optional)

Jika ingin menambahkan field `location` dan `event_type`:

#### A. Buat Migration

```bash
php artisan make:migration add_location_and_type_to_events_table
```

#### B. Update Migration

```php
public function up(): void
{
    Schema::table('events', function (Blueprint $table) {
        $table->string('location')->nullable()->after('poster');
        $table->string('event_type')->default('MIXED')->after('location'); // KUMITE, KATA, MIXED
    });
}
```

#### C. Update Event Model

Tambahkan ke `fillable`:
```php
protected $fillable = [
    'name',
    'poster',
    'event_date',
    'registration_deadline',
    'coach_fee',
    'event_fee',
    'status',
    'location',     // Add this
    'event_type',   // Add this
];
```

#### D. Update LandingController

```php
'type' => $event->event_type ?? 'MIXED',
'location' => $event->location ?? 'TBD',
```

#### E. Run Migration

```bash
php artisan migrate
```

## 📝 Routes

- `GET /` - Landing page (LandingController@index)
- `POST /check-status` - Check registration status (placeholder)

**Note**: Landing page menggunakan conditional routing untuk fitur register:
- Jika route `register` tersedia → Tombol "Daftar Sekarang" akan mengarah ke halaman register
- Jika tidak tersedia → Akan diarahkan ke halaman login dengan teks "Login / Daftar"

## 🎨 Customization

### Update Colors

Di `welcome.blade.php`, cari tailwind config dan update:

```javascript
"primary": "#b9001c",     // Main red color
"accent": "#FFD700",      // Gold accent
"on-background": "#1b1c1c", // Text color
```

### Update Content

- **Hero text**: Edit bagian Hero Section di `welcome.blade.php`
- **Features**: Edit bagian Features Bento Grid
- **Contact info**: Edit bagian Contact Section

### Update Images

Default images menggunakan Unsplash. Untuk menggunakan custom images:

1. Upload images ke `public/assets/media/karate-hero/`
2. Update di `welcome.blade.php`:
```html
<img src="{{ asset('assets/media/karate-hero/custom-image.jpg') }}" ... />
```

## 🔧 Troubleshooting

### Events tidak muncul

Pastikan:
1. Event status = `registration_open` atau `RegistrationOpen`
2. Event date >= hari ini
3. Database seeder sudah dijalankan

```bash
# Cek events di database
php artisan tinker
>>> App\Models\Event::all(['name', 'event_date', 'status']);
```

### Images tidak muncul

1. Cek apakah `poster` field ada isinya
2. Cek apakah path/URL valid
3. Fallback image akan digunakan jika poster tidak ada

### Styling berantakan

Pastikan Tailwind CDN loaded:
- Cek network tab di browser dev tools
- Pastikan tidak ada CSP yang blocking external resources

## 📚 Next Steps

1. ✅ Landing page infrastructure sudah siap
2. ⏳ Implementasi check status pendaftaran
3. ⏳ Tambahkan field location dan event_type ke migration
4. ⏳ Integrasi dengan payment system untuk registration
5. ⏳ SEO optimization (meta tags, sitemap, etc.)
6. ⏳ Multi-language support (ID/EN)

## 🎉 Hasil

Landing page sudah siap dengan:
- Data-driven dari database ✅
- Responsive design ✅
- Modern UI dengan Tailwind CSS ✅
- Easy to customize ✅
- Ready for production (dengan sedikit tweaks) ✅

---

**Created**: 2026-07-25
**Last Updated**: 2026-07-25
