# Livewire Best Practices

> Panduan ini ditulis dengan bahasa high-level agar mudah dipahami oleh junior programmer maupun AI model. Gunakan file ini sebagai referensi saat membangun UI dinamis dengan Laravel Livewire — mulai dari struktur komponen, data binding, hingga optimasi performa.

---

## 1. Versi & Setup

| Kategori | Rekomendasi |
|---|---|
| Livewire | Gunakan **v3.x** (stable terbaru, terintegrasi dengan Laravel 10/11/12) |
| Laravel | Minimal **Laravel 10+** |
| PHP | Minimal **PHP 8.1+** |

**Instalasi:**

```bash
composer require livewire/livewire
```

**Aturan:**
- Livewire sudah auto-inject script & style di layout — tidak perlu manual `@livewireStyles` / `@livewireScripts` di v3
- Pastikan layout Blade punya `{{ $slot }}` untuk component rendering
- Gunakan `php artisan livewire:publish --config` jika perlu custom konfigurasi

---

## 2. Struktur Folder & Naming Convention

### Struktur Standar

```
app/
├── Livewire/
│   ├── Forms/              # Form Object (validasi terpisah)
│   ├── Pages/              # Full-page component
│   ├── Partials/           # Komponen kecil / reusable
│   └── Widgets/            # Widget (chart, stats, dll)
resources/
└── views/
    └── livewire/
        ├── pages/          # View untuk full-page component
        ├── partials/       # View untuk partial component
        └── widgets/        # View untuk widget
```

### Naming Convention

| Kategori | Convention | Contoh |
|---|---|---|
| Component Class | PascalCase | `CreateOrder`, `UserTable` |
| Component View | kebab-case | `create-order.blade.php`, `user-table.blade.php` |
| Form Object | PascalCase + Form | `OrderForm`, `UserForm` |
| Nested folder | Dot notation di Blade | `<livewire:pages.dashboard />` |
| Full-page route | Langsung di Route | `Route::get('/orders', CreateOrder::class)` |

**Aturan:**
- ✅ Nama class = deskriptif sesuai fungsi: `EditProfile`, `SearchProducts`
- ❌ Jangan pakai prefix `Livewire` di nama class — sudah di namespace `App\Livewire`
- ✅ Group komponen terkait dalam subfolder: `Livewire/Order/CreateOrder`, `Livewire/Order/ListOrder`

---

## 3. Anatomi Component — Struktur yang Bersih

### Urutan Penulisan di Component

Ikuti urutan ini agar konsisten:

1. **Traits** (`use WithPagination`, `use WithFileUploads`)
2. **Properties** (public, protected, private)
3. **Lifecycle hooks** (`mount`, `boot`, `hydrate`)
4. **Computed properties**
5. **Action methods** (method yang dipanggil dari view)
6. **Helper methods** (private/protected)
7. **`render()`** — selalu paling bawah

### Contoh Component Bersih

```php
<?php

namespace App\Livewire;

use App\Livewire\Forms\OrderForm;
use App\Models\Order;
use App\Services\OrderService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ListOrder extends Component
{
    use WithPagination;

    // ===== Properties =====
    public string $search = '';
    public string $status = '';

    // ===== Lifecycle =====
    public function mount(): void
    {
        // Hanya dipanggil sekali saat component pertama kali dibuat
    }

    // ===== Computed =====
    #[Computed]
    public function orders()
    {
        return Order::query()
            ->when($this->search, fn ($q) => $q->where('order_number', 'like', "%{$this->search}%"))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->with('user')
            ->latest()
            ->paginate(15);
    }

    // ===== Actions =====
    public function delete(int $orderId): void
    {
        Order::findOrFail($orderId)->delete();
    }

    public function updatedSearch(): void
    {
        $this->resetPage(); // Reset pagination saat search berubah
    }

    // ===== Render =====
    public function render()
    {
        return view('livewire.list-order');
    }
}
```

