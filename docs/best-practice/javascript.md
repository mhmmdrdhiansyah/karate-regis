# JavaScript Best Practices

> Panduan ini ditulis dengan bahasa high-level agar mudah dipahami oleh junior programmer maupun AI model. Gunakan file ini sebagai referensi saat membangun sistem baru dengan JavaScript.

---

## 1. Gunakan `const` dan `let` — Jangan `var`

`var` memiliki scoping yang membingungkan dan bisa menyebabkan bug yang sulit dilacak.

```js
// ❌ Jangan — var memiliki function scope dan bisa di-hoist
var name = "John";
var name = "Jane"; // tidak error — rawan bug

// ✅ Gunakan const untuk nilai yang tidak berubah
const MAX_RETRIES = 3;
const API_URL = "/api/users";

// ✅ Gunakan let untuk nilai yang akan berubah
let currentPage = 1;
currentPage = 2; // ✅ boleh di-reassign
```

**Aturan simpel:**
- Default pakai `const` — hanya pakai `let` jika nilai memang perlu berubah
- Jangan pernah pakai `var`

---

## 2. Arrow Function vs Regular Function

```js
// ✅ Arrow function — untuk callback dan fungsi pendek
const double = (n) => n * 2;
const names = users.map((u) => u.name);
const filtered = items.filter((item) => item.active);

// ✅ Regular function — untuk function utama / yang di-export
function calculateTotal(items) {
  return items.reduce((sum, item) => sum + item.price, 0);
}

// ✅ Regular function — untuk function yang butuh hoisting
function formatCurrency(amount) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
  }).format(amount);
}
```

**Aturan:**
- Arrow function → callback, utility kecil, inline function
- Regular function → function utama, method yang di-export, function yang perlu hoisting
- Jangan campur style di satu file — pilih satu pattern dan konsisten

---

## 3. Destructuring — Ambil Data dengan Bersih

```js
// ❌ Akses property satu per satu
const name = user.name;
const email = user.email;
const role = user.role;

// ✅ Destructuring object
const { name, email, role } = user;

// ✅ Destructuring dengan rename
const { name: userName, email: userEmail } = user;

// ✅ Destructuring dengan default value
const { name, role = "viewer" } = user;

// ✅ Destructuring array
const [first, second, ...rest] = items;

// ✅ Destructuring parameter function
function greet({ name, age }) {
  return `Halo ${name}, umur ${age} tahun`;
}
```

**Tips:** Jangan destructure terlalu dalam (nested). Jika sudah > 2 level, simpan ke variable terpisah.

---

## 4. Spread & Rest Operator

```js
// ✅ Spread — copy atau merge object/array (immutable)
const updatedUser = { ...user, name: "Jane" };
const allItems = [...oldItems, ...newItems];

// ❌ Jangan mutasi object/array asli
user.name = "Jane";       // ❌ mutasi langsung
items.push(newItem);       // ❌ mutasi langsung

// ✅ Selalu buat salinan baru
const updatedUser = { ...user, name: "Jane" };
const newItems = [...items, newItem];

// ✅ Rest — kumpulkan sisa parameter
function createUser({ password, ...publicData }) {
  // publicData = semua field kecuali password
  saveToDb(publicData);
}
```

---

## 5. Template Literals — Jangan Pakai Concatenation

```js
// ❌ String concatenation — sulit dibaca
const message = "Halo " + name + ", selamat datang di " + appName + "!";

// ✅ Template literal — bersih dan jelas
const message = `Halo ${name}, selamat datang di ${appName}!`;

// ✅ Multi-line string
const html = `
  <div class="card">
    <h2>${title}</h2>
    <p>${description}</p>
  </div>
`;
```

---

## 6. Array Methods — Ganti Loop Manual

Gunakan array methods bawaan daripada `for` loop manual. Lebih deklaratif dan mudah dibaca.

