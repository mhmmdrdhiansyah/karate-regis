# Laravel & PHP Best Practices

> Panduan ini ditulis dengan bahasa high-level agar mudah dipahami oleh junior programmer maupun AI model. Gunakan file ini sebagai referensi saat membangun sistem baru dengan Laravel — mulai dari struktur project, komunikasi ke database, hingga deployment.

---

## 1. Versi & Setup — Gunakan Versi Stabil Terbaru

| Kategori | Rekomendasi |
|---|---|
| PHP | Gunakan versi **terbaru yang didukung Laravel** (minimal PHP 8.2+) |
| Laravel | Gunakan versi **LTS atau major terbaru** (Laravel 11, 12, 13) |
| Dependency | Selalu jalankan `composer update` secara berkala, cek breaking change |

**Aturan:**
- Pin versi PHP di semua environment — development, staging, production harus sama
- Gunakan `composer.lock` di version control — jangan di-`.gitignore`
- Jangan skip major version saat upgrade — ikuti migration guide resmi

---

## 2. Struktur Project — Ikuti Konvensi Laravel

### Struktur Folder Standar

```
app/
├── Http/
│   ├── Controllers/       # Controller — tipis, hanya handle request/response
│   ├── Middleware/         # Custom middleware
│   └── Requests/          # Form Request (validasi)
├── Models/                # Eloquent model
├── Services/              # Business logic (Service layer)
├── Repositories/          # Data access layer (opsional, untuk sistem besar)
├── Actions/               # Single-purpose class (alternatif service)
├── Enums/                 # PHP Enum (status, role, tipe)
├── Exceptions/            # Custom exception
├── Events/                # Event class
├── Listeners/             # Event listener
├── Jobs/                  # Queue job
├── Mail/                  # Mailable class
├── Notifications/         # Notification class
├── Observers/             # Model observer
├── Policies/              # Authorization policy
├── Traits/                # Reusable trait
└── Helpers/               # Helper functions (jika perlu)
config/                    # Konfigurasi aplikasi
database/
├── migrations/            # Migration file
├── seeders/               # Seeder
└── factories/             # Factory untuk testing
resources/
├── views/                 # Blade template
└── lang/                  # Localization
routes/
├── web.php                # Web routes
├── api.php                # API routes
└── console.php            # Artisan command routes
tests/
├── Feature/               # Feature/integration test
└── Unit/                  # Unit test
```

### Prinsip Arsitektur

```
Request → Controller → Service → Model/Repository → Database
                ↓
           Form Request (validasi)
                ↓
           API Resource (response)
```

**Aturan:**
- **Controller harus tipis** — hanya terima request, panggil service, return response
- **Business logic di Service** — bukan di controller, bukan di model
- **Model hanya untuk relasi, scope, dan accessor/mutator** — bukan tempat logic bisnis
- Jangan taruh query kompleks di controller — pindah ke service atau repository

---

## 3. Naming Convention — Konsisten Itu Wajib

| Kategori | Convention | Contoh |
|---|---|---|
| Model | Singular, PascalCase | `User`, `BlogPost`, `OrderItem` |
| Controller | Singular + Controller | `UserController`, `OrderController` |
| Migration | snake_case, deskriptif | `create_users_table`, `add_phone_to_users_table` |
| Table | Plural, snake_case | `users`, `blog_posts`, `order_items` |
| Column | snake_case | `first_name`, `created_at`, `is_active` |
| Foreign Key | singular_table + `_id` | `user_id`, `order_id` |
| Pivot Table | Singular, alphabetical, snake_case | `post_tag`, `order_product` |
| Form Request | PascalCase + Request | `StoreUserRequest`, `UpdateOrderRequest` |
| API Resource | PascalCase + Resource | `UserResource`, `OrderResource` |
| Service | PascalCase + Service | `UserService`, `PaymentService` |
| Event | PascalCase (past tense) | `OrderPlaced`, `UserRegistered` |
| Listener | PascalCase (verb) | `SendOrderNotification` |
| Job | PascalCase (verb) | `ProcessPayment`, `GenerateReport` |
| Policy | PascalCase + Policy | `UserPolicy`, `PostPolicy` |
| Enum | PascalCase | `OrderStatus`, `UserRole` |
| Trait | PascalCase + adjective/able | `Searchable`, `HasSlug` |
| Scope (Eloquent) | camelCase prefixed `scope` | `scopeActive()`, `scopeByStatus()` |
| Route name | kebab-case, dot-separated | `users.index`, `orders.store` |