---

## 4. Properties — Aturan Data Binding

### Public Property = State yang Dikirim ke Frontend

```php
// ✅ Property yang bisa di-bind dari view
public string $name = '';
public int $quantity = 1;
public bool $isActive = false;
public array $selectedIds = [];

// ✅ Lock property agar tidak bisa dimanipulasi dari frontend
#[Locked]
public int $orderId;

// ❌ JANGAN expose data sensitif sebagai public property
public string $secretToken = ''; // BAHAYA — bisa dilihat/diubah dari DevTools
```

### wire:model — Binding Input ke Property

```blade
{{-- ✅ Default: update saat form submit (paling efisien) --}}
<input type="text" wire:model="name">

{{-- ✅ Live: update real-time ke server (untuk search, filter) --}}
<input type="text" wire:model.live.debounce.300ms="search">

{{-- ✅ Blur: update saat input kehilangan fokus --}}
<input type="text" wire:model.blur="email">

{{-- ✅ Lazy: update saat change event --}}
<select wire:model.live="status">
    <option value="">Semua</option>
    <option value="pending">Pending</option>
</select>
```

**Aturan wire:model:**

| Modifier | Kapan Pakai |
|---|---|
| `wire:model` (tanpa modifier) | Form input biasa — update saat submit |
| `wire:model.live` | Search, filter, toggle — butuh respons real-time |
| `wire:model.live.debounce.300ms` | Search input — hindari request berlebihan |
| `wire:model.blur` | Validasi per-field saat user pindah input |
| `wire:model.number` | Input angka — auto cast ke integer |
| `wire:model.boolean` | Checkbox — auto cast ke boolean |

**Aturan:**
- ✅ Default pakai `wire:model` tanpa modifier — paling hemat request
- ⚠️ Gunakan `.live` hanya jika memang butuh real-time update
- ✅ Selalu tambahkan `.debounce` jika pakai `.live` pada text input

---

## 5. Form Object — Pisahkan Validasi dari Component

### Kenapa Pakai Form Object?

Agar component tetap bersih, validasi dan property form dipisahkan ke class tersendiri.

```php
<?php
// app/Livewire/Forms/OrderForm.php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class OrderForm extends Form
{
    #[Validate('required|string|max:255')]
    public string $customer_name = '';

    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|numeric|min:0')]
    public float $total_amount = 0;

    #[Validate('nullable|string|max:500')]
    public string $notes = '';
}
```

### Penggunaan di Component

```php
<?php

namespace App\Livewire;

use App\Livewire\Forms\OrderForm;
use App\Models\Order;
use App\Services\OrderService;
use Livewire\Component;

class CreateOrder extends Component
{
    public OrderForm $form;

    public function save(OrderService $orderService): void
    {
        // ✅ Validasi otomatis dari Form Object
        $validated = $this->form->validate();

        $orderService->createOrder($validated);

        // ✅ Reset form setelah berhasil
        $this->form->reset();

        // ✅ Feedback ke user
        session()->flash('success', 'Order berhasil dibuat.');
        $this->redirect('/orders');
    }

    public function render()
    {
        return view('livewire.create-order');
    }
}
```

### View dengan Form Object

```blade
<form wire:submit="save">
    <div>
        <label>Nama Customer</label>
        <input type="text" wire:model="form.customer_name">
        @error('form.customer_name') <span class="text-red-500">{{ $message }}</span> @enderror
    </div>

    <div>
        <label>Email</label>
        <input type="email" wire:model="form.email">
        @error('form.email') <span class="text-red-500">{{ $message }}</span> @enderror
    </div>

    <button type="submit">Simpan</button>
</form>
```

**Aturan:**
- ✅ Gunakan Form Object untuk semua form yang punya lebih dari 2 field
- ✅ Gunakan `#[Validate]` attribute di Form Object — lebih bersih dari `rules()` method
- ✅ Panggil `$this->form->reset()` setelah submit berhasil
- ✅ Untuk edit: gunakan `$this->form->fill($model->toArray())` di `mount()`

