# Laravel Best Practices - Hardcode PHP + Blade Version

## Optimized for Traditional MVC Pattern

---

## 🎯 PROJECT CONTEXT

**Tech Stack:**

- Backend: Laravel (PHP hardcode, no API)
- Frontend: Blade templates
- Assets: **DYNAMIC (Bootstrap 5 OR Tailwind CSS)** -> *Check files or ask user*
- Database: MySQL/PostgreSQL with Eloquent

**Pattern:** Traditional MVC (Model-View-Controller)

---

## 🚨 CRITICAL RULES

### Rule 1: Controller Structure (Keep It Simple)

**For CRUD operations:**

```php
class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')->paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(StoreUserRequest $request)
    {
        User::create($request->validated());

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dibuat');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diupdate');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus');
    }
}
```

**RULES:**

- ✅ Use Route Model Binding (`User $user` instead of `$id`)
- ✅ Use `compact()` to pass data to view
- ✅ Always redirect with flash message after action
- ✅ Use pagination (`paginate()`) instead of `get()` for list

**WHEN to use Service Class:**

- ❌ Simple CRUD → NO, langsung di Controller
- ✅ Complex business logic (e.g., generate invoice, send email, multiple model interactions)
- ✅ Reusable logic across multiple controllers

**Example when Service is needed:**

```php
// ✅ This needs Service
public function store(StoreOrderRequest $request)
{
    // Complex logic:
    // 1. Create order
    // 2. Reduce stock
    // 3. Send email
    // 4. Create invoice
    // 5. Log activity

    $order = $this->orderService->createOrder($request->validated());

    return redirect()->route('orders.show', $order)
        ->with('success', 'Order berhasil dibuat');
}
```

---

### Rule 2: Validation (FormRequest WAJIB)

**ALWAYS create FormRequest for validation:**

```php
// app/Http/Requests/StoreUserRequest.php
class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // atau cek permission di sini
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.unique' => 'Email sudah terdaftar',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
        ];
    }
}
```

**For Update (handle unique with ignore):**

```php
public function rules(): array
{
    return [
        'email' => 'required|email|unique:users,email,' . $this->user->id,
    ];
}
```

**FORBIDDEN:**

- ❌ Validation di Controller
- ❌ Manual validation with Validator::make()

---

### Rule 3: Model (Eloquent Best Practices)

**Basic Model Structure:**

```php
class User extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    // Scopes (for reusable queries)
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
    }

    // Accessors (for displaying data)
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    // Mutators (for setting data)
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }
}
```

**RULES:**

- ✅ Define `$fillable` (whitelist untuk mass assignment)
- ✅ Use `$hidden` untuk sensitive data
- ✅ Use `$casts` untuk type casting
- ✅ Create Scopes untuk reusable queries
- ✅ Accessors untuk format display
- ✅ Mutators untuk modify input

**FORBIDDEN:**

- ❌ Business logic di Model
- ❌ External API calls di Model
- ❌ Email sending di Model

---

### Rule 4: Blade Views (Structure & Best Practices)

**STEP 1: DETECT CSS FRAMEWORK**
Before generating views, check the project files:
- If `tailwind.config.js` exists OR `app.css` contains `@tailwind` -> **USE TAILWIND**.
- If `bootstrap` is in `package.json` OR CDN links in layout -> **USE BOOTSTRAP**.
- If unsure -> **ASK ME**.

**STEP 2: GENERATE BASED ON DETECTED FRAMEWORK**

**Scenario A: If BOOTSTRAP 5 (Standard)**
- Use standard classes: `card`, `table`, `btn btn-primary`, `form-control`.
- Use Grid system: `row`, `col-md-6`.

**Scenario B: If TAILWIND CSS (Modern)**
- Use Utility classes.
- **Form Input:** `class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"`
- **Button:** `class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"`
- **Table:** `class="min-w-full divide-y divide-gray-200"`

**Folder Structure:**

```
resources/views/
├── layouts/
│   ├── app.blade.php          # Main layout
│   ├── guest.blade.php        # Layout for login/register
│   └── partials/
│       ├── header.blade.php
│       ├── sidebar.blade.php
│       └── footer.blade.php
├── users/
│   ├── index.blade.php        # List
│   ├── create.blade.php       # Create form
│   ├── edit.blade.php         # Edit form
│   └── show.blade.php         # Detail
└── components/
    ├── alert.blade.php        # Reusable alert
    ├── modal.blade.php        # Reusable modal
    └── form/
        ├── input.blade.php
        └── select.blade.php
```