---

## 4. Eloquent Model — Konvensi & Best Practices

### Definisi Model yang Bersih

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    // ✅ Selalu definisikan fillable — jangan pakai guarded = []
    protected $fillable = [
        'user_id',
        'status',
        'total_amount',
        'notes',
    ];

    // ✅ Cast tipe data yang benar
    protected function casts(): array
    {
        return [
            'total_amount'  => 'decimal:2',
            'status'        => OrderStatus::class,  // Enum cast
            'metadata'      => 'array',
            'confirmed_at'  => 'datetime',
            'is_paid'       => 'boolean',
        ];
    }

    // ===== Relationships =====

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ===== Scopes =====

    public function scopeActive($query)
    {
        return $query->where('status', OrderStatus::Active);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ===== Accessors & Mutators =====

    protected function formattedTotal(): Attribute
    {
        return Attribute::make(
            get: fn () => 'Rp ' . number_format($this->total_amount, 0, ',', '.'),
        );
    }
}
```

### Urutan Penulisan di Model

Ikuti urutan ini agar konsisten di semua model:

1. `use` traits
2. Constants
3. `$fillable` / `$guarded`
4. `$casts` / `casts()`
5. `$hidden` / `$appends`
6. Relationships
7. Scopes
8. Accessors & Mutators
9. Custom methods

### Aturan Model

- ✅ Selalu gunakan `$fillable` — daftar kolom yang boleh mass-assign
- ❌ Jangan gunakan `$guarded = []` — ini membuka semua kolom, rawan mass-assignment attack
- ✅ Selalu definisikan return type di relationship (`BelongsTo`, `HasMany`, dll)
- ✅ Gunakan Enum cast untuk kolom status/tipe
- ✅ Gunakan `SoftDeletes` untuk data yang tidak boleh benar-benar dihapus

---

## 5. Migration — Aturan Schema Database

### Contoh Migration yang Baik

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            // ✅ Primary key
            $table->id();

            // ✅ Foreign key dengan constraint
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // ✅ Kolom data
            $table->string('order_number', 50)->unique();
            $table->string('status', 30)->default('pending');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            // ✅ Timestamps & soft delete
            $table->timestamps();
            $table->softDeletes();

            // ✅ Index untuk query yang sering dipakai
            $table->index('status');
            $table->index('created_at');
            $table->index(['user_id', 'status']);  // Composite index
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
```

### Aturan Migration

| Aturan | Penjelasan |
|---|---|
| Satu migration = satu perubahan | Jangan campur `create` dan `alter` di migration yang sama |
| Selalu isi `down()` | Agar bisa rollback — kecuali production data migration |
| Gunakan `foreignId()->constrained()` | Buat foreign key dengan convention otomatis |
| Index kolom yang sering di-`WHERE` | Tambahkan `$table->index()` di migration, bukan belakangan |
| Jangan ubah migration yang sudah di-run | Buat migration baru untuk perubahan |
| String length | Selalu definisikan max length: `string('name', 100)` |
| Decimal | Definisikan precision: `decimal('amount', 12, 2)` |

**Perintah penting:**

```bash
# Jalankan migration
php artisan migrate

# Rollback 1 step
php artisan migrate:rollback

# Fresh migration (drop semua tabel, jalankan ulang) — HANYA development
php artisan migrate:fresh --seed

# Cek status migration
php artisan migrate:status
```

---

## 6. Query Database — Hindari N+1 Problem

### N+1 Problem — Masalah Paling Umum

```php
// ❌ N+1 — ini menjalankan 1 + N query (jika 100 order = 101 query)
$orders = Order::all();
foreach ($orders as $order) {
    echo $order->user->name;        // Setiap iterasi = 1 query tambahan
}

// ✅ Eager loading — hanya 2 query total
$orders = Order::with('user')->get();
foreach ($orders as $order) {
    echo $order->user->name;        // Sudah ter-load, tidak ada query tambahan
}

// ✅ Nested eager loading
$orders = Order::with(['user', 'items.product'])->get();

// ✅ Eager loading dengan kondisi
$orders = Order::with(['items' => function ($query) {
    $query->where('quantity', '>', 0)->orderBy('created_at', 'desc');
}])->get();
```

