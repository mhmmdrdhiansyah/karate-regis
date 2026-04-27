# Tailwind CSS Best Practices

> Panduan ini ditulis dengan bahasa high-level agar mudah dipahami oleh junior programmer maupun AI model. Gunakan file ini sebagai referensi saat membangun front-end dengan Tailwind CSS.

---

## 1. Setup — Gunakan Tailwind v4 (CSS-First Config)

Tailwind v4 tidak lagi menggunakan `tailwind.config.js`. Semua konfigurasi dilakukan langsung di CSS.

```css
/* ✅ Tailwind v4 — cukup satu baris import */
@import "tailwindcss";
```

```css
/* ❌ Jangan — ini syntax v3, sudah deprecated */
@tailwind base;
@tailwind components;
@tailwind utilities;
```

**Jika project masih v3**, tetap gunakan `tailwind.config.js`. Tapi untuk project baru, **selalu mulai dengan v4**.

---

## 2. Utility-First — Tulis Style Langsung di Class

Prinsip utama Tailwind: **jangan buat CSS custom kalau utility sudah cukup**. Tulis styling langsung di elemen HTML/JSX.

```html
<!-- ✅ Utility-first — langsung terlihat apa yang terjadi -->
<button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-md hover:bg-indigo-500 active:bg-indigo-700">
  Simpan
</button>

<!-- ❌ Jangan — class custom yang abstrak dan harus dicari di CSS -->
<button class="btn-primary">Simpan</button>
```

**Kenapa?** Dengan utility-first:
- Tidak perlu invent nama class
- Tidak perlu switch antara file HTML dan CSS
- Styling terlihat langsung di tempat digunakan
- Tidak ada CSS unused yang menumpuk

---

## 3. Urutan Class — Konsisten dan Rapi

Ikuti urutan ini agar class mudah dibaca:

```
Layout → Sizing → Spacing → Typography → Visual → State → Animation
```

```html
<!-- ✅ Urutan yang konsisten -->
<div class="flex items-center gap-4 w-full max-w-md p-6 text-sm font-medium text-gray-700 bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow">
  <!-- Layout → Size → Spacing → Typography → Visual → State → Animation -->
</div>
```

**Tips:** Gunakan **Prettier plugin** (`prettier-plugin-tailwindcss`) agar urutan class otomatis konsisten.

```bash
npm install -D prettier-plugin-tailwindcss
```

---

## 4. Responsive Design — Mobile-First

Tailwind menggunakan pendekatan **mobile-first**. Style tanpa prefix berlaku di semua ukuran layar. Prefix berlaku dari breakpoint tersebut **ke atas**.

```html
<!-- ✅ Mobile-first: stack di mobile, grid di tablet, 3 kolom di desktop -->
<div class="flex flex-col sm:grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
  <!-- content -->
</div>
```

### Breakpoint Bawaan

| Prefix | Min Width | Keterangan |
|--------|-----------|------------|
| *(none)* | 0px | Default / mobile |
| `sm:` | 640px | Tablet portrait |
| `md:` | 768px | Tablet landscape |
| `lg:` | 1024px | Desktop |
| `xl:` | 1280px | Desktop besar |
| `2xl:` | 1536px | Layar sangat besar |

**Aturan:**
- Selalu desain mobile dulu, lalu tambahkan prefix untuk layar lebih besar
- Jangan gunakan lebih dari 2-3 breakpoint per elemen — kalau terlalu banyak, extract ke component

---

## 5. Dark Mode — Gunakan `dark:` Prefix

```html
<!-- ✅ Dark mode yang elegan -->
<div class="bg-white text-gray-900 dark:bg-gray-900 dark:text-gray-100">
  <h2 class="text-gray-800 dark:text-white">Judul</h2>
  <p class="text-gray-500 dark:text-gray-400">Deskripsi konten.</p>
</div>
```

### Setup Dark Mode

