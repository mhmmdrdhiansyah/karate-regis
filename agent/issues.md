# Issues: Fitur User & Kontingen

## Context
Review fitur user dan kontingen menemukan beberapa bug kritis, duplikasi kode, struktur tidak konsisten, dan fitur yang belum lengkap. Plan ini mencakup perbaikan bug, migrasi struktur modular ke standar Laravel, ekstraksi kode duplikat, dan penambahan fitur baru.

---

## Phase 1: Bug Fixes (Kritis)

### 1.1 Fix unique validation di UpdateUserRequest
**File**: `app/Modules/AuthManagement/Requests/UpdateUserRequest.php:18-19`
- `$this->user->id` → `$this->route('user')->id` (kedua baris username & email)

### 1.2 Fix kontingen destroy tidak hapus data kontingen
**File**: `app/Http/Controllers/KontingenManagementController.php:113-119`
- Bungkus dalam `DB::transaction`, hapus kontingen dulu baru user:
```php
DB::transaction(function () use ($kontingen) {
    $kontingen->delete();
    $kontingen->user->delete();
});
```

### 1.3 Fix XSS pada toastr
**File**: Semua view yang punya `toastr.success("{{ session(...) }}")` (~16 lokasi)
- Ganti `"{{ session('success') }}"` dengan `@js(session('success'))`
- File terdampak: user/index, role/index, permission/index, kontingen/index, kontingen/edit, kontingen/show, participants/index, participants/show, profile/edit

### 1.4 Fix typo `@endpush>`
**File**: `resources/views/admin/kontingen/edit.blade.php:251`
- `@endpush>` → `@endpush`

---

## Phase 2: Migrasi Module ke Standar Laravel

### 2.1 Pindahkan Controllers (3 file)
Buat file baru, salin kode, ubah namespace:
| Dari | Ke |
|------|-----|
| `app/Modules/AuthManagement/Controllers/UserController.php` | `app/Http/Controllers/UserController.php` |
| `app/Modules/AuthManagement/Controllers/RoleController.php` | `app/Http/Controllers/RoleController.php` |
| `app/Modules/AuthManagement/Controllers/PermissionController.php` | `app/Http/Controllers/PermissionController.php` |

Perubahan di tiap controller:
- Namespace: `App\Http\Controllers`
- Import Request dari `App\Http\Requests\*`
- Import Model: `App\Models\User`, `Spatie\Permission\Models\Role`, `Spatie\Permission\Models\Permission`
- Ganti `bcrypt()` → `Hash::make()` (UserController)

### 2.2 Pindahkan Requests (6 file)
| Dari | Ke |
|------|-----|
| `app/Modules/AuthManagement/Requests/StoreUserRequest.php` | `app/Http/Requests/StoreUserRequest.php` |
| `app/Modules/AuthManagement/Requests/UpdateUserRequest.php` | `app/Http/Requests/UpdateUserRequest.php` |
| `app/Modules/AuthManagement/Requests/StoreRoleRequest.php` | `app/Http/Requests/StoreRoleRequest.php` |
| `app/Modules/AuthManagement/Requests/UpdateRoleRequest.php` | `app/Http/Requests/UpdateRoleRequest.php` |
| `app/Modules/AuthManagement/Requests/StorePermissionRequest.php` | `app/Http/Requests/StorePermissionRequest.php` |
| `app/Modules/AuthManagement/Requests/UpdatePermissionRequest.php` | `app/Http/Requests/UpdatePermissionRequest.php` |

### 2.3 Update routes/web.php
```php
// Ganti 3 import dari module ke standar
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
```

### 2.4 Update Tests (3 file)
- `tests/Feature/AuthManagement/UserCrudTest.php`: ubah import ke `App\Models\User`, `Spatie\Permission\Models\Role`, `Spatie\Permission\Models\Permission`
- `tests/Feature/AuthManagement/RoleCrudTest.php`: sama
- `tests/Feature/AuthManagement/PermissionCrudTest.php`: sama

