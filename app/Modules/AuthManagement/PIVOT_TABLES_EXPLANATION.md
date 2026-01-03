# Pivot Tables Explanation (model_has_permissions & model_has_roles)

## ❓ Apakah Saya yang Membuat Tabel Ini?

**TIDAK!** Tabel-tabel ini TIDAK saya buat secara manual. Tabel-tabel ini dibuat secara otomatis oleh **Spatie Laravel Permission package** melalui migration.

**Migration File:** `database/migrations/2025_12_29_074939_create_permission_tables.php`

---

## 📊 Tabel-tabel yang Dibuat oleh Spatie Laravel Permission

Package ini membuat 5 tabel:

### 1. `permissions` (Line 23-31)
Menyimpan semua permissions yang ada.

**Struktur:**
- `id` - Primary key
- `name` - Nama permission (contoh: 'create users')
- `guard_name` - Guard yang digunakan (contoh: 'web')
- `created_at`, `updated_at` - Timestamps

**Contoh Data:**
| id | name | guard_name |
|-----|------|------------|
| 1 | create users | web |
| 2 | edit users | web |

---

### 2. `roles` (Line 33-48)
Menyimpan semua roles yang ada.

**Struktur:**
- `id` - Primary key
- `name` - Nama role (contoh: 'admin', 'staff')
- `guard_name` - Guard yang digunakan (contoh: 'web')
- `created_at`, `updated_at` - Timestamps

**Contoh Data:**
| id | name | guard_name |
|-----|------|------------|
| 1 | super-admin | web |
| 2 | staff | web |

---

### 3. `model_has_permissions` (Line 50-72) ⭐
**Tabel PIVOT** yang menghubungkan model (User) dengan permissions secara langsung.

**Fungsi:**
- Menyimpan permissions yang diberikan secara langsung ke model (User)
- Digunakan ketika Anda memberikan permission langsung ke user, bukan melalui role

**Struktur:**
- `permission_id` - Foreign key ke tabel `permissions`
- `model_type` - Tipe model (contoh: 'App\Models\User')
- `model_id` - ID dari model (contoh: user ID)
- Primary key: `[permission_id, model_id, model_type]`

**Contoh Data:**
| permission_id | model_type | model_id |
|--------------|-------------|-----------|
| 1 | App\Models\User | 5 |
| 2 | App\Models\User | 5 |

**Penjelasan:**
User dengan ID 5 memiliki permission 'create users' (ID 1) dan 'edit users' (ID 2) secara langsung.

---

### 4. `model_has_roles` (Line 74-95) ⭐
**Tabel PIVOT** yang menghubungkan model (User) dengan roles.

**Fungsi:**
- Menyimpan roles yang dimiliki oleh model (User)
- Digunakan ketika Anda memberikan role ke user

**Struktur:**
- `role_id` - Foreign key ke tabel `roles`
- `model_type` - Tipe model (contoh: 'App\Models\User')
- `model_id` - ID dari model (contoh: user ID)
- Primary key: `[role_id, model_id, model_type]`

**Contoh Data:**
| role_id | model_type | model_id |
|---------|-------------|-----------|
| 1 | App\Models\User | 5 |
| 2 | App\Models\User | 10 |

**Penjelasan:**
- User dengan ID 5 memiliki role 'super-admin' (ID 1)
- User dengan ID 10 memiliki role 'staff' (ID 2)

---

### 5. `role_has_permissions` (Line 97-112) ⭐
**Tabel PIVOT** yang menghubungkan roles dengan permissions.

**Fungsi:**
- Menyimpan permissions yang dimiliki oleh role
- Digunakan ketika Anda memberikan permission ke role

**Struktur:**
- `permission_id` - Foreign key ke tabel `permissions`
- `role_id` - Foreign key ke tabel `roles`
- Primary key: `[permission_id, role_id]`

**Contoh Data:**
| permission_id | role_id |
|--------------|----------|
| 1 | 1 |
| 2 | 1 |
| 1 | 2 |

**Penjelasan:**
- Role 'super-admin' (ID 1) memiliki permission 'create users' (ID 1) dan 'edit users' (ID 2)
- Role 'staff' (ID 2) memiliki permission 'create users' (ID 1)

