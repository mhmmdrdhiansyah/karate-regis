# Guard Name Complete Fix

## 🚨 Error yang Terjadi

```
The given role or permission should use guard `` instead of `web`.
```

## 🔍 Penyebab Masalah

Error ini terjadi karena:
1. **Role dan Permission Baru Tidak Memiliki guard_name**
   - Ketika role atau permission dibuat melalui UI (create form), `guard_name` tidak diset
   - Spatie Laravel Permission membutuhkan `guard_name` untuk mencocokkan dengan guard yang digunakan

2. **Database Tidak Update**
   - Roles dan permissions yang sudah ada di database mungkin tidak memiliki `guard_name`
   - Perlu di-update dengan seeder yang baru

## ✅ Solusi Lengkap

### 1. Perbaikan Seeder

**File:** `database/seeders/Auth/RolesAndPermissionsSeeder.php`

Semua permissions dan roles sekarang memiliki `guard_name`:

```php
// Permissions
Permission::firstOrCreate([
    'name' => $permission,
    'guard_name' => 'web'
]);

// Role: Super Admin
$superAdminRole = Role::firstOrCreate([
    'name' => 'super-admin',
    'guard_name' => 'web'
]);

// Role: Staff
$staffRole = Role::firstOrCreate([
    'name' => 'staff',
    'guard_name' => 'web'
]);
```

### 2. Perbaikan RoleController

**File:** `app/Modules/AuthManagement/Controllers/RoleController.php`

**Line 45:** Menambahkan `guard_name` saat membuat role baru

```php
// ❌ SEBELUM
$role = Role::create(['name' => $request->name]);

// ✅ SESUDAH
$role = Role::create([
    'name' => $request->name,
    'guard_name' => 'web'
]);
```

### 3. Perbaikan PermissionController

**File:** `app/Modules/AuthManagement/Controllers/PermissionController.php`

**Line 39:** Menambahkan `guard_name` saat membuat permission baru

```php
// ❌ SEBELUM
Permission::create(['name' => $request->name]);

// ✅ SESUDAH
Permission::create([
    'name' => $request->name,
    'guard_name' => 'web'
]);
```

### 4. UserController Tidak Perlu Perbaikan

UserController sudah benar karena menggunakan methods dari Spatie:
- `assignRole($role)` - Method ini otomatis menangani guard
- `syncRoles($roles)` - Method ini otomatis menangani guard

## 🚀 Langkah-langkah untuk Memperbaiki

### 1. Jalankan Seeder Lagi

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

Atau jalankan semua seeders:

```bash
php artisan db:seed
```

### 2. Update Data yang Sudah Ada di Database

Jika Anda sudah memiliki roles dan permissions di database tanpa `guard_name`, jalankan SQL berikut:

```sql
-- Update semua roles
UPDATE roles SET guard_name = 'web' WHERE guard_name IS NULL OR guard_name = '';

-- Update semua permissions
UPDATE permissions SET guard_name = 'web' WHERE guard_name IS NULL OR guard_name = '';
```

Atau gunakan artisan tinker:

```bash
php artisan tinker
```

Kemudian jalankan:

```php
// Update roles
use Spatie\Permission\Models\Role;
Role::whereNull('guard_name')->orWhere('guard_name', '')->update(['guard_name' => 'web']);

// Update permissions
use Spatie\Permission\Models\Permission;
Permission::whereNull('guard_name')->orWhere('guard_name', '')->update(['guard_name' => 'web']);

exit
```

### 3. Clear Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 4. Clear Permission Cache

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

### 5. Test Aplikasi

- Login sebagai super-admin (admin@admin.com / password)
- Coba create role baru
- Coba create permission baru
- Coba create user baru dengan role
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

1. **Role/Permission Baru Tidak Memiliki guard_name**
   - Ketika role atau permission dibuat melalui UI, `guard_name` tidak diset
   - Spatie Permission mengasumsikan guard_name default, tapi kadang tidak cocok

2. **Database Tidak Update**
   - Roles dan permissions yang sudah ada di database tidak memiliki `guard_name`
   - Perlu di-update

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

### Perbaikan yang Dilakukan:

1. ✅ **Seeder** - Menambahkan `guard_name` ke semua permissions dan roles
2. ✅ **RoleController** - Menambahkan `guard_name` saat membuat role baru
3. ✅ **PermissionController** - Menambahkan `guard_name` saat membuat permission baru
4. ✅ **UserController** - Sudah benar (tidak perlu perbaikan)

### Langkah yang Perlu Dilakukan:

1. ✅ Jalankan seeder lagi
2. ✅ Update data yang sudah ada di database (jika ada)
3. ✅ Clear cache
4. ✅ Clear permission cache
5. ✅ Test aplikasi

**Masalah guard name sekarang sudah diperbaiki sepenuhnya!** 🎉