---

## 6. Validasi — Real-time & On Submit

### Real-time Validation (Per-field)

```php
// ✅ Validasi otomatis saat property berubah (pakai wire:model.blur)
#[Validate('required|email|unique:users,email')]
public string $email = '';
```

```blade
<input type="email" wire:model.blur="email">
@error('email') <span class="text-red-500">{{ $message }}</span> @enderror
```

### Validasi Saat Submit

```php
public function save(): void
{
    // ✅ Validasi semua field sekaligus
    $this->validate();

    // Proses data...
}
```

### Custom Validation Rules

```php
// ✅ Jika butuh rule kompleks, override rules() di Form Object
class OrderForm extends Form
{
    public string $start_date = '';
    public string $end_date = '';

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'end_date.after' => 'Tanggal selesai harus setelah tanggal mulai.',
        ];
    }
}
```

---

## 7. Actions & Events — Komunikasi Antar Component

### Actions — Method yang Dipanggil dari View

```blade
{{-- ✅ Klik button --}}
<button wire:click="approve({{ $orderId }})">Approve</button>

{{-- ✅ Konfirmasi sebelum action --}}
<button wire:click="delete({{ $id }})" wire:confirm="Yakin ingin menghapus?">Hapus</button>

{{-- ✅ Submit form --}}
<form wire:submit="save">
```

### Events — Komunikasi Antar Component

```php
// ===== Component A: Dispatch event =====
public function save(): void
{
    // ... save logic
    $this->dispatch('order-created', orderId: $order->id);
}

// ===== Component B: Listen event =====
use Livewire\Attributes\On;

#[On('order-created')]
public function handleOrderCreated(int $orderId): void
{
    // Refresh data atau tampilkan notifikasi
}
```

### Refresh Component

```php
// ✅ Refresh dari component lain
$this->dispatch('$refresh')->to(OrderTable::class);

// ✅ Self-refresh (dari view)
// <button wire:click="$refresh">Refresh</button>
```

**Aturan:**
- ✅ Gunakan event untuk komunikasi **antar component yang setara** (sibling)
- ✅ Gunakan parameter untuk komunikasi **parent ke child**
- ✅ Prefix event dengan domain: `order-created`, `user-updated`
- ❌ Jangan pakai event untuk komunikasi yang bisa diselesaikan dengan property/parameter

---

## 8. Computed Properties — Hindari Query di render()

### Kenapa Computed Property?

Computed property di-cache selama 1 request lifecycle — tidak dipanggil ulang meskipun diakses berkali-kali di view.

```php
use Livewire\Attributes\Computed;

// ✅ Computed — query hanya jalan sekali per request
#[Computed]
public function categories()
{
    return Category::where('is_active', true)->get();
}

// ✅ Akses di view: $this->categories (di class) atau $categories (di blade)
```

```blade
{{-- ✅ Di view, akses langsung tanpa () --}}
@foreach ($this->categories as $category)
    <option value="{{ $category->id }}">{{ $category->name }}</option>
@endforeach
```

**Aturan:**
- ✅ Gunakan `#[Computed]` untuk data yang **di-query dari database**
- ✅ Gunakan `#[Computed]` untuk data dropdown, list, dan data referensi
- ❌ Jangan simpan result query ke public property — boros memory dan payload
- ❌ Jangan panggil query langsung di `render()` tanpa computed

---

## 9. Loading States & UX Feedback

### Loading Indicator

```blade
{{-- ✅ Tampilkan loading saat action berjalan --}}
<button wire:click="save">
    <span wire:loading.remove wire:target="save">Simpan</span>
    <span wire:loading wire:target="save">Menyimpan...</span>
</button>

{{-- ✅ Disable button saat loading --}}
<button wire:click="save" wire:loading.attr="disabled" wire:target="save">
    Simpan
</button>

{{-- ✅ Loading overlay untuk section --}}
<div wire:loading.class="opacity-50" wire:target="search">
    {{-- content --}}
</div>
```