### Deteksi N+1 Otomatis

```php
// ✅ Tambahkan di AppServiceProvider::boot()
// Ini akan throw exception jika ada lazy loading (N+1)
Model::preventLazyLoading(! app()->isProduction());

// ✅ Atau log saja tanpa throw exception
Model::preventLazyLoading();
Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
    logger()->warning("N+1 detected: {$model}::{$relation}");
});
```

**Aturan:** Aktifkan `preventLazyLoading` di development. Ini adalah defence terbaik terhadap N+1.

---

## 7. Query Best Practices — Efisien & Aman

### Gunakan Query Builder / Eloquent, Bukan Raw SQL

```php
// ❌ Raw SQL — rawan SQL injection, susah maintain
DB::select("SELECT * FROM users WHERE email = '$email'");

// ✅ Eloquent
User::where('email', $email)->first();

// ✅ Query Builder (untuk query kompleks yang tidak perlu model)
DB::table('users')->where('email', $email)->first();
```

### Query yang Efisien

```php
// ❌ Ambil semua kolom padahal hanya butuh 2 kolom
$users = User::all();

// ✅ Select hanya kolom yang dibutuhkan
$users = User::select(['id', 'name', 'email'])->get();

// ❌ Ambil semua data lalu filter di PHP
$activeUsers = User::all()->filter(fn ($u) => $u->is_active);

// ✅ Filter di database (WHERE), bukan di PHP
$activeUsers = User::where('is_active', true)->get();

// ❌ Count dengan mengambil semua data
$count = User::all()->count();

// ✅ Count langsung di database
$count = User::count();
$count = User::where('is_active', true)->count();

// ✅ Gunakan chunk untuk data besar (hemat memory)
User::where('is_active', true)->chunk(500, function ($users) {
    foreach ($users as $user) {
        // proses per batch 500
    }
});

// ✅ Atau lazy() untuk streaming (lebih simple)
foreach (User::where('is_active', true)->lazy() as $user) {
    // proses satu per satu tanpa load semua ke memory
}
```

### Kapan Pakai Apa?

| Method | Kegunaan |
|---|---|
| `->get()` | Ambil collection (multiple rows) |
| `->first()` | Ambil 1 row pertama (atau null) |
| `->firstOrFail()` | Ambil 1 row, throw 404 jika tidak ada |
| `->find($id)` | Ambil by primary key |
| `->findOrFail($id)` | Ambil by primary key, throw 404 jika tidak ada |
| `->pluck('name', 'id')` | Ambil 1–2 kolom saja, return flat collection |
| `->exists()` | Cek apakah ada (return boolean, tanpa load data) |
| `->count()` | Hitung jumlah (query COUNT, tanpa load data) |
| `->paginate(15)` | Ambil dengan pagination |
| `->chunk(500, fn)` | Proses data besar per batch |
| `->lazy()` | Streaming tanpa load semua ke memory |

---

## 8. Database Transaction — Jaga Konsistensi Data

### Kapan Pakai Transaction?

Gunakan transaction jika ada **lebih dari 1 operasi database yang harus sukses bersama-sama**.

```php
// ✅ Transaction — jika salah satu gagal, semua dibatalkan
use Illuminate\Support\Facades\DB;

DB::transaction(function () use ($request) {
    $order = Order::create([
        'user_id' => auth()->id(),
        'total_amount' => $request->total,
    ]);

    foreach ($request->items as $item) {
        $order->items()->create($item);
    }

    // Update stok produk
    foreach ($request->items as $item) {
        Product::where('id', $item['product_id'])
            ->decrement('stock', $item['quantity']);
    }
});

// ✅ Transaction dengan return value
$order = DB::transaction(function () use ($data) {
    $order = Order::create($data);
    $order->items()->createMany($data['items']);
    return $order;
});

// ✅ Manual transaction (untuk kontrol lebih)
DB::beginTransaction();
try {
    // operasi 1
    // operasi 2
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    throw $e;
}
```