```css
/* Default: mengikuti preferensi sistem (prefers-color-scheme) */
@import "tailwindcss";

/* Jika ingin kontrol manual via class di <html>: */
@import "tailwindcss";
@custom-variant dark (&:where(.dark, .dark *));
```

```html
<!-- Kontrol manual -->
<html class="dark">
  <body class="bg-white dark:bg-gray-950">
    <!-- ... -->
  </body>
</html>
```

**Tips:** Selalu sediakan warna dark mode untuk setiap warna light yang kamu tulis. Pattern mudahnya:
- Background: `bg-white dark:bg-gray-900`
- Text utama: `text-gray-900 dark:text-white`
- Text sekunder: `text-gray-500 dark:text-gray-400`
- Border: `border-gray-200 dark:border-gray-700`

---

## 6. Theming — Kustomisasi Design Token

### Tailwind v4 — Gunakan `@theme`

```css
@import "tailwindcss";

@theme {
  /* Warna brand */
  --color-brand-50: oklch(0.97 0.02 250);
  --color-brand-100: oklch(0.93 0.04 250);
  --color-brand-500: oklch(0.55 0.19 250);
  --color-brand-600: oklch(0.48 0.19 250);
  --color-brand-700: oklch(0.40 0.17 250);

  /* Font */
  --font-sans: "Inter", "sans-serif";
  --font-display: "Outfit", "sans-serif";

  /* Border radius */
  --radius-card: 0.75rem;
  --radius-button: 0.5rem;

  /* Shadow */
  --shadow-card: 0 4px 6px -1px rgb(0 0 0 / 0.07), 0 2px 4px -2px rgb(0 0 0 / 0.07);

  /* Animasi */
  --ease-smooth: cubic-bezier(0.4, 0, 0.2, 1);
}
```

**Penggunaan:**

```html
<div class="font-display rounded-card bg-brand-500 text-white shadow-card">
  Kartu dengan design token custom
</div>
```

### Tailwind v3 — Gunakan `tailwind.config.js`

```js
// tailwind.config.js (hanya untuk v3)
module.exports = {
  theme: {
    extend: {
      colors: {
        brand: {
          500: "#6366f1",
          600: "#4f46e5",
        },
      },
      fontFamily: {
        sans: ["Inter", "sans-serif"],
      },
    },
  },
};
```

**Aturan theming:**
- Jangan hardcode warna hex langsung di class → definisikan di theme
- Gunakan token semantik: `brand`, `surface`, `muted` — bukan nama warna fisik
- Semua warna, font, radius, dan shadow harus melalui design token

---

## 7. Reusable Styles — Kapan Extract, Kapan Tidak

### ❌ Jangan pakai `@apply` sembarangan

```css
/* ❌ Ini mengalahkan tujuan utility-first */
.btn-primary {
  @apply rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-md hover:bg-indigo-500;
}
```

### ✅ Gunakan component (React/Vue) untuk reusability

```tsx
// ✅ Extract ke component — jauh lebih baik daripada @apply
type ButtonProps = {
  children: React.ReactNode;
  variant?: "primary" | "secondary" | "danger";
  size?: "sm" | "md" | "lg";
  onClick?: () => void;
};

function Button({ children, variant = "primary", size = "md", onClick }: ButtonProps) {
  const base = "inline-flex items-center justify-center font-semibold rounded-lg transition-colors";

  const variants = {
    primary: "bg-indigo-600 text-white hover:bg-indigo-500 active:bg-indigo-700",
    secondary: "bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300",
    danger: "bg-red-600 text-white hover:bg-red-500",
  };

  const sizes = {
    sm: "px-3 py-1.5 text-xs",
    md: "px-4 py-2 text-sm",
    lg: "px-6 py-3 text-base",
  };

  return (
    <button className={`${base} ${variants[variant]} ${sizes[size]}`} onClick={onClick}>
      {children}
    </button>
  );
}
```

### Kapan `@apply` boleh?