### Flash Message

```php
// Di component
session()->flash('success', 'Data berhasil disimpan.');

// Atau gunakan Livewire event untuk toast/notification
$this->dispatch('notify', type: 'success', message: 'Berhasil!');
```

```blade
{{-- Di view --}}
@if (session()->has('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
```

**Aturan:**
- ✅ Selalu tampilkan loading state untuk action yang butuh waktu
- ✅ Gunakan `wire:target` untuk menarget loading ke action spesifik
- ✅ Disable button saat proses berjalan — cegah double submit
- ✅ Berikan feedback (flash/toast) setelah setiap action berhasil/gagal

---

## 10. Pagination & Data Besar

```php
use Livewire\WithPagination;

class UserTable extends Component
{
    use WithPagination;

    public string $search = '';

    // ✅ Reset page saat filter berubah
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->with('role')      // Eager load
            ->latest()
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.user-table');
    }
}
```

```blade
{{-- Di view --}}
<div>
    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari...">

    <table>
        @foreach ($this->users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->role->name }}</td>
            </tr>
        @endforeach
    </table>

    {{ $this->users->links() }}
</div>
```

**Aturan:**
- ✅ Selalu gunakan `WithPagination` untuk list data — jangan `->get()` tanpa limit
- ✅ Panggil `$this->resetPage()` di setiap `updated*` method untuk filter/search
- ✅ Eager load relationship di query — hindari N+1

---

## 11. File Upload

```php
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;

class UploadDocument extends Component
{
    use WithFileUploads;

    #[Validate('required|file|mimes:pdf,jpg,png|max:2048')] // 2MB
    public $document;

    public function save(): void
    {
        $this->validate();

        $path = $this->document->store('documents', 'public');

        Document::create([
            'path' => $path,
            'name' => $this->document->getClientOriginalName(),
        ]);

        $this->reset('document');
        session()->flash('success', 'File berhasil diupload.');
    }
}
```

```blade
<form wire:submit="save">
    <input type="file" wire:model="document">

    {{-- ✅ Preview & loading saat upload --}}
    <div wire:loading wire:target="document">Uploading...</div>

    @error('document') <span class="text-red-500">{{ $message }}</span> @enderror

    <button type="submit" wire:loading.attr="disabled" wire:target="document">
        Upload
    </button>
</form>
```

**Aturan:**
- ✅ Selalu validasi `mimes` dan `max` size
- ❌ Jangan pakai `upload` sebagai nama property/method — reserved oleh Livewire
- ✅ Tampilkan loading indicator saat file sedang di-upload
- ✅ Simpan file ke `storage`, bukan ke `public` langsung

---

## 12. Security — Jangan Abaikan

### Property Security

```php
// ✅ Lock property yang tidak boleh diubah dari frontend
#[Locked]
public int $orderId;

#[Locked]
public int $userId;

// ✅ Authorize di setiap action
public function delete(int $orderId): void
{
    $order = Order::findOrFail($orderId);
    $this->authorize('delete', $order); // Policy check

    $order->delete();
}
```

### Checklist Security

| Aspek | Aturan |
|---|---|
| Public property | ⚠️ Semua bisa dilihat & diubah dari frontend — hati-hati |
| Sensitive data | ✅ Gunakan `#[Locked]` untuk ID, foreign key, dan data sensitif |
| Authorization | ✅ Cek izin di setiap action method — jangan percaya frontend |
| Mass assignment | ✅ Gunakan `$this->form->validate()` bukan `$this->all()` |
| XSS | ✅ Gunakan `{{ }}` (escaped) di Blade, hindari `{!! !!}` |
| Rate limiting | ✅ Terapkan `#[Throttle]` pada action yang berat |

---

## 13. Full-Page Component — Pengganti Controller