**Aturan:**
- ✅ Gunakan `DB::transaction()` (closure) — lebih aman, otomatis rollback jika exception
- ⚠️ Jangan dispatch job/event di dalam transaction — job mungkin diproses sebelum commit
- ✅ Gunakan `afterCommit()` di queue jika dispatch dari dalam transaction

---

## 9. Validasi — Gunakan Form Request

### Jangan Validasi di Controller

```php
// ❌ Validasi di controller — controller jadi gemuk
public function store(Request $request)
{
    $request->validate([
        'name' => 'required|max:255',
        'email' => 'required|email|unique:users',
    ]);
    // ...
}

// ✅ Gunakan Form Request — validasi terpisah, reusable
public function store(StoreUserRequest $request)
{
    $validated = $request->validated();
    // ...
}
```

### Contoh Form Request

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Atur di sini atau di Policy
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role'     => ['required', 'in:admin,user,viewer'],
            'phone'    => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'email.unique'  => 'Email sudah terdaftar.',
        ];
    }
}
```

**Aturan:**
- ✅ Selalu pakai Form Request untuk create dan update
- ✅ Gunakan array syntax `['required', 'string']` bukan pipe `'required|string'` — lebih readable
- ✅ Gunakan `$request->validated()` — hanya ambil data yang sudah tervalidasi
- ❌ Jangan pakai `$request->all()` untuk mass-assign — bisa ada field berbahaya

---

## 10. API Resource — Format Response yang Konsisten

### Jangan Return Model Langsung

```php
// ❌ Return model langsung — expose semua kolom termasuk yang sensitif
return response()->json($user);

// ✅ Gunakan API Resource — kontrol penuh atas response
return new UserResource($user);
```

### Contoh API Resource

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'role'       => $this->role,
            'created_at' => $this->created_at->toISOString(),

            // ✅ Conditional relationship — hanya include jika di-load
            'orders'     => OrderResource::collection($this->whenLoaded('orders')),
            'profile'    => new ProfileResource($this->whenLoaded('profile')),

            // ✅ Conditional field
            'phone'      => $this->when($request->user()?->isAdmin(), $this->phone),
        ];
    }
}
```

### Standard API Response Format

```php
// ✅ Gunakan format response yang konsisten di seluruh API

// Success (single)
return UserResource::make($user);

// Success (collection + pagination)
return UserResource::collection(
    User::paginate(15)
);

// Success (custom message)
return response()->json([
    'message' => 'User berhasil dibuat.',
    'data'    => new UserResource($user),
], 201);

// Error
return response()->json([
    'message' => 'Data tidak ditemukan.',
    'errors'  => [],
], 404);
```

---

## 11. Service Layer — Pisahkan Business Logic

### Kenapa Butuh Service?

Controller harus **tipis**. Semua business logic ada di Service.

```php
// ❌ Controller gemuk — logic campur di controller
class OrderController extends Controller
{
    public function store(StoreOrderRequest $request)
    {
        // validasi stok, hitung total, buat order, kurangi stok,
        // kirim notifikasi, dll — semua di sini = susah test & maintain
    }
}

// ✅ Controller tipis + Service
class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function store(StoreOrderRequest $request)
    {
        $order = $this->orderService->createOrder($request->validated());

        return new OrderResource($order);
    }
}
```

### Contoh Service

```php
<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            // 1. Validasi stok
            $this->validateStock($data['items']);

            // 2. Hitung total
            $total = $this->calculateTotal($data['items']);

            // 3. Buat order
            $order = Order::create([
                'user_id'      => auth()->id(),
                'total_amount' => $total,
                'status'       => 'pending',
            ]);

            // 4. Buat items
            $order->items()->createMany($data['items']);

            // 5. Kurangi stok
            $this->decrementStock($data['items']);

            return $order->load('items');
        });
    }

    private function validateStock(array $items): void
    {
        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);
            if ($product->stock < $item['quantity']) {
                throw new \DomainException("Stok {$product->name} tidak mencukupi.");
            }
        }
    }

    private function calculateTotal(array $items): float
    {
        return collect($items)->sum(fn ($item) =>
            Product::find($item['product_id'])->price * $item['quantity']
        );
    }

    private function decrementStock(array $items): void
    {
        foreach ($items as $item) {
            Product::where('id', $item['product_id'])
                ->decrement('stock', $item['quantity']);
        }
    }
}
```

