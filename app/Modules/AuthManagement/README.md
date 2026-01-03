# Auth Management Module

Modul terorganisir untuk manajemen User, Role, dan Permission di Laravel 11.

## 📁 Struktur Folder

```
app/Modules/AuthManagement/
├── Controllers/
│   ├── UserController.php
│   ├── RoleController.php
│   └── PermissionController.php
├── Requests/
│   ├── StoreUserRequest.php
│   ├── UpdateUserRequest.php
│   ├── StoreRoleRequest.php
│   ├── UpdateRoleRequest.php
│   ├── StorePermissionRequest.php
│   └── UpdatePermissionRequest.php
└── Models/
    ├── User.php
    ├── Role.php
    └── Permission.php

resources/views/authmanagement/
├── user/
│   ├── index.blade.php
│   └── create.blade.php
├── role/
│   ├── index.blade.php
│   └── create.blade.php
├── permission/
│   ├── index.blade.php
│   └── create.blade.php
└── layouts/
    └── app.blade.php

tests/Feature/AuthManagement/
├── UserTest.php
├── RoleTest.php
└── PermissionTest.php
```

## 🚀 Fitur

### User Management
- ✅ CRUD User lengkap (Create, Read, Update, Delete)
- ✅ Pencarian User berdasarkan nama dan email
- ✅ Assign Role ke User
- ✅ Pagination 10 data per halaman
- ✅ Proteksi: User tidak bisa menghapus akun sendiri
- ✅ Route Model Binding

### Role Management
- ✅ CRUD Role lengkap
- ✅ Assign Permission ke Role
- ✅ Pencarian Role berdasarkan nama
- ✅ Pagination 10 data per halaman
- ✅ Proteksi: Super Admin role tidak bisa diedit/dihapus
- ✅ Route Model Binding

### Permission Management
- ✅ CRUD Permission lengkap
- ✅ Pencarian Permission berdasarkan nama
- ✅ Pagination 10 data per halaman
- ✅ Route Model Binding

## 🔐 Security & Authorization

### Middleware Protection
- Semua route dilindungi dengan `auth` middleware
- Hanya user dengan role `super-admin` yang bisa akses
- Permission-based access control di setiap action

### Permissions Required
- **User Management**: `create users`, `edit users`, `delete users`
- **Role Management**: `create roles`, `edit roles`, `delete roles`
- **Permission Management**: `create permissions`, `edit permissions`, `delete permissions`

## 📝 Coding Rules Applied

### ✅ Controller Structure
- Route Model Binding (`User $user` instead of `$id`)
- Use `compact()` untuk pass data ke view
- Redirect dengan flash message setelah action
- Pagination (`paginate(10)`) untuk list
- Eager loading untuk relationships (`with('roles')`)

### ✅ FormRequest Validation
- Semua validation di FormRequest (bukan di controller)
- Custom messages dalam bahasa Indonesia
- Authorization check di `authorize()` method
- Handle unique validation untuk update

### ✅ Model Best Practices
- `$fillable` untuk mass assignment
- `$hidden` untuk sensitive data
- `$casts` untuk type casting
- Scopes untuk reusable queries (`scopeSearch`, `scopeActive`)

### ✅ Routes
- Resource routes (`Route::resource()`)
- Named routes (`->name('auth.users.index')`)
- Grouped dengan middleware
- Prefix untuk organisasi (`/auth`)

## 🧪 Testing

### Test Coverage
Semua test menggunakan **Pest PHP** dengan coverage:

#### UserTest.php
- ✅ Happy Path (6 tests)
- ✅ Validation Errors (7 tests)
- ✅ Authorization Errors (7 tests)
- ✅ Unauthenticated Access (6 tests)

#### RoleTest.php
- ✅ Happy Path (6 tests)
- ✅ Validation Errors (7 tests)
- ✅ Authorization Errors (6 tests)
- ✅ Super Admin Protection (3 tests)
- ✅ Unauthenticated Access (6 tests)

#### PermissionTest.php
- ✅ Happy Path (6 tests)
- ✅ Validation Errors (4 tests)
- ✅ Authorization Errors (6 tests)
- ✅ Unauthenticated Access (6 tests)

### Running Tests