```css
/* ✅ Boleh untuk base styling yang berlaku global */
@layer base {
  body {
    @apply bg-white text-gray-900 dark:bg-gray-950 dark:text-gray-100;
  }

  h1, h2, h3 {
    @apply font-display tracking-tight;
  }
}

/* ✅ Boleh untuk styling third-party component yang tidak bisa dikontrol class-nya */
.prose code {
  @apply rounded bg-gray-100 px-1.5 py-0.5 text-sm dark:bg-gray-800;
}
```

**Aturan simpel:**
- UI yang kamu kontrol → extract ke **component** (React/Vue)
- Base styling global → boleh `@apply` di `@layer base`
- Third-party yang tidak bisa di-customize → boleh `@apply`
- Selebihnya → tulis utility langsung

---

## 8. Spacing & Sizing — Gunakan Skala yang Konsisten

Tailwind punya spacing scale yang sudah dipikirkan matang. **Selalu gunakan skala ini**, jangan pakai arbitrary value sembarangan.

```html
<!-- ✅ Gunakan spacing scale bawaan -->
<div class="p-4 mb-6 gap-3">
  <!-- p-4 = 1rem, mb-6 = 1.5rem, gap-3 = 0.75rem -->
</div>

<!-- ❌ Hindari arbitrary value jika ada padanannya -->
<div class="p-[17px] mb-[23px] gap-[11px]">
  <!-- Angka-angka random tanpa alasan -->
</div>
```

### Spacing Scale yang Sering Dipakai

| Class | Nilai | Kegunaan Umum |
|-------|-------|---------------|
| `1` | 0.25rem (4px) | Spacing sangat kecil |
| `2` | 0.5rem (8px) | Spacing kecil (gap icon-text) |
| `3` | 0.75rem (12px) | Padding compact |
| `4` | 1rem (16px) | Padding standar |
| `6` | 1.5rem (24px) | Padding section |
| `8` | 2rem (32px) | Spacing antar section |
| `12` | 3rem (48px) | Spacing besar |
| `16` | 4rem (64px) | Spacing section besar |

**Kapan arbitrary value (`[...]`) boleh?**
- Saat butuh nilai presisi untuk match design Figma → `w-[237px]`
- Saat butuh nilai yang memang tidak ada di scale → `top-[calc(100%-2rem)]`
- **Tapi selalu prioritaskan scale bawaan dulu**

---

## 9. Animasi & Transisi — Buat UI Terasa Hidup

```html
<!-- ✅ Transisi halus pada hover -->
<button class="bg-indigo-600 text-white rounded-lg px-4 py-2 transition-all duration-200 ease-out hover:bg-indigo-500 hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0 active:shadow-md">
  Klik Saya
</button>

<!-- ✅ Animasi masuk untuk card -->
<div class="animate-in fade-in slide-in-from-bottom-4 duration-300">
  <CardContent />
</div>
```

### Pattern Animasi yang Sering Dipakai

```html
<!-- Hover scale -->
<div class="transition-transform duration-200 hover:scale-105">

<!-- Fade + shadow on hover -->
<div class="transition-all duration-200 hover:shadow-xl hover:opacity-90">

<!-- Skeleton loading -->
<div class="h-4 w-3/4 animate-pulse rounded bg-gray-200 dark:bg-gray-700"></div>

<!-- Spin untuk loading indicator -->
<svg class="h-5 w-5 animate-spin text-indigo-600"><!-- ... --></svg>
```

**Aturan:**
- Selalu gunakan `transition-*` untuk hover/focus — jangan biarkan perubahan tanpa animasi
- Durasi 150-300ms untuk interaksi kecil, 300-500ms untuk transisi besar
- Gunakan `ease-out` untuk masuk, `ease-in` untuk keluar
- Jangan animate `width`, `height`, atau `top/left` — gunakan `transform` dan `opacity` untuk performa

---

## 10. Pattern Layout yang Sering Dipakai

### Centering