---

## 12. Controller — Tipis & Fokus

### RESTful Controller Pattern

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    // GET /users
    public function index()
    {
        $users = User::with('profile')
            ->latest()
            ->paginate(15);

        return UserResource::collection($users);
    }

    // GET /users/{user}
    public function show(User $user)
    {
        return new UserResource($user->load('profile', 'orders'));
    }

    // POST /users
    public function store(StoreUserRequest $request)
    {
        $user = $this->userService->createUser($request->validated());

        return new UserResource($user);
    }

    // PUT /users/{user}
    public function update(UpdateUserRequest $request, User $user)
    {
        $user = $this->userService->updateUser($user, $request->validated());

        return new UserResource($user);
    }

    // DELETE /users/{user}
    public function destroy(User $user)
    {
        $this->userService->deleteUser($user);

        return response()->json(['message' => 'User deleted.'], 200);
    }
}
```

**Aturan Controller:**
- ✅ Maksimal 5–7 method (index, show, store, update, destroy + custom jika perlu)
- ✅ Jika controller punya terlalu banyak method → pecah jadi controller baru
- ✅ Gunakan Route Model Binding (`User $user` di parameter)
- ✅ Inject Service via constructor

---

## 13. Routing — Rapi & Terstruktur

```php
// ✅ routes/api.php (API v1)
Route::prefix('v1')->group(function () {

    // Public routes
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {

        // User
        Route::apiResource('users', UserController::class);

        // Order — nested resource
        Route::apiResource('users.orders', OrderController::class)
            ->shallow();

        // Custom route
        Route::post('/orders/{order}/confirm', [OrderController::class, 'confirm'])
            ->name('orders.confirm');
    });
});
```

**Aturan Routing:**
- ✅ Gunakan `apiResource()` untuk CRUD API — otomatis generate 5 route
- ✅ Prefix dengan versi API (`/v1/`)
- ✅ Group middleware yang sama
- ✅ Gunakan `Route::name()` untuk semua custom route
- ❌ Jangan taruh logic di route file — route hanya untuk deklarasi

---

## 14. Enum — Gunakan PHP Enum untuk Status & Tipe

```php
<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending   = 'pending';
    case Confirmed = 'confirmed';
    case Shipped   = 'shipped';
    case Delivered  = 'delivered';
    case Cancelled = 'cancelled';

    // ✅ Label untuk UI
    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'Menunggu',
            self::Confirmed => 'Dikonfirmasi',
            self::Shipped   => 'Dikirim',
            self::Delivered  => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    // ✅ Warna untuk badge UI
    public function color(): string
    {
        return match ($this) {
            self::Pending   => 'yellow',
            self::Confirmed => 'blue',
            self::Shipped   => 'indigo',
            self::Delivered  => 'green',
            self::Cancelled => 'red',
        };
    }
}
```

**Penggunaan di Model & Query:**

```php
// Di Model (cast otomatis)
protected function casts(): array
{
    return [
        'status' => OrderStatus::class,
    ];
}

// Di query
Order::where('status', OrderStatus::Pending)->get();

// Di code
if ($order->status === OrderStatus::Delivered) {
    // ...
}

// Di validasi
'status' => ['required', new Illuminate\Validation\Rules\Enum(OrderStatus::class)],
```

---

## 15. Error Handling — Konsisten & Informatif

### Custom Exception

```php
<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    public function __construct(
        public readonly string $productName,
        public readonly int $requested,
        public readonly int $available,
    ) {
        parent::__construct("Stok {$productName} tidak mencukupi. Diminta: {$requested}, tersedia: {$available}.");
    }

    // ✅ Laravel otomatis panggil method ini untuk render response
    public function render($request)
    {
        return response()->json([
            'message'  => $this->getMessage(),
            'errors' => [
                'stock' => [
                    "Stok {$this->productName} hanya tersisa {$this->available}."
                ],
            ],
        ], 422);
    }
}
```

### Global Exception Handler

```php
// ✅ Di bootstrap/app.php (Laravel 11+)
->withExceptions(function (Exceptions $exceptions) {
    // Render semua exception sebagai JSON untuk API
    $exceptions->shouldRenderJsonWhen(function ($request) {
        return $request->expectsJson() || $request->is('api/*');
    });

    // Log tapi jangan report exception tertentu
    $exceptions->dontReport([
        InsufficientStockException::class,
    ]);
})
```

**Aturan:**
- ✅ Buat custom exception untuk error domain-specific
- ✅ Gunakan HTTP status code yang tepat (400, 401, 403, 404, 422, 500)
- ✅ Return message yang jelas dan konsisten
- ❌ Jangan `try-catch` di mana-mana — biarkan Laravel handle
- ❌ Jangan expose stack trace di production

---

## 16. Caching — Percepat Aplikasi

```php
use Illuminate\Support\Facades\Cache;