```php
<?php

namespace App\Livewire\Pages;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Detail Order')]
class ShowOrder extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        $this->authorize('view', $order);
        $this->order = $order->load('items.product', 'user');
    }

    public function render()
    {
        return view('livewire.pages.show-order');
    }
}
```

### Route untuk Full-Page Component

```php
// routes/web.php
use App\Livewire\Pages\ShowOrder;
use App\Livewire\Pages\ListOrder;
use App\Livewire\Pages\CreateOrder;

Route::middleware('auth')->group(function () {
    Route::get('/orders', ListOrder::class)->name('orders.index');
    Route::get('/orders/create', CreateOrder::class)->name('orders.create');
    Route::get('/orders/{order}', ShowOrder::class)->name('orders.show');
});
```

**Aturan:**
- ✅ Gunakan full-page component untuk halaman yang sepenuhnya Livewire
- ✅ Tetap gunakan Controller tradisional jika halaman mayoritas statis
- ✅ Selalu set `#[Layout]` dan `#[Title]` di full-page component

---

## 14. Arsitektur — Integrasi dengan Laravel

### Prinsip Arsitektur

```
User Interaction → Livewire Component → Service → Model → Database
                        ↓
                   Form Object (validasi)
                        ↓
                   View (Blade)
```

**Aturan Kritis:**
- ✅ **Component harus tipis** — hanya handle UI interaction dan panggil service
- ✅ **Business logic tetap di Service** — bukan di component
- ✅ **Inject Service via method injection** — Livewire support DI di action method
- ❌ Jangan taruh query kompleks langsung di component — pindahkan ke service/repository

### Contoh Integrasi yang Benar

```php
// ✅ Component tipis — logic di service
public function approve(int $orderId, OrderService $service): void
{
    $order = Order::findOrFail($orderId);
    $this->authorize('approve', $order);

    $service->approveOrder($order);

    $this->dispatch('notify', type: 'success', message: 'Order disetujui.');
}
```

---

## 15. Performance — Optimasi

### Aturan Performance

| Aspek | Aturan |
|---|---|
| wire:model | ✅ Default tanpa `.live` — kirim data hanya saat submit |
| Computed | ✅ Pakai `#[Computed]` — cache query dalam 1 request |
| Lazy loading | ✅ Pakai `lazy` attribute untuk component berat: `<livewire:heavy-chart lazy />` |
| Pagination | ✅ Selalu paginate — jangan load semua data |
| Eager loading | ✅ Selalu `->with()` relationship yang dipakai di view |
| wire:key | ✅ Tambahkan `wire:key` di setiap item loop untuk tracking DOM |

### wire:key di Loop

```blade
{{-- ✅ Selalu pakai wire:key di dalam loop --}}
@foreach ($this->orders as $order)
    <div wire:key="order-{{ $order->id }}">
        {{ $order->order_number }}
    </div>
@endforeach
```

### Lazy Loading Component

```blade
{{-- ✅ Component berat di-load setelah halaman render --}}
<livewire:widgets.sales-chart lazy />

{{-- ✅ Dengan placeholder --}}
<livewire:widgets.sales-chart lazy>
    <x-slot:placeholder>
        <div>Loading chart...</div>
    </x-slot:placeholder>
</livewire:widgets.sales-chart>
```

---

## 16. Lifecycle Hooks — Referensi Cepat

| Hook | Kapan Dipanggil | Use Case |
|---|---|---|
| `mount()` | Sekali, saat component dibuat | Inisialisasi data, fill form |
| `boot()` | Setiap request (sebelum action) | Setup dependency |
| `hydrate()` | Setiap request (setelah state di-restore) | Re-attach non-serializable data |
| `updating($prop, $val)` | Sebelum property di-update | Validasi atau block update |
| `updated($prop, $val)` | Setelah property di-update | Side-effect, auto-save |
| `updatedPropertyName()` | Setelah property spesifik di-update | Reset pagination, filter |
| `rendering()` | Sebelum render view | Last-minute data prep |
| `rendered()` | Setelah render view | Post-render logic |
| `dehydrate()` | Sebelum response dikirim | Clean up |