```html
<!-- ✅ Center horizontal & vertikal (flex) -->
<div class="flex items-center justify-center min-h-screen">
  <div>Konten di tengah</div>
</div>

<!-- ✅ Center horizontal & vertikal (grid — lebih simple) -->
<div class="grid place-items-center min-h-screen">
  <div>Konten di tengah</div>
</div>
```

### Container dengan Max Width

```html
<!-- ✅ Container standar -->
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
  <!-- Content -->
</div>
```

### Sidebar Layout

```html
<!-- ✅ Sidebar + content responsive -->
<div class="flex min-h-screen">
  <aside class="hidden w-64 shrink-0 border-r bg-gray-50 lg:block dark:border-gray-800 dark:bg-gray-900">
    <!-- Sidebar -->
  </aside>
  <main class="flex-1 p-6">
    <!-- Main content -->
  </main>
</div>
```

### Stack (Vertikal) & Row (Horizontal)

```html
<!-- ✅ Vertical stack dengan gap konsisten -->
<div class="flex flex-col gap-4">
  <div>Item 1</div>
  <div>Item 2</div>
  <div>Item 3</div>
</div>

<!-- ✅ Horizontal row dengan alignment -->
<div class="flex items-center gap-3">
  <img class="h-10 w-10 rounded-full" src="..." alt="" />
  <div>
    <p class="font-medium text-gray-900 dark:text-white">Nama</p>
    <p class="text-sm text-gray-500">Deskripsi</p>
  </div>
</div>
```

---

## 11. Class Merging — Hindari Konflik

Saat membuat component yang menerima `className` dari luar, **class bisa konflik**. Gunakan library `tailwind-merge` untuk mengatasi ini.

```bash
npm install tailwind-merge clsx
```

```ts
// lib/cn.ts — utility wajib untuk setiap project Tailwind + React
import { clsx, type ClassValue } from "clsx";
import { twMerge } from "tailwind-merge";

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}
```

```tsx
// ✅ Penggunaan cn() — class dari luar bisa override class default
function Card({ className, children }: { className?: string; children: React.ReactNode }) {
  return (
    <div className={cn("rounded-xl bg-white p-6 shadow-md dark:bg-gray-800", className)}>
      {children}
    </div>
  );
}

// Class dari luar akan menang jika ada konflik
<Card className="p-8 bg-indigo-50">
  {/* p-8 menggantikan p-6, bg-indigo-50 menggantikan bg-white */}
</Card>
```

**Aturan:** Selalu gunakan `cn()` di component yang menerima prop `className`. Jangan string concat biasa.

---

## 12. Aksesibilitas (a11y) — Jangan Lupa

```html
<!-- ✅ Focus ring yang jelas -->
<button class="rounded-lg bg-indigo-600 px-4 py-2 text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
  Aksi
</button>

<!-- ✅ Screen reader only text -->
<button class="p-2">
  <svg class="h-5 w-5" aria-hidden="true"><!-- icon --></svg>
  <span class="sr-only">Tutup menu</span>
</button>

<!-- ✅ Disabled state yang jelas -->
<button class="bg-indigo-600 text-white disabled:cursor-not-allowed disabled:opacity-50" disabled>
  Tidak tersedia
</button>
```

**Aturan a11y:**
- Semua elemen interaktif harus punya `focus-visible:ring-*`
- Icon button wajib punya text (`sr-only` atau `aria-label`)
- State `disabled` harus terlihat berbeda
- Warna teks dan background harus punya kontras cukup (minimal 4.5:1)

---

## 13. Warna — Gunakan Palet yang Harmonis

### Jangan Hardcode, Gunakan Skala Warna

```html
<!-- ❌ Jangan — warna random tanpa sistem -->
<div class="bg-[#1a2b3c] text-[#f0f0f0]">

<!-- ✅ Gunakan skala warna Tailwind yang sudah teruji -->
<div class="bg-gray-900 text-gray-100">
```

### Pattern Warna untuk UI