// ✅ Cache sederhana — simpan 1 jam
$users = Cache::remember('users:active', 3600, function () {
    return User::where('is_active', true)->get();
});

// ✅ Cache forever (sampai di-clear manual)
$settings = Cache::rememberForever('app:settings', function () {
    return Setting::all()->pluck('value', 'key');
});

// ✅ Clear cache saat data berubah
Cache::forget('users:active');

// ✅ Cache dengan tag (hanya Redis/Memcached)
Cache::tags(['users'])->remember('users:active', 3600, fn () => /* ... */);
Cache::tags(['users'])->flush(); // clear semua cache bertag 'users'
```

### Kapan Pakai Cache?

| Data | Cache? | TTL |
|---|---|---|
| Config / settings yang jarang berubah | ✅ Ya | Forever, clear saat update |
| List data untuk dropdown | ✅ Ya | 1–24 jam |
| Data user yang sering diakses | ⚠️ Hati-hati | 5–15 menit |
| Data real-time (stok, saldo) | ❌ Jangan | — |
| Response API publik (read-heavy) | ✅ Ya | 1–5 menit |

**Aturan:**
- ✅ Gunakan Redis untuk cache — lebih cepat dari file/database
- ✅ Selalu set TTL (time to live) — jangan cache selamanya tanpa strategi invalidation
- ✅ Clear cache saat data terkait berubah (di observer atau service)
- ❌ Jangan cache data yang sering berubah atau butuh real-time

---

## 17. Queue & Jobs — Jangan Proses Berat di Request

### Apa yang Harus di-Queue?

| Task | Queue? |
|---|---|
| Kirim email / notifikasi | ✅ Ya |
| Generate PDF / export Excel | ✅ Ya |
| Resize / upload gambar | ✅ Ya |
| Panggil API eksternal | ✅ Ya |
| Simple CRUD | ❌ Tidak perlu |
| Kalkulasi ringan | ❌ Tidak perlu |

### Contoh Job

```php
<?php

namespace App\Jobs;

use App\Models\Order;
use App\Mail\OrderConfirmation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // ✅ Retry config
    public int $tries = 3;
    public int $backoff = 60; // detik antar retry

    public function __construct(
        public readonly Order $order
    ) {}

    public function handle(): void
    {
        Mail::to($this->order->user->email)
            ->send(new OrderConfirmation($this->order));
    }

    // ✅ Handle jika semua retry gagal
    public function failed(\Throwable $exception): void
    {
        logger()->error("Failed to send order confirmation", [
            'order_id'  => $this->order->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}
```

**Dispatch job:**

```php
// ✅ Dispatch ke queue
SendOrderConfirmation::dispatch($order);

// ✅ Dispatch setelah response dikirim ke user (tanpa queue driver)
SendOrderConfirmation::dispatchAfterResponse($order);

// ✅ Dispatch setelah DB commit (penting jika di dalam transaction)
SendOrderConfirmation::dispatch($order)->afterCommit();
```

---

## 18. Middleware — Guard & Filter Request

```php
// ✅ Custom middleware sederhana
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()?->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}

// ✅ Register di bootstrap/app.php (Laravel 11+)
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin' => EnsureIsAdmin::class,
    ]);
})

// ✅ Gunakan di route
Route::middleware('admin')->group(function () {
    Route::apiResource('users', AdminUserController::class);
});
```

---

## 19. Observer — React ke Perubahan Model

```php
<?php

namespace App\Observers;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;

class OrderObserver
{
    // ✅ Otomatis generate order number sebelum create
    public function creating(Order $order): void
    {
        $order->order_number = 'ORD-' . now()->format('Ymd') . '-' . strtoupper(uniqid());
    }