### 2.5 Factories
- Hapus `database/factories/AuthManagement/UserFactory.php` (duplikat yang tidak lengkap)
- Buat `database/factories/RoleFactory.php` (namespace `Database\Factories`, model `Spatie\Permission\Models\Role`)
- Buat `database/factories/PermissionFactory.php` (namespace `Database\Factories`, model `Spatie\Permission\Models\Permission`)

### 2.6 Hapus direktori module
- Hapus seluruh `app/Modules/` (termasuk semua .md docs)
- Hapus `database/factories/AuthManagement/`

---

## Phase 3: Hapus Last Login dari UI

- `resources/views/user/index.blade.php`: hapus kolom header `<th>Last Login</th>`, `<td>Yesterday</td>` di desktop, dan row mobile card
- `resources/views/user/show.blade.php`: hapus `<div>Last Login</div>` + `<div>Yesterday (Dummy)</div>`

---

## Phase 4: Ekstrak Kode Duplikat

### 4.1 Shared Mobile Card CSS
**Buat**: `resources/views/partials/mobile-card-styles.blade.php`
- Terima prop `$prefix` (default: `mc`)
- Satu set CSS rule generik (`.{{ $prefix }}-card`, `.{{ $prefix }}-card-hd`, dll)
- Update `user/index.blade.php` → `@include('partials.mobile-card-styles', ['prefix' => 'u'])`
- Update `kontingen/index.blade.php` → `@include('partials.mobile-card-styles', ['prefix' => 'k'])`

### 4.2 Shared Province/Regency JS
**Buat**: `resources/views/partials/wilayah-select-js.blade.php`
- Terima props opsional: `$savedProvince`, `$savedRegency` (untuk edit mode)
- Ekstrak logika AJAX loading province & regency
- Update `kontingen/create.blade.php` → `@include('partials.wilayah-select-js')`
- Update `kontingen/edit.blade.php` → `@include('partials.wilayah-select-js', ['savedProvince' => $kontingen->province, 'savedRegency' => $kontingen->regency])`

---

## Phase 5: Blade Icon Component

### 5.1 Buat Component
**Buat**: `app/View/Components/Icon.php` + `resources/views/components/icon.blade.php`
- Prop: `name` (string), `class` (opsional)
- Icons: `search`, `plus`, `eye`, `edit`, `trash`, `chevron-down`, `collapse`, `toggle-on`, `toggle-off`, `envelope`, `folder`
- Gunakan `@switch` untuk render SVG yang sesuai

### 5.2 Migrasi Views
Ganti inline SVG di semua view: `<x-icon name="search" class="svg-icon-1" />`
- Prioritas: `user/index.blade.php`, `kontingen/index.blade.php`, lalu file lainnya

---

## Phase 6: Password Update oleh Admin

### 6.1 Update UpdateUserRequest
**File**: `app/Http/Requests/UpdateUserRequest.php` (lokasi baru setelah Phase 2)
- Tambah rule: `'password' => ['sometimes', 'nullable', 'confirmed', Password::defaults()]`
- Tambah validation messages untuk password

### 6.2 Update UserController::update()
**File**: `app/Http/Controllers/UserController.php` (lokasi baru setelah Phase 2)
```php
$data = $request->validated();
if (empty($data['password'])) {
    unset($data['password'], $data['password_confirmation']);
} else {
    $data['password'] = Hash::make($data['password']);
}
$user->update(Arr::except($data, ['role', 'password_confirmation']));
$user->syncRoles($request->role);
```

---

## Phase 7: Fitur Baru

### 7.1 Soft Deletes
**Migrasi baru**: `add_soft_deletes_to_users_and_contingents_table.php`
- Tambah `softDeletes()` ke tabel users dan contingents

**Model changes**:
- `app/Models/User.php`: tambah `use SoftDeletes;`
- `app/Models/Contingent.php`: tambah `use SoftDeletes;`