**Main Layout (layouts/app.blade.php):**

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Laravel App')</title>

    {{-- CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body>
    @include('layouts.partials.header')

    <div class="container mt-4">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Content --}}
        @yield('content')
    </div>

    @include('layouts.partials.footer')

    {{-- JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>

    @stack('scripts')
</body>
</html>
```

**Index View (users/index.blade.php):**

```blade
@extends('layouts.app')

@section('title', 'Daftar User')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Daftar User</h2>
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        Tambah User
    </a>
</div>

{{-- Search Form --}}
<form method="GET" class="mb-3">
    <div class="input-group">
        <input type="text" name="search" class="form-control"
               placeholder="Cari user..." value="{{ request('search') }}">
        <button class="btn btn-outline-secondary" type="submit">Cari</button>
    </div>
</form>

{{-- Table --}}
<div class="card">
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $users->firstItem() + $loop->index }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->role->name }}</td>
                        <td>
                            @if($user->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('users.edit', $user) }}"
                               class="btn btn-sm btn-warning">Edit</a>

                            <form action="{{ route('users.destroy', $user) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Yakin hapus user ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        <div class="d-flex justify-content-center">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
```

**Create/Edit Form (users/create.blade.php):**

```blade
@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')
<div class="card">
    <div class="card-header">
        <h4>Tambah User</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf

            {{-- Name --}}
            <div class="mb-3">
                <label for="name" class="form-label">Nama *</label>
                <input type="text"
                       class="form-control @error('name') is-invalid @enderror"
                       id="name"
                       name="name"
                       value="{{ old('name') }}"
                       required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Email --}}
            <div class="mb-3">
                <label for="email" class="form-label">Email *</label>
                <input type="email"
                       class="form-control @error('email') is-invalid @enderror"
                       id="email"
                       name="email"
                       value="{{ old('email') }}"
                       required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Password --}}
            <div class="mb-3">
                <label for="password" class="form-label">Password *</label>
                <input type="password"
                       class="form-control @error('password') is-invalid @enderror"
                       id="password"
                       name="password"
                       required>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Password Confirmation --}}
            <div class="mb-3">
                <label for="password_confirmation" class="form-label">
                    Konfirmasi Password *
                </label>
                <input type="password"
                       class="form-control"
                       id="password_confirmation"
                       name="password_confirmation"
                       required>
            </div>

            {{-- Role --}}
            <div class="mb-3">
                <label for="role_id" class="form-label">Role *</label>
                <select class="form-select @error('role_id') is-invalid @enderror"
                        id="role_id"
                        name="role_id"
                        required>
                    <option value="">Pilih Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}"
                                {{ old('role_id') == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
                @error('role_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Submit Buttons --}}
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
```

**BLADE RULES:**

- ✅ Use `@extends` untuk layout
- ✅ Use `@section` untuk content
- ✅ Use `@include` untuk partials (header, footer)
- ✅ Use `@error` directive untuk validation errors
- ✅ Use `old()` helper untuk repopulate form
- ✅ Use `{{ }}` untuk echo (auto-escaped)
- ✅ Use `{!! !!}` ONLY untuk HTML content (careful!)
- ✅ Use `@forelse` instead of `@foreach` (better empty handling)

**FORBIDDEN:**

- ❌ Inline CSS/JS di Blade (use external files or @stack)
- ❌ Database queries di Blade
- ❌ Complex logic di Blade (use Controller/Model)
- ❌ Raw PHP (`<?php ?>`) di Blade (use Blade directives)

---

### Rule 5: Routes (Clean & RESTful)

**web.php:**

```php
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Resource routes (automatic CRUD routes)
Route::resource('users', UserController::class);

// Custom routes (if needed)
Route::get('users/{user}/activate', [UserController::class, 'activate'])
    ->name('users.activate');

// Route with middleware
Route::middleware(['auth'])->group(function () {
    Route::resource('posts', PostController::class);
    Route::resource('categories', CategoryController::class);
});

// Route with prefix
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('dashboard', [AdminController::class, 'dashboard'])
        ->name('admin.dashboard');
});
```

**RULES:**

- ✅ Use `Route::resource()` untuk CRUD standard
- ✅ Always name your routes (->name())
- ✅ Group routes with middleware
- ✅ Use prefix untuk admin/api sections

---

### Rule 6: Database Queries (Eloquent Best Practices)

**ALWAYS use Eager Loading:**

```php
// ✅ GOOD
$users = User::with(['role', 'posts'])->get();

// ❌ BAD (N+1 Problem)
$users = User::all();
foreach($users as $user) {
    echo $user->role->name; // Query per iteration!
}
```

**Use Scopes:**

```php
// ✅ GOOD
$activeUsers = User::active()->get();
$searchResults = User::search($request->search)->get();

// ❌ BAD (repeat code everywhere)
$activeUsers = User::where('is_active', true)->get();
```

**Pagination:**

```php
// ✅ GOOD
$users = User::paginate(10);
$users = User::simplePaginate(10); // for simple prev/next

// ❌ BAD
$users = User::all(); // Load semua data!
```

**Search dengan LIKE:**

```php
$users = User::where('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->paginate(10);
```

---

### Rule 7: Flash Messages & Error Handling

**In Controller:**

```php
// Success
return redirect()->route('users.index')
    ->with('success', 'User berhasil dibuat');

// Error
return redirect()->back()
    ->with('error', 'Terjadi kesalahan')
    ->withInput();
```

**In Blade (layout):**

```blade
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
```

---

### Rule 8: Asset Management (Simple Approach)

**Folder Structure:**

```
public/
├── css/
│   └── app.css
├── js/
│   └── app.js
└── images/
    └── logo.png
```

**In Blade:**

```blade
{{-- CSS --}}
<link href="{{ asset('css/app.css') }}" rel="stylesheet">

{{-- JS --}}
<script src="{{ asset('js/app.js') }}"></script>

{{-- Image --}}
<img src="{{ asset('images/logo.png') }}" alt="Logo">
```

**Use CDN for frameworks:**

```blade
{{-- Bootstrap --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

{{-- Font Awesome --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
```

---

## 🎯 AI BEHAVIOR RULES FOR HARDCODE LARAVEL

### When I Say "Buat CRUD untuk {Model}"

**AI MUST generate in this order:**

1. **Migration:**

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('password');
    $table->foreignId('role_id')->constrained();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

2. **Model:**

```php
class User extends Model
{
    protected $fillable = ['name', 'email', 'password', 'role_id'];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
```

3. **FormRequests:**

- StoreUserRequest
- UpdateUserRequest

4. **Controller:**

- Full CRUD methods (index, create, store, edit, update, destroy)

5. **Routes:**

```php
Route::resource('users', UserController::class);
```

6. **Views:**

- index.blade.php (list dengan table)
- create.blade.php (form tambah)
- edit.blade.php (form edit)
- (Optional) show.blade.php (detail)

**AI MUST confirm before generating:**

```
"Saya akan generate:
✓ Migration: create_users_table
✓ Model: User
✓ FormRequest: StoreUserRequest, UpdateUserRequest
✓ Controller: UserController (7 methods)
✓ Route: Route::resource('users', UserController::class)
✓ Views: index, create, edit

Generate dengan Bootstrap 5 styling.
Lanjut? (yes/no)"
```

---

### When I Say "Tambahin fitur search/filter"

**AI MUST:**

1. Add search form di index.blade.php
2. Modify Controller index() method
3. Use Scope di Model (if reusable)

**Example:**

```php
// Controller
public function index(Request $request)
{
    $users = User::with('role')
        ->when($request->search, function($query) use ($request) {
            $query->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
        })
        ->when($request->role_id, function($query) use ($request) {
            $query->where('role_id', $request->role_id);
        })
        ->paginate(10);

    $roles = Role::all();

    return view('users.index', compact('users', 'roles'));
}
```

---

## 📋 CODE GENERATION CHECKLIST

**Before showing code, AI MUST verify:**

- [ ] Route Model Binding used? (e.g., `User $user`)
- [ ] FormRequest validation created?
- [ ] Eager loading for relationships?
- [ ] Flash messages after actions?
- [ ] `old()` helper in forms?
- [ ] `@error` directives for validation?
- [ ] Pagination used for lists?
- [ ] Bootstrap classes applied?
- [ ] Confirmation for delete actions?
- [ ] Blade layout extended properly?

---

## 🚫 ABSOLUTE PROHIBITIONS

**NEVER do these:**

1. ❌ **Create API endpoints** (unless I explicitly ask)

```php
   // ❌ DON'T
   return response()->json(['data' => $user]);

   // ✅ DO
   return view('users.show', compact('user'));
```

2. ❌ **Use Service class for simple CRUD**

   - Eloquent + Controller is enough

3. ❌ **Create SPA structure** (React/Vue components)

   - This is Blade hardcode, not SPA

4. ❌ **Use Vite/Mix** unless I ask

   - Simple asset linking is fine

5. ❌ **Create Repository pattern** for basic CRUD

   - Overkill for simple apps

6. ❌ **Put queries in Blade**

```blade
   {{-- ❌ DON'T --}}
   @foreach(App\Models\User::all() as $user)

   {{-- ✅ DO (pass from controller) --}}
   @foreach($users as $user)
```

---

## 💬 COMMUNICATION PROTOCOL

### Before Generating:

```
"Saya mendeteksi proyek ini menggunakan: **[TAILWIND / BOOTSTRAP]**.

"Saya akan membuat:
- Migration: create_{table}_table
- Model: {ModelName} dengan relasi {relationships}
- FormRequest: Store{Model}Request, Update{Model}Request
- Controller: {Model}Controller (CRUD lengkap)
- Route: Route::resource('{route}', {Model}Controller::class)
- Views: index, create, edit (Style: **[FRAMEWORK TERDETEKSI]**)

Lanjut?"
```

### After Generating:

```
"Code generated.

Next steps:
1. Run: php artisan migrate
2. Test create/edit/delete di browser
3. Butuh tambahan fitur? (search, filter, dll)"
```

---

## 🔥 GOLDEN RULES

1. **Keep It Simple** - No over-engineering
2. **Blade for Views** - No mixing with React/Vue
3. **Bootstrap/Tailwind OK** - Via CDN
4. **Pagination Always** - Never `->get()` for lists
5. **FormRequest Always** - Never validate in Controller
6. **Flash Messages Always** - User feedback is important

---

**Ready to build traditional Laravel apps! 🚀**