    // ✅ Clear cache setelah data berubah
    public function saved(Order $order): void
    {
        Cache::forget("user:{$order->user_id}:orders");
    }

    // ✅ Log saat order dihapus
    public function deleted(Order $order): void
    {
        logger()->info("Order deleted", ['order_id' => $order->id]);
    }
}
```

**Register observer:**

```php
// Di Model
class Order extends Model
{
    protected static function booted(): void
    {
        static::observe(OrderObserver::class);
    }
}

// Atau di AppServiceProvider
Order::observe(OrderObserver::class);
```

---

## 20. Security — Jangan Abaikan

### Checklist Security

| Aspek | Aturan |
|---|---|
| Mass Assignment | ✅ Gunakan `$fillable` di setiap model |
| SQL Injection | ✅ Gunakan Eloquent/Query Builder, bukan raw query |
| XSS | ✅ Gunakan `{{ }}` di Blade (auto escape), bukan `{!! !!}` |
| CSRF | ✅ Laravel otomatis handle — jangan disable |
| Authentication | ✅ Gunakan Laravel Sanctum (SPA/API) atau Passport (OAuth) |
| Authorization | ✅ Gunakan Policy untuk access control |
| Password | ✅ Gunakan `Hash::make()` — jangan md5/sha1 |
| Sensitive data | ✅ Simpan di `.env`, jangan hardcode di code |
| Rate limiting | ✅ Terapkan di route API |
| CORS | ✅ Konfigurasi di `config/cors.php` — jangan allow all di production |
| File upload | ✅ Validasi mime type dan size, simpan di storage (bukan public) |

### Rate Limiting

```php
// ✅ Di bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->throttleWithRedis(); // Gunakan Redis untuk throttle
})

// ✅ Custom rate limiter di AppServiceProvider
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->input('email') . $request->ip());
});
```

---

## 21. Logging — Jangan Pakai `dd()` di Production

```php
// ✅ Gunakan Log facade
use Illuminate\Support\Facades\Log;

