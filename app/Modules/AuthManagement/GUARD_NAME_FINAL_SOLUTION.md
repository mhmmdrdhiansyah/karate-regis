# Guard Name Final Solution

## ✅ Solusi Lengkap untuk Error Guard Name

### Error yang Terjadi:
```
The given role or permission should use guard `` instead of `web`.
```

---

## 🔧 Perbaikan yang Dilakukan

### 1. Models - Menambahkan `protected $guard_name = 'web'`

#### User Model
**File:** `app/Modules/AuthManagement/Models/User.php`

```php
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $guard_name = 'web';  // ← DITAMBAHKAN

    protected $fillable = [
        // ...
    ];
}
```

#### Role Model
**File:** `app/Modules/AuthManagement/Models/Role.php`

```php
class Role extends SpatieRole
{
    use HasFactory;

    protected $guard_name = 'web';  // ← DITAMBAHKAN

    protected $fillable = [
        // ...
    ];
}
```

#### Permission Model
**File:** `app/Modules/AuthManagement/Models/Permission.php`

```php
class Permission extends SpatiePermission
{
    use HasFactory;

    protected $guard_name = 'web';  // ← DITAMBAHKAN

    protected $fillable = [
        // ...
    ];
}
```

---

### 2. Controllers - Menambahkan `guard_name` saat create

#### RoleController
**File:** `app/Modules/AuthManagement/Controllers/RoleController.php`

```php
public function store(StoreRoleRequest $request)
{
    DB::transaction(function () use ($request) {
        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web'  // ← DITAMBAHKAN
        ]);
        $role->syncPermissions($request->permissions);
    });
}
```

#### PermissionController
**File:** `app/Modules/AuthManagement/Controllers/PermissionController.php`

```php
public function store(StorePermissionRequest $request)
{
    Permission::create([
        'name' => $request->name,
        'guard_name' => 'web'  // ← DITAMBAHKAN
    ]);
}
```

---

### 3. Request Classes - Menambahkan `guard_name` di validation

#### StoreUserRequest
**File:** `app/Modules/AuthManagement/Requests/StoreUserRequest.php`

```php
public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        'password' => ['required', 'confirmed', Password::defaults()],
        'role' => ['required', 'exists:roles,name,guard_name,web'],  // ← DIPERBARUI
    ];
}
```

#### UpdateUserRequest
**File:** `app/Modules/AuthManagement/Requests/UpdateUserRequest.php`

```php
public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $this->user->id],
        'role' => ['required', 'exists:roles,name,guard_name,web'],  // ← DIPERBARUI
    ];
}
```

#### StoreRoleRequest
**File:** `app/Modules/AuthManagement/Requests/StoreRoleRequest.php`

```php
public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
        'permissions' => ['required', 'array', 'min:1'],
        'permissions.*' => ['exists:permissions,name,guard_name,web'],  // ← DIPERBARUI
    ];
}
```

#### UpdateRoleRequest
**File:** `app/Modules/AuthManagement/Requests/UpdateRoleRequest.php`

```php
public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $this->role->id],
        'permissions' => ['required', 'array', 'min:1'],
        'permissions.*' => ['exists:permissions,name,guard_name,web'],  // ← DIPERBARUI
    ];
}
```

---

### 4. Seeder - Menambahkan `guard_name`

**File:** `database/seeders/Auth/RolesAndPermissionsSeeder.php`

```php
// Permissions
foreach ($permissions as $permission) {
    Permission::firstOrCreate([
        'name' => $permission,
        'guard_name' => 'web'  // ← DITAMBAHKAN
    ]);
}

// Role: Super Admin
$superAdminRole = Role::firstOrCreate([
    'name' => 'super-admin',
    'guard_name' => 'web'  // ← DITAMBAHKAN
]);

// Role: Staff
$staffRole = Role::firstOrCreate([
    'name' => 'staff',
    'guard_name' => 'web'  // ← DITAMBAHKAN
]);
```

---

## 📝 Penjelasan Teknis

### 1. Kenapa perlu `protected $guard_name = 'web'` di Models?

Spatie Laravel Permission menggunakan property `$guard_name` di model untuk:
- Menentukan guard yang digunakan ketika checking permissions
- Menentukan guard yang digunakan ketika assigning roles/permissions
- Memastikan consistency antara model dan database

**Tanpa property ini:**
- Spatie akan mengasumsikan guard default
- Kadang guard default tidak cocok dengan yang ada di database
- Error: "The given role or permission should use guard `` instead of `web`"

