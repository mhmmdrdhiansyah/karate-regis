# Guard Name Fix Instructions

## 🚨 Error yang Terjadi

```
The given role or permission should use guard `` instead of `web`.
```

## 🔍 Penyebab Masalah

Error ini terjadi karena:
1. Roles dan Permissions di database TIDAK memiliki `guard_name` yang diset
2. Spatie Laravel Permission membutuhkan `guard_name` untuk mencocokkan dengan guard yang digunakan di aplikasi
3. Default guard di Laravel adalah `web`

## ✅ Solusi yang Dilakukan

### 1. Update Seeder

**File:** `database/seeders/Auth/RolesAndPermissionsSeeder.php`

**Perubahan:**

#### Permissions
```php
// ❌ SEBELUM
Permission::firstOrCreate(['name' => $permission]);

// ✅ SESUDAH
Permission::firstOrCreate([
    'name' => $permission,
    'guard_name' => 'web'
]);
```

#### Role: Super Admin
```php
// ❌ SEBELUM
$superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);

// ✅ SESUDAH
$superAdminRole = Role::firstOrCreate([
    'name' => 'super-admin',
    'guard_name' => 'web'
]);
```

#### Role: Staff
```php
// ❌ SEBELUM
$staffRole = Role::firstOrCreate(['name' => 'staff']);

// ✅ SESUDAH
$staffRole = Role::firstOrCreate([
    'name' => 'staff',
    'guard_name' => 'web'
]);
```

## 🚀 Langkah-langkah untuk Memperbaiki

### 1. Jalankan Seeder Lagi

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

Atau jalankan semua seeders:

```bash
php artisan db:seed
```

### 2. Clear Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 3. Clear Permission Cache

```bash
php artisan permission:cache-reset
```

Atau gunakan artisan tinker:

```bash
php artisan tinker
```

Kemudian jalankan:

```php
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
exit
```

### 4. Test Aplikasi

- Login sebagai super-admin (admin@admin.com / password)
- Coba create user baru
- Pastikan tidak ada error lagi

## 📝 Penjelasan Teknis

### Apa itu Guard Name?

Guard name adalah identifier untuk authentication guard di Laravel. Default guard di Laravel adalah `web`.

### Kenapa Guard Name Penting?

Spatie Laravel Permission menggunakan guard name untuk:
1. Mencocokkan roles dan permissions dengan guard yang digunakan
2. Memastikan user memiliki role/permission yang sesuai dengan guard
3. Menghindari konflik jika menggunakan multiple guards

### Kenapa Error Ini Terjadi?

1. **Seeder Lama Tidak Set Guard Name**
   - Seeder sebelumnya hanya men-set `name`, tidak men-set `guard_name`
   - Spatie Permission mengasumsikan guard_name default, tapi kadang tidak cocok

2. **Database Tidak Update**
   - Roles dan permissions yang sudah ada di database tidak memiliki `guard_name`
   - Perlu di-update dengan seeder yang baru

## 🔍 Cara Cek Guard Name

Anda bisa cek guard name di database:

```sql
SELECT * FROM roles;
SELECT * FROM permissions;
```

Pastikan kolom `guard_name` memiliki nilai `web`.

## 📚 Referensi

- [Spatie Laravel Permission Documentation](https://spatie.be/docs/laravel-permission/v6/basic-usage/guards)
- [Laravel Authentication Guards](https://laravel.com/docs/11.x/authentication)

---

## 🎯 Summary

1. ✅ Seeder diperbarui untuk men-set `guard_name` ke `web`
2. ✅ Jalankan seeder lagi untuk update database
3. ✅ Clear cache dan permission cache
4. ✅ Test aplikasi

**Masalah guard name sekarang sudah diperbaiki!** 🎉