Log::info('Order created', ['order_id' => $order->id, 'user_id' => $user->id]);
Log::warning('Stok hampir habis', ['product_id' => $product->id, 'stock' => $product->stock]);
Log::error('Payment failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);

// ✅ Structured logging — selalu sertakan context
Log::channel('orders')->info('Order shipped', [
    'order_id'  => $order->id,
    'tracking'  => $tracking_number,
    'carrier'   => $carrier,
]);
```

**Aturan:**
- ❌ Jangan pakai `dd()`, `dump()`, atau `var_dump()` di production
- ❌ Jangan log data sensitif (password, token, credit card)
- ✅ Gunakan log level yang tepat: `debug`, `info`, `warning`, `error`, `critical`
- ✅ Gunakan channel terpisah untuk domain berbeda jika perlu

---

## 22. Environment & Config — Pisahkan dari Code

```bash
# ✅ .env — konfigurasi per-environment
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:xxxxxxxxxxxxx

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=myapp
DB_USERNAME=app_user
DB_PASSWORD=strong_password

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
```

**Aturan:**
- ✅ `.env` **TIDAK** boleh masuk version control — sudah di `.gitignore` by default
- ✅ Buat `.env.example` sebagai template — **TANPA** value sensitif
- ✅ Akses config lewat `config('app.name')`, bukan `env('APP_NAME')` langsung
- ✅ Cache config di production: `php artisan config:cache`
- ❌ Jangan panggil `env()` di luar file config — tidak berfungsi setelah config di-cache

---

## 23. Testing — Minimal Feature Test untuk Happy Path

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_users(): void
    {
        User::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/users');

        $response
            ->assertOk()
            ->assertJsonCount(5, 'data');
    }

    public function test_can_create_user(): void
    {
        $data = [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/users', $data);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'John Doe');

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_validation_fails_without_name(): void
    {
        $response = $this->postJson('/api/v1/users', [
            'email' => 'john@example.com',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'password']);
    }
}
```

**Aturan Testing:**
- ✅ Minimal tulis test untuk setiap endpoint API (happy path + validation error)
- ✅ Gunakan `RefreshDatabase` trait — database bersih setiap test
- ✅ Gunakan Factory untuk generate test data
- ✅ Gunakan `assertDatabaseHas` / `assertDatabaseMissing` untuk cek side-effect
- Jalankan test: `php artisan test` atau `./vendor/bin/phpunit`

---

## 24. Artisan Commands — Cheat Sheet

```bash
# === Development ===
php artisan serve                     # Jalankan development server
php artisan tinker                    # REPL interaktif
php artisan route:list                # Lihat semua route
php artisan model:show User           # Lihat detail model

# === Make (Generate File) ===
php artisan make:model Post -mfs      # Model + Migration + Factory + Seeder
php artisan make:controller PostController --api   # API Controller
php artisan make:request StorePostRequest           # Form Request
php artisan make:resource PostResource              # API Resource
php artisan make:service PostService                # Service (Laravel 11+)
php artisan make:enum PostStatus                    # Enum (Laravel 11+)
php artisan make:job ProcessPost                    # Job
php artisan make:event PostCreated                  # Event
php artisan make:listener NotifyUser                # Listener
php artisan make:observer PostObserver              # Observer
php artisan make:policy PostPolicy                  # Policy
php artisan make:middleware EnsureIsAdmin            # Middleware
php artisan make:test PostTest                      # Feature test
php artisan make:test PostTest --unit               # Unit test

# === Database ===
php artisan migrate                   # Run migration
php artisan migrate:rollback          # Rollback
php artisan migrate:fresh --seed      # Reset + seed (dev only)
php artisan db:seed                   # Run seeder

# === Production Optimization ===
php artisan config:cache              # Cache config
php artisan route:cache               # Cache routes
php artisan view:cache                # Cache views
php artisan optimize                  # Cache config + routes + views

# === Cache & Queue ===
php artisan cache:clear               # Clear cache
php artisan queue:work                # Run queue worker
php artisan queue:work --tries=3      # Dengan retry
```

---

## 25. Checklist Sebelum Deploy

### Code Quality
- [ ] Tidak ada `dd()`, `dump()`, `var_dump()` yang tertinggal
- [ ] Semua endpoint punya Form Request validation
- [ ] Semua response API menggunakan API Resource
- [ ] `preventLazyLoading` aktif di development
- [ ] Business logic ada di Service, bukan di Controller
- [ ] Tidak ada raw SQL query tanpa parameter binding

### Database
- [ ] Semua migration sudah ter-test (`migrate:fresh` berhasil)
- [ ] Foreign key constraint sudah benar
- [ ] Index sudah ditambahkan untuk kolom yang sering di-query
- [ ] Tidak ada N+1 query (pakai eager loading)
- [ ] Transaction dipakai untuk operasi multi-table

### Security
- [ ] `$fillable` didefinisikan di setiap model
- [ ] Tidak ada data sensitif yang hardcode di code
- [ ] `.env` tidak masuk version control
- [ ] Rate limiting aktif di API routes
- [ ] CORS dikonfigurasi dengan benar

### Performance
- [ ] Cache digunakan untuk data yang jarang berubah
- [ ] Queue digunakan untuk proses berat (email, export, dll)
- [ ] Production: `php artisan optimize` sudah dijalankan
- [ ] Pagination digunakan untuk list endpoint

---

## Quick Reference — Do & Don't

```php
// ❌ DON'T
$request->all();                              // Mass assign tanpa filter
User::all()->where('active', true);           // Filter di PHP, bukan di DB
$guarded = [];                                // Buka semua kolom
DB::select("SELECT * FROM users WHERE id = $id"); // SQL injection
dd($data);                                    // Debug di production
env('APP_NAME');                              // Panggil env() di luar config
$user->orders;                                // Lazy loading (N+1)
// Logic panjang di controller                // Controller gemuk

// ✅ DO
$request->validated();                        // Hanya data tervalidasi
User::where('is_active', true)->get();        // Filter di database
$fillable = ['name', 'email'];               // Whitelist kolom
User::where('id', $id)->first();             // Parameterized query
Log::info('message', $context);               // Structured logging
config('app.name');                           // Lewat config helper
User::with('orders')->get();                  // Eager loading
// Logic di Service, Controller tipis         // Separation of concerns
```

---

> **Catatan:** File ini adalah living document. Update sesuai kebutuhan tim dan project.