---

## 🔄 Cara Kerja Sistem Permission

### Contoh 1: User dengan Role

```php
// 1. Buat role dengan permissions
$role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
$role->givePermissionTo('create users', 'edit users');

// 2. Assign role ke user
$user = User::find(1);
$user->assignRole('admin');
```

**Yang Terjadi di Database:**

1. **Tabel `roles`:**
| id | name | guard_name |
|-----|------|------------|
| 1 | admin | web |

2. **Tabel `role_has_permissions`:**
| permission_id | role_id |
|--------------|----------|
| 1 | 1 |  // create users
| 2 | 1 |  // edit users

3. **Tabel `model_has_roles`:**
| role_id | model_type | model_id |
|---------|-------------|-----------|
| 1 | App\Models\User | 1 |

**Hasil:** User dengan ID 1 memiliki role 'admin', yang memiliki permissions 'create users' dan 'edit users'.

---

### Contoh 2: User dengan Permission Langsung

```php
// 1. Berikan permission langsung ke user
$user = User::find(1);
$user->givePermissionTo('delete users');
```

**Yang Terjadi di Database:**

1. **Tabel `model_has_permissions`:**
| permission_id | model_type | model_id |
|--------------|-------------|-----------|
| 3 | App\Models\User | 1 |  // delete users

**Hasil:** User dengan ID 1 memiliki permission 'delete users' secara langsung, selain permissions dari role-nya.

---

## 🎯 Menggunakan Polymorphic Relations

Tabel `model_has_permissions` dan `model_has_roles` menggunakan **polymorphic relations**:

### Apa itu Polymorphic Relations?

Polymorphic relations memungkinkan satu tabel untuk berhubungan dengan berbagai tipe model.

**Contoh:**
- `model_has_permissions` bisa menghubungkan permissions ke:
  - `App\Models\User`
  - `App\Models\Admin`
  - `App\Models\Customer`
  - Dan model lainnya!

**Tanpa Polymorphic Relations:**
- Anda perlu tabel terpisah untuk setiap model:
  - `user_has_permissions`
  - `admin_has_permissions`
  - `customer_has_permissions`

**Dengan Polymorphic Relations:**
- Hanya butuh satu tabel: `model_has_permissions`
- Kolom `model_type` menentukan tipe model
- Kolom `model_id` menentukan ID model

---

## 📝 Summary

### Apa yang Dilakukan Spatie Laravel Permission?

1. ✅ Membuat 5 tabel melalui migration
2. ✅ Menggunakan pivot tables untuk many-to-many relationships
3. ✅ Menggunakan polymorphic relations untuk fleksibilitas
4. ✅ Menyediakan methods untuk mengelola roles dan permissions

### Apa yang Saya Lakukan?

1. ❌ **TIDAK** membuat tabel-tabel ini secara manual
2. ✅ Menggunakan migration dari Spatie Laravel Permission
3. ✅ Menggunakan methods dari Spatie:
   - `assignRole($role)` - Mengisi `model_has_roles`
   - `givePermissionTo($permission)` - Mengisi `model_has_permissions`
   - `syncRoles($roles)` - Sync `model_has_roles`
   - `syncPermissions($permissions)` - Sync `role_has_permissions`

---

## 📚 Referensi

- [Spatie Laravel Permission Documentation](https://spatie.be/docs/laravel-permission/v6)
- [Laravel Polymorphic Relations](https://laravel.com/docs/11.x/eloquent-relationships#polymorphic-relations)

---

## 🎉 Kesimpulan

**Tabel `model_has_permissions` dan `model_has_roles` adalah pivot tables yang dibuat secara otomatis oleh Spatie Laravel Permission package.**

**Fungsinya:**
- `model_has_permissions` - Menghubungkan model (User) dengan permissions secara langsung
- `model_has_roles` - Menghubungkan model (User) dengan roles

**Saya TIDAK membuat tabel-tabel ini secara manual.** Mereka dibuat oleh migration dari Spatie Laravel Permission package. ✅