**Controller changes**:
- `UserController.php`: tambah `restore()` dan `forceDelete()` methods
- `KontingenManagementController.php`: tambah `restore()` dan `forceDelete()` methods

**Routes baru**:
```php
Route::post('auth/users/{user}/restore', [UserController::class, 'restore'])->name('auth.users.restore');
Route::delete('auth/users/{user}/force-delete', [UserController::class, 'forceDelete'])->name('auth.users.forceDelete');
```

**UI**: Tambah tab "Trashed" di user/index dan kontingen/index untuk menampilkan record terhapus dengan tombol Restore & Force Delete

### 7.2 Reset Password oleh Admin
**Route baru**:
```php
Route::post('auth/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('auth.users.reset-password');
```

**Controller**: Tambah `resetPassword()` di UserController
- Validasi: `new_password` required, confirmed, Password::defaults()
- Update user password

**UI**: Tambah modal "Reset Password" di `user/show.blade.php` dan tombol di `user/create.blade.php` (edit mode)

### 7.3 Activity Log UI
**Buat**: `app/Http/Controllers/Admin/ActivityLogController.php`
- `index()`: list logs dengan search, filter by action, pagination
- Middleware: `permission:view activity logs` (tambah permission di seeder)

**Buat**: `resources/views/admin/activity-logs/index.blade.php`
- Card + table + pagination pattern (ikuti pola kontingen/index)

**Route**: `GET admin/activity-logs` → `admin.activity-logs.index`

**Tambah logging** ke UserController dan KontingenManagementController:
```php
ActivityLog::create([
    'user_id' => auth()->id(),
    'action' => 'user.created',
    'subject_type' => 'User',
    'subject_id' => $user->id,
    'description' => "Admin membuat user baru: {$user->name}",
]);
```

**Sidebar**: Tambah menu "Activity Log" di section Administrator, guard `@can('view activity logs')`

**Seeder**: Tambah permission `view activity logs` ke super-admin dan panitia

### 7.4 Kontingen Status (is_active)
**Migrasi baru**: `add_is_active_to_contingents_table.php`
- `$table->boolean('is_active')->default(true)`

**Model**: Tambah `is_active` ke fillable + cast `'is_active' => 'boolean'`

**Controller**: Tambah `toggleStatus()` di KontingenManagementController
- Toggle `is_active`, redirect back dengan success message

**Route**: `POST kontingen/{kontingen}/toggle-status`

**UI**: Badge status (hijau/merah) di kontingen/index dan kontingen/show, tombol toggle

**Enforcement**: Di registration flow, cek `is_active` kontingen

---

## Phase 8: Minor Fixes

1. **`bcrypt()` → `Hash::make()`**: UserController (saat Phase 2), KontingenManagementController line 62
2. **Pagination info saat kosong**: `user/index.blade.php` wrap pagination row dalam `@if($users->count() > 0)`
3. **Tombol Edit User tidak terlink**: `user/show.blade.php:68` ganti `<button>` ke `<a href="{{ route('auth.users.edit', $user) }}">`
4. **Simplify kontingen routes**: Hapus pemisahan destroy route, gabung ke resource route tunggal (middleware di controller sudah handle)
5. **Permission check di kontingen views**: Pastikan tombol delete di kontingen/index dibungkus `@can('delete kontingen')`

---

## Verification

1. `php artisan route:list` — verifikasi semua route terdaftar
2. `php artisan test --filter=AuthManagement` — semua test harus pass setelah Phase 2
3. Manual test flow:
   - Login sebagai super-admin → buat user → edit user → ubah username (test bug #1 fix)
   - Buat kontingen → hapus kontingen → verifikasi user & contingent data terhapus
   - Edit user → isi password baru → login sebagai user tersebut (test Phase 6)
   - Hapus user → cek DB soft delete → restore → force delete
   - Reset password user dari admin → login dengan password baru
   - Toggle status kontingen → cek badge berubah
   - Akses halaman Activity Log → verifikasi log tercatat