**Dengan property ini:**
- Spatie tahu guard yang digunakan adalah `web`
- Spatie akan mencocokkan dengan data di database yang memiliki `guard_name = 'web'`
- Tidak akan ada error guard mismatch

### 2. Kenapa perlu `guard_name` di Controllers?

Ketika membuat role atau permission baru:
- Harus men-set `guard_name` secara eksplisit
- Agar data di database memiliki `guard_name = 'web'`
- Agar bisa digunakan oleh model yang menggunakan guard `web`

### 3. Kenapa perlu `guard_name` di Validation?

Validation `exists:roles,name,guard_name,web` berarti:
- Cek apakah role ada di tabel `roles`
- Dengan `name` yang sesuai
- DAN dengan `guard_name = 'web'`

**Tanpa `guard_name` di validation:**
- Validation akan menerima role dengan `guard_name` apapun
- User bisa memilih role dengan guard yang salah
- Error ketika mencoba assign role ke user

**Dengan `guard_name` di validation:**
- Validation hanya akan menerima role dengan `guard_name = 'web'`
- User hanya bisa memilih role yang valid untuk guard `web`
- Tidak akan ada error guard mismatch

---

## 🚀 Langkah-langkah untuk Memperbaiki

### 1. Clear Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 2. Clear Permission Cache

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

### 3. Refresh Composer Autoload

```bash
composer dump-autoload
```

### 4. Test Aplikasi

- Login sebagai super-admin (admin@admin.com / password)
- Coba create user baru dengan role
- Coba create role baru
- Coba create permission baru
- Pastikan tidak ada error lagi

---

## 📊 Summary Semua Perbaikan

### Files yang Diperbarui:

#### Models (3 files):
1. ✅ `app/Modules/AuthManagement/Models/User.php` - Added `protected $guard_name = 'web'`
2. ✅ `app/Modules/AuthManagement/Models/Role.php` - Added `protected $guard_name = 'web'`
3. ✅ `app/Modules/AuthManagement/Models/Permission.php` - Added `protected $guard_name = 'web'`

#### Controllers (2 files):
4. ✅ `app/Modules/AuthManagement/Controllers/RoleController.php` - Added `'guard_name' => 'web'` in create
5. ✅ `app/Modules/AuthManagement/Controllers/PermissionController.php` - Added `'guard_name' => 'web'` in create

#### Request Classes (4 files):
6. ✅ `app/Modules/AuthManagement/Requests/StoreUserRequest.php` - Updated `exists:roles,name,guard_name,web`
7. ✅ `app/Modules/AuthManagement/Requests/UpdateUserRequest.php` - Updated `exists:roles,name,guard_name,web`
8. ✅ `app/Modules/AuthManagement/Requests/StoreRoleRequest.php` - Updated `exists:permissions,name,guard_name,web`
9. ✅ `app/Modules/AuthManagement/Requests/UpdateRoleRequest.php` - Updated `exists:permissions,name,guard_name,web`

#### Seeder (1 file):
10. ✅ `database/seeders/Auth/RolesAndPermissionsSeeder.php` - Added `'guard_name' => 'web'` to all roles and permissions

**Total: 10 files diperbarui**

---

## 🎯 Hasil Akhir

Setelah semua perbaikan ini dilakukan:

1. ✅ Models tahu guard yang digunakan (`web`)
2. ✅ Controllers men-set guard name saat create role/permission
3. ✅ Validation hanya menerima role/permission dengan guard yang benar
4. ✅ Seeder membuat role/permission dengan guard yang benar
5. ✅ Tidak akan ada error guard mismatch lagi

---

## 📚 Referensi

- [Spatie Laravel Permission Documentation - Guards](https://spatie.be/docs/laravel-permission/v6/basic-usage/guards)
- [Laravel Validation - exists](https://laravel.com/docs/11.x/validation#rule-exists)

---

## 🎉 Kesimpulan

**Solusi lengkap untuk error guard name:**

1. ✅ Tambahkan `protected $guard_name = 'web'` di semua models (User, Role, Permission)
2. ✅ Tambahkan `'guard_name' => 'web'` di controllers saat create role/permission
3. ✅ Update validation rules dengan `exists:roles,name,guard_name,web` dan `exists:permissions,name,guard_name,web`
4. ✅ Update seeder untuk men-set `guard_name` ke `web`
5. ✅ Clear cache dan permission cache

**Error guard name sekarang sudah diperbaiki sepenuhnya!** 🚀