```js
// ❌ Loop manual
const activeUsers = [];
for (let i = 0; i < users.length; i++) {
  if (users[i].active) {
    activeUsers.push(users[i]);
  }
}

// ✅ filter
const activeUsers = users.filter((u) => u.active);

// ✅ map — transformasi data
const names = users.map((u) => u.name);

// ✅ find — cari satu item
const admin = users.find((u) => u.role === "admin");

// ✅ some / every — cek kondisi
const hasAdmin = users.some((u) => u.role === "admin");
const allActive = users.every((u) => u.active);

// ✅ reduce — kalkulasi / akumulasi
const total = orders.reduce((sum, order) => sum + order.amount, 0);

// ✅ Chaining — kombinasi beberapa method
const result = users
  .filter((u) => u.active)
  .map((u) => u.name)
  .sort((a, b) => a.localeCompare(b));
```

**Kapan pakai `for` loop?** Hanya jika butuh `break` / `continue` / performa sangat kritis di iterasi besar.

---

## 7. Object Shorthand & Computed Properties

```js
// ✅ Property shorthand — nama variable sama dengan key
const name = "John";
const age = 25;

// ❌ Redundant
const user = { name: name, age: age };

// ✅ Shorthand
const user = { name, age };

// ✅ Method shorthand
const service = {
  getUser(id) {
    return db.find(id);
  },
  // bukan: getUser: function(id) { ... }
};

// ✅ Computed property names
const field = "email";
const data = { [field]: "john@email.com" }; // { email: "john@email.com" }
```

---

## 8. Optional Chaining & Nullish Coalescing

```js
// ❌ Pengecekan manual bertingkat
const city =
  user && user.address && user.address.city ? user.address.city : "Unknown";

// ✅ Optional chaining (?.) + Nullish coalescing (??)
const city = user?.address?.city ?? "Unknown";

// ✅ Optional chaining untuk method
const result = api?.getUser?.(id);

// ✅ Optional chaining untuk array
const firstTag = post?.tags?.[0] ?? "none";
```

**Perbedaan `??` vs `||`:**

```js
const count = 0;

count || 10;  // → 10 ❌ (0 dianggap falsy)
count ?? 10;  // → 0  ✅ (hanya null/undefined yang diganti)

const text = "";
text || "default";  // → "default" ❌ (string kosong dianggap falsy)
text ?? "default";  // → ""         ✅ (hanya null/undefined yang diganti)
```

**Aturan:** Gunakan `??` untuk default value. Gunakan `||` hanya jika memang ingin mengganti semua falsy value (`0`, `""`, `false`).

---

## 9. Async/Await — Hindari Callback Hell

```js
// ❌ Callback hell
getUser(id, (user) => {
  getOrders(user.id, (orders) => {
    getPayments(orders[0].id, (payments) => {
      // deeply nested...
    });
  });
});

// ❌ .then chaining yang panjang
getUser(id)
  .then((user) => getOrders(user.id))
  .then((orders) => getPayments(orders[0].id))
  .then((payments) => console.log(payments));

// ✅ async/await — flat dan mudah dibaca
async function getUserPayments(id) {
  const user = await getUser(id);
  const orders = await getOrders(user.id);
  const payments = await getPayments(orders[0].id);
  return payments;
}
```

### Error Handling di Async

```js
// ✅ try/catch untuk error handling
async function fetchUser(id) {
  try {
    const res = await fetch(`/api/users/${id}`);
    if (!res.ok) {
      throw new Error(`HTTP error: ${res.status}`);
    }
    return await res.json();
  } catch (error) {
    console.error("Failed to fetch user:", error.message);
    throw error; // re-throw agar caller tahu ada error
  }
}
```

### Parallel Execution

```js
// ❌ Sequential — lambat
const users = await fetchUsers();
const roles = await fetchRoles();

// ✅ Parallel — jauh lebih cepat
const [users, roles] = await Promise.all([
  fetchUsers(),
  fetchRoles(),
]);

// ✅ Promise.allSettled — jika ingin hasil meski ada yang gagal
const results = await Promise.allSettled([
  fetchUsers(),
  fetchRoles(),
]);

results.forEach((result) => {
  if (result.status === "fulfilled") {
    console.log(result.value);
  } else {
    console.error(result.reason);
  }
});
```