```bash
# Run all AuthManagement tests
php artisan test --filter=AuthManagement

# Run specific test file
php artisan test tests/Feature/AuthManagement/UserTest.php
php artisan test tests/Feature/AuthManagement/RoleTest.php
php artisan test tests/Feature/AuthManagement/PermissionTest.php

# Run specific test suite
php artisan test --filter="User Management - Happy Path"
php artisan test --filter="Role Management - Authorization Errors"
```

## 🛠️ Installation & Setup

### 1. Install Dependencies
```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

### 2. Seed Roles & Permissions
```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

### 3. Update Routes
Routes sudah ter-update di `routes/web.php` dengan prefix `/auth`

### 4. Update Views
Copy atau update view files ke `resources/views/authmanagement/`

## 📡 API Routes

### User Routes
```
GET    /auth/users              - List users (with search)
GET    /auth/users/create       - Create user form
POST   /auth/users              - Store new user
GET    /auth/users/{user}       - Show user detail
GET    /auth/users/{user}/edit  - Edit user form
PUT    /auth/users/{user}       - Update user
DELETE /auth/users/{user}       - Delete user
```

### Role Routes
```
GET    /auth/roles              - List roles (with search)
GET    /auth/roles/create       - Create role form
POST   /auth/roles              - Store new role
GET    /auth/roles/{role}       - Show role detail
GET    /auth/roles/{role}/edit  - Edit role form
PUT    /auth/roles/{role}       - Update role
DELETE /auth/roles/{role}       - Delete role
```

### Permission Routes
```
GET    /auth/permissions              - List permissions (with search)
GET    /auth/permissions/create       - Create permission form
POST   /auth/permissions              - Store new permission
GET    /auth/permissions/{permission}       - Show permission detail
GET    /auth/permissions/{permission}/edit  - Edit permission form
PUT    /auth/permissions/{permission}       - Update permission
DELETE /auth/permissions/{permission}       - Delete permission
```

## 🔍 Search Functionality

### User Search
Search berdasarkan `name` atau `email`:
```
GET /auth/users?search=john
```

### Role Search
Search berdasarkan `name`:
```
GET /auth/roles?search=admin
```

### Permission Search
Search berdasarkan `name`:
```
GET /auth/permissions?search=create
```

## 📊 Pagination

Semua list menggunakan pagination 10 data per halaman dengan query string preservation:

```php
$users = User::with('roles')
    ->latest()
    ->paginate(10)
    ->withQueryString();
```

## 🎨 Flash Messages

### Success Messages
- User created: "User berhasil ditambahkan"
- User updated: "User berhasil diperbarui"
- User deleted: "User berhasil dihapus"
- Role created: "Role berhasil dibuat"
- Role updated: "Role berhasil diperbarui"
- Role deleted: "Role berhasil dihapus"
- Permission created: "Permission berhasil dibuat"
- Permission updated: "Permission berhasil diperbarui"
- Permission deleted: "Permission berhasil dihapus"

### Error Messages
- Delete own account: "Anda tidak bisa menghapus akun sendiri!"
- Edit super-admin: "Role Super Admin tidak bisa diedit!"
- Update super-admin: "Restricted!"
- Delete super-admin: "Role Super Admin tidak boleh dihapus!"

## 🔄 Migration from Old Code

### Old Structure
```
app/Http/Controllers/UserController.php
app/Http/Controllers/RoleController.php
app/Http/Controllers/PermissionController.php
app/Models/User.php
resources/views/user/
resources/views/role/
resources/views/permission/
```

### New Structure
```
app/Modules/AuthManagement/Controllers/
app/Modules/AuthManagement/Requests/
app/Modules/AuthManagement/Models/
resources/views/authmanagement/
tests/Feature/AuthManagement/
```

### Changes Made
1. ✅ Moved all files to `AuthManagement` module
2. ✅ Created FormRequest classes for validation
3. ✅ Added Route Model Binding
4. ✅ Improved code organization
5. ✅ Added comprehensive Pest PHP tests
6. ✅ Applied all coding rules from `additional/rules.md`
7. ✅ Updated routes with `/auth` prefix

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)
- [Pest PHP Testing](https://pestphp.com/docs)
- [Coding Rules](../../additional/rules.md)

## 🤝 Contributing

Pastikan untuk mengikuti coding rules yang ada di `additional/rules.md` sebelum membuat perubahan.

## 📝 License

This module is part of the Laravel project.