| Elemen | Light | Dark |
|--------|-------|------|
| Background utama | `bg-white` | `dark:bg-gray-950` |
| Background card | `bg-white` | `dark:bg-gray-900` |
| Background subtle | `bg-gray-50` | `dark:bg-gray-800` |
| Text utama | `text-gray-900` | `dark:text-white` |
| Text sekunder | `text-gray-500` | `dark:text-gray-400` |
| Text muted | `text-gray-400` | `dark:text-gray-500` |
| Border | `border-gray-200` | `dark:border-gray-700` |
| Primary action | `bg-indigo-600` | `dark:bg-indigo-500` |
| Danger | `bg-red-600` | `dark:bg-red-500` |
| Success | `bg-emerald-600` | `dark:bg-emerald-500` |
| Warning | `bg-amber-500` | `dark:bg-amber-400` |

---

## 14. Performance — Keep CSS Bundle Kecil

Tailwind secara default hanya menghasilkan CSS untuk class yang benar-benar dipakai. Tapi ada beberapa tips tambahan:

```
✅ DO:
- Tulis class lengkap: `bg-red-500` (bukan dynamic: `bg-${color}-500`)
- Gunakan safelist hanya jika benar-benar perlu

❌ DON'T:
- Jangan construct class secara dinamis
- Jangan simpan class di variable yang tidak terdeteksi scanner
```

```tsx
// ❌ Class dinamis — tidak terdeteksi oleh Tailwind
const color = "red";
<div className={`bg-${color}-500`} /> // ❌ bg-red-500 tidak akan di-generate

// ✅ Gunakan mapping object
const colorMap = {
  red: "bg-red-500",
  blue: "bg-blue-500",
  green: "bg-green-500",
} as const;

<div className={colorMap[color]} /> // ✅ semua class tertulis lengkap
```

---

## 15. Checklist Sebelum Push Code

- [ ] Semua warna menggunakan skala Tailwind atau design token dari `@theme` — tidak ada hex random
- [ ] Dark mode sudah di-handle untuk semua elemen yang terlihat
- [ ] Layout responsive minimal untuk mobile dan desktop (`sm:` atau `lg:`)
- [ ] Semua elemen interaktif punya `hover:`, `focus-visible:`, dan `disabled:` state
- [ ] Icon button punya text untuk screen reader (`sr-only` atau `aria-label`)
- [ ] Class tidak di-construct secara dinamis (string interpolation)
- [ ] Component reusable menggunakan `cn()` (tailwind-merge) untuk class merging
- [ ] Tidak ada arbitrary value (`[...]`) yang seharusnya bisa pakai scale bawaan
- [ ] Prettier plugin Tailwind sudah terpasang dan berjalan
- [ ] Transisi/animasi halus untuk semua state change yang terlihat user

---

## Quick Reference — Do & Don't

```html
<!-- ❌ DON'T -->
<div class="btn-primary">                        <!-- class custom abstrak -->
<div class="p-[17px] mt-[23px]">                 <!-- arbitrary tanpa alasan -->
<div class="bg-[#1a2b3c]">                       <!-- hardcode hex -->
<div class={`bg-${color}-500`}>                   <!-- dynamic class -->
<button class="bg-blue-500">                      <!-- tanpa hover/focus -->
<button><svg /></button>                          <!-- icon tanpa label -->

<!-- ✅ DO -->
<div class="rounded-lg bg-indigo-600 px-4 py-2">              <!-- utility langsung -->
<div class="p-4 mt-6">                                        <!-- gunakan scale -->
<div class="bg-brand-500">                                     <!-- design token -->
<div class={colorMap[color]}>                                  <!-- object mapping -->
<button class="bg-blue-500 hover:bg-blue-400 focus-visible:ring-2">  <!-- dengan state -->
<button><svg /><span class="sr-only">Close</span></button>    <!-- dengan label -->
```

---

> **Catatan:** File ini adalah living document. Update sesuai kebutuhan tim dan project.