---

## 10. Error Handling — Jangan Abaikan Error

```js
// ❌ Menelan error tanpa penanganan
try {
  await saveData(data);
} catch (e) {
  // kosong — error hilang tanpa jejak
}

// ❌ Catch tanpa tindakan yang berguna
try {
  await saveData(data);
} catch (e) {
  console.log(e); // hanya log, tidak ada recovery
}

// ✅ Handle error dengan benar
try {
  await saveData(data);
} catch (error) {
  console.error("Gagal menyimpan data:", error.message);
  // pilih salah satu: retry, throw, atau return default
  throw error;
}
```

### Custom Error Class

```js
// ✅ Buat error class sesuai domain
class AppError extends Error {
  constructor(message, code, statusCode = 500) {
    super(message);
    this.name = "AppError";
    this.code = code;
    this.statusCode = statusCode;
  }
}

class NotFoundError extends AppError {
  constructor(entity, id) {
    super(`${entity} with id ${id} not found`, "NOT_FOUND", 404);
  }
}

class ValidationError extends AppError {
  constructor(message) {
    super(message, "VALIDATION_ERROR", 400);
  }
}

// Penggunaan
async function getUser(id) {
  const user = await db.users.findById(id);
  if (!user) {
    throw new NotFoundError("User", id);
  }
  return user;
}
```

---

## 11. Module System — Gunakan ES Modules

```js
// ❌ CommonJS (usang untuk project baru)
const express = require("express");
module.exports = { getUser };

// ✅ ES Modules
import express from "express";
export { getUser };

// ✅ Named exports (disarankan)
export function getUser(id) { /* ... */ }
export function createUser(data) { /* ... */ }

// ✅ Import named
import { getUser, createUser } from "./user-service.js";

// ✅ Default export — hanya untuk 1 hal utama per file
export default class UserService { /* ... */ }
```

**Aturan:**
- Gunakan named export — lebih mudah di-refactor dan di-autocomplete
- Default export hanya untuk class utama atau konfigurasi
- Jangan campur named dan default export di satu file tanpa alasan

---

## 12. Ternary & Short Circuit — Jaga Keterbacaan

```js
// ✅ Ternary sederhana — boleh
const status = isActive ? "Active" : "Inactive";
const label = count === 0 ? "Empty" : `${count} items`;

// ❌ Nested ternary — sulit dibaca
const color = status === "active" ? "green" : status === "pending" ? "yellow" : "red";

// ✅ Gunakan if-else atau object map untuk kondisi banyak
const colorMap = {
  active: "green",
  pending: "yellow",
  inactive: "red",
};
const color = colorMap[status] ?? "gray";
```

**Aturan:** Ternary hanya untuk 1 level kondisi. Jika lebih dari 1 → gunakan `if-else`, `switch`, atau object map.

---

## 13. Guard Clause — Early Return

Hindari nesting dalam yang berlebihan. Gunakan early return (guard clause).

```js
// ❌ Deeply nested
function processOrder(order) {
  if (order) {
    if (order.items.length > 0) {
      if (order.status === "pending") {
        // proses order...
        return result;
      }
    }
  }
  return null;
}

// ✅ Guard clause — flat dan mudah dibaca
function processOrder(order) {
  if (!order) return null;
  if (order.items.length === 0) return null;
  if (order.status !== "pending") return null;

  // proses order...
  return result;
}
```

---

## 14. Naming Conventions

| Kategori | Convention | Contoh |
|---|---|---|
| Variable & Function | camelCase | `getUserById`, `isActive` |
| Constant | SCREAMING_SNAKE_CASE | `MAX_RETRIES`, `API_BASE_URL` |
| Class | PascalCase | `UserService`, `AppError` |
| File (general) | kebab-case | `user-service.js`, `api-client.js` |
| Boolean variable | prefix `is/has/can/should` | `isLoading`, `hasPermission` |
| Event handler | prefix `handle` atau `on` | `handleClick`, `onSubmit` |
| Private property (convention) | prefix `_` | `_internalCache` |