---

## 17. Testing Livewire Component

```php
<?php

namespace Tests\Feature\Livewire;

use App\Livewire\CreateOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_order(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateOrder::class)
            ->set('form.customer_name', 'John Doe')
            ->set('form.email', 'john@example.com')
            ->set('form.total_amount', 100000)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect('/orders');

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'John Doe',
        ]);
    }

    public function test_validation_fails_without_name(): void
    {
        Livewire::test(CreateOrder::class)
            ->set('form.customer_name', '')
            ->call('save')
            ->assertHasErrors(['form.customer_name' => 'required']);
    }

    public function test_can_search_orders(): void
    {
        Livewire::test(ListOrder::class)
            ->set('search', 'ORD-001')
            ->assertSee('ORD-001');
    }
}
```

**Aturan Testing:**
- ✅ Test setiap form: happy path + validation error
- ✅ Gunakan `Livewire::test()` — bukan HTTP test biasa
- ✅ Test event dispatch dengan `->assertDispatched('event-name')`
- ✅ Test authorization dengan `->assertForbidden()`

---

## 18. Artisan Commands — Cheat Sheet

```bash
# === Generate Component ===
php artisan make:livewire CreateOrder            # Component + View
php artisan make:livewire Pages/Dashboard        # Nested (subfolder)
php artisan make:livewire OrderForm --form        # Form Object
php artisan make:livewire CreateOrder --inline    # Tanpa file view terpisah
php artisan make:livewire CreateOrder --test      # Component + Test file

# === Utility ===
php artisan livewire:configure-s3-upload-cleanup  # Setup S3 temp cleanup
php artisan livewire:publish --config             # Publish config file
```

---

## 19. Checklist Sebelum Deploy

### Component Quality
- [ ] Semua component menggunakan Form Object untuk validasi
- [ ] Business logic ada di Service, bukan di component
- [ ] `#[Locked]` digunakan untuk property sensitif (ID, foreign key)
- [ ] Authorization dicek di setiap action method
- [ ] Loading state ditampilkan untuk semua action

### Performance
- [ ] `wire:model` tanpa `.live` di form biasa (hemat request)
- [ ] `#[Computed]` digunakan untuk query database
- [ ] `wire:key` ada di setiap item dalam loop
- [ ] Pagination digunakan untuk list data
- [ ] Eager loading (`->with()`) digunakan untuk relationship
- [ ] Component berat menggunakan `lazy` loading

### UX
- [ ] Error validation ditampilkan per-field dengan `@error`
- [ ] Flash message / toast setelah setiap action
- [ ] Button disabled saat loading (cegah double submit)
- [ ] Konfirmasi (`wire:confirm`) untuk action destruktif (delete)

---

## Quick Reference — Do & Don't

```php
// ❌ DON'T
public string $password = '';                    // Data sensitif di public property
wire:model.live="name"                           // Live tanpa debounce di text input
$this->all();                                    // Ambil semua property tanpa validasi
Order::all();                                    // Load semua data tanpa paginate
// Query langsung di render()                    // Query ulang setiap render
// Logic bisnis panjang di component             // Component gemuk

// ✅ DO
#[Locked] public int $userId;                    // Lock property sensitif
wire:model.live.debounce.300ms="search"          // Debounce untuk live input
$this->form->validate();                         // Validasi lewat Form Object
Order::paginate(15);                             // Selalu paginate
#[Computed] public function orders() {}          // Cache query dengan computed
// Panggil Service dari component                // Component tipis
```

---

> **Catatan:** File ini adalah living document. Update sesuai kebutuhan tim dan project. Gunakan bersama `laravel.md` karena Livewire berjalan di atas Laravel — semua best practice Laravel tetap berlaku.