### Penamaan yang Deskriptif

```js
// ❌ Nama tidak jelas
const d = new Date();
const arr = users.filter((x) => x.a);
const temp = calculate(input);

// ✅ Nama deskriptif
const createdAt = new Date();
const activeUsers = users.filter((user) => user.isActive);
const totalRevenue = calculateRevenue(monthlyData);
```

---

## 15. Immutability — Jangan Mutasi Data

```js
// ❌ Mutasi object
const user = { name: "John", age: 25 };
user.age = 26; // mutasi langsung

// ✅ Buat object baru
const updatedUser = { ...user, age: 26 };

// ❌ Mutasi array
const items = [1, 2, 3];
items.push(4);           // mutasi langsung
items.splice(1, 1);      // mutasi langsung

// ✅ Buat array baru
const withNewItem = [...items, 4];
const withoutSecond = items.filter((_, i) => i !== 1);

// ✅ Object.freeze untuk constants
const CONFIG = Object.freeze({
  API_URL: "https://api.example.com",
  TIMEOUT: 5000,
});
```

**Kenapa?** Immutability mencegah bug side-effect yang sulit dilacak, terutama di aplikasi React/state management.

---

## 16. Penulisan Komentar — Jelaskan "Kenapa", Bukan "Apa"

```js
// ❌ Komentar yang tidak berguna — menjelaskan yang sudah jelas
const age = 25; // set age to 25
users.filter((u) => u.active); // filter active users

// ✅ Jelaskan alasan / konteks
// Timeout 3x lipat dari normal karena API payment gateway sering lambat
const PAYMENT_TIMEOUT = 15000;

// Urut berdasarkan createdAt DESC agar data terbaru muncul pertama.
// Tidak menggunakan DB sort karena data sudah di-cache di memory.
const sorted = items.sort((a, b) => b.createdAt - a.createdAt);
```

**Aturan:**
- Tulis komentar untuk menjelaskan **kenapa**, bukan **apa**
- Kode yang baik seharusnya bisa dibaca tanpa komentar
- Gunakan JSDoc untuk function publik yang di-export

```js
/**
 * Hitung total harga dengan diskon dan pajak.
 * @param {number} subtotal - Harga sebelum diskon
 * @param {number} discountPercent - Persentase diskon (0-100)
 * @param {number} taxRate - Rate pajak (contoh: 0.11 untuk PPN 11%)
 * @returns {number} Total harga akhir
 */
function calculateTotal(subtotal, discountPercent, taxRate) {
  const discounted = subtotal * (1 - discountPercent / 100);
  return discounted * (1 + taxRate);
}
```

---

## 17. Hindari Magic Numbers & Strings

```js
// ❌ Magic number — apa artinya 86400000?
setTimeout(cleanup, 86400000);

if (user.role === "adm") {  // ❌ magic string — typo tidak terdeteksi
  grantAccess();
}

// ✅ Gunakan named constants
const ONE_DAY_MS = 24 * 60 * 60 * 1000;
setTimeout(cleanup, ONE_DAY_MS);

const ROLES = {
  ADMIN: "admin",
  USER: "user",
  VIEWER: "viewer",
};

if (user.role === ROLES.ADMIN) {
  grantAccess();
}
```

---

## 18. DRY (Don't Repeat Yourself) — Buat Kode Reusable

Jangan menulis logic yang sama berulang kali. Kumpulkan ke dalam function, helper, atau service.

### Kapan Harus Extract ke Function?

**Rule of Three:** Jika sebuah logic ditulis 3 kali di tempat berbeda, **wajib** dipindah ke helper/utility function. Jika 2 kali, pertimbangkan untuk dipindah jika logic-nya cukup kompleks.

```js
// ❌ Redundant (Berulang)
function processUserOrder(order) {
  const tax = order.amount * 0.11;
  const total = order.amount + tax;
  return total;
}

function processSubscription(sub) {
  const tax = sub.amount * 0.11;
  const total = sub.amount + tax;
  return total;
}

// ✅ Reusable Helper
function calculateTotalWithTax(amount) {
  const TAX_RATE = 0.11;
  return amount * (1 + TAX_RATE);
}

function processUserOrder(order) {
  return calculateTotalWithTax(order.amount);
}

function processSubscription(sub) {
  return calculateTotalWithTax(sub.amount);
}
```

### Struktur Folder untuk Reusable Code

Pisahkan kode berdasarkan fungsi dan tanggung jawabnya:

| Folder / File | Deskripsi | Contoh Modul |
|---|---|---|
| `utils/` | Function murni (pure function) untuk perhitungan, format string/date. Tidak ada side-effect. | `format-date.js`, `currency.js` |
| `helpers/` | Wrapper atau helper yang mungkin memiliki side-effect atau state ringan. | `logger.js`, `local-storage.js` |
| `services/` | Logic bisnis spesifik yang berinteraksi dengan eksternal (API / Database). | `auth-service.js`, `payment.js` |
| `constants/` | Pusat variabel konstan yang digunakan di seluruh aplikasi (mencegah magic string). | `roles.js`, `endpoints.js` |

### Service Pattern untuk API Call

Jangan memanggil API atau database secara tersebar di banyak file. Buat layer Service agar mudah di-*maintain* dan di-reuse.

```js
// ❌ Tersebar di mana-mana
async function handleLogin() {
  const res = await fetch('https://api.myapp.com/login', { /* ... */ });
}

// ✅ Kumpulkan di single location (Service)
// src/services/auth-service.js
import { apiClient } from '../utils/api-client.js';

export const AuthService = {
  login: (credentials) => apiClient.post('/login', credentials),
  logout: () => apiClient.post('/logout'),
  getCurrentUser: () => apiClient.get('/me'),
};

// Penggunaan menjadi sangat bersih
import { AuthService } from '../services/auth-service.js';
const user = await AuthService.getCurrentUser();
```

---

## 19. Checklist Sebelum Push Code

- [ ] Tidak ada `var` — hanya `const` dan `let`
- [ ] Tidak ada `console.log` debugging yang tertinggal
- [ ] Semua async function punya error handling (try/catch)
- [ ] Tidak ada object/array yang dimutasi langsung
- [ ] Magic numbers sudah diganti dengan named constants
- [ ] Nama variable dan function deskriptif dan konsisten
- [ ] Tidak ada nested ternary atau deeply nested if-else
- [ ] Komentar menjelaskan "kenapa", bukan "apa"
- [ ] Menggunakan ES Modules (`import`/`export`), bukan CommonJS
- [ ] Array diproses dengan array methods, bukan loop manual
- [ ] Logic yang digunakan berulang sudah dipindah ke helper/utility

---

## Quick Reference — Do & Don't

```js
// ❌ DON'T
var x = 10;                           // pakai var
user.name = "Jane";                   // mutasi langsung
"Hello " + name + "!";               // string concatenation
if (data) { if (data.user) { ... } } // nested conditions
setTimeout(fn, 86400000);            // magic number
try { save(); } catch(e) {}          // menelan error
const d = getData();                  // nama tidak jelas

// ✅ DO
const x = 10;                         // pakai const/let
const updated = { ...user, name: "Jane" }; // immutable update
`Hello ${name}!`;                     // template literal
if (!data?.user) return null;         // guard clause + optional chaining
setTimeout(fn, ONE_DAY_MS);          // named constant
try { save(); } catch(e) { throw e; } // handle error
const userData = fetchUserData();      // nama deskriptif
```

---

> **Catatan:** File ini adalah living document. Update sesuai kebutuhan tim dan project.
