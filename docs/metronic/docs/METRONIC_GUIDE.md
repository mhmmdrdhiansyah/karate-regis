# Dokumentasi & Patokan Pengembangan Sistem Berbasis Metronic Template

## Context

Dokumentasi ini dibuat berdasarkan analisis terhadap Metronic Template v8.3.0 yang dimiliki. Tujuannya adalah menyediakan referensi/panduan untuk membangun sistem selanjutnya dengan pola yang konsisten.

---

## 1. Struktur Direktori Proyek

### Organisasi Utama
```
dist/
├── assets/                    # Semua aset statis
│   ├── css/                   # Stylesheet bundles
│   ├── js/                    # JavaScript bundles
│   ├── media/                 # Gambar, ikon, logo
│   └── plugins/               # Pustaka pihak ketiga
├── layout/                    # Layout & partials
│   ├── partials/              # Komponen layout reusable
│   └── _default.html          # Layout default
├── pages/                     # Halaman umum
├── apps/                      # Halaman aplikasi spesifik
├── dashboards/                # Variasi dashboard
├── authentication/            # Halaman auth
├── account/                   # Manajemen akun
├── widgets/                   # Komponen widget
└── modals/                    # Modal/popup templates
```

---

## 2. Organisasi Aset CSS

### File Naming Convention
- **Bundle**: `[name].bundle.css` → file terkompilasi
- **RTL**: `[name].bundle.rtl.css` → untuk bahasa Arab/rtl
- **Dark**: `[name].dark.bundle.css` → tema gelap

### Struktur CSS
```
assets/css/
├── style.bundle.css           # CSS utama (~329KB)
├── style.bundle.rtl.css       # Versi RTL
└── style.bundle.css.map       # Source map untuk debugging
```

### CSS Variables (Custom Properties)
```css
:root {
    /* Warna */
    --bs-primary: #009EF7;
    --bs-success: #50CD89;
    --bs-info: #7239EA;
    --bs-warning: #FFC700;
    --bs-danger: #F1416C;
    --bs-dark: #181C32;

    /* Typography */
    --bs-font-sans-serif: Poppins, Helvetica, sans-serif;
    --bs-body-color: #181C32;

    /* Spacing */
    --bs-spacer: 1rem;
}
```

### Class Naming Patterns
- **Prefix-based**: `menu-`, `aside-`, `toolbar-`, `header-`, `footer-`
- **State modifiers**: `.aside-light`, `.aside-hoverable`, `.header-fixed`
- **Utilities**: Bootstrap utilities (`d-flex`, `mt-4`, `p-3`, dll)

### Data Attributes untuk Konfigurasi
```html
<div data-kt-app-layout="dark-sidebar"
     data-kt-app-header-fixed="true"
     data-kt-app-sidebar-enabled="true"
     data-kt-app-sidebar-hoverable="true">
```

---

## 3. Organisasi JavaScript

### Struktur File JS
```
assets/js/
├── scripts.bundle.js          # Core framework (~80KB)
├── plugins.bundle.js          # Pustaka vendor
├── widgets.bundle.js          # Widget scripts
└── custom/                    # Custom modules
    ├── account/               # Fitur akun
    ├── apps/                  # Modul aplikasi
    ├── authentication/        # Auth features
    ├── pages/                 # Page-specific JS
    ├── modals/                # Modal logic
    └── utilities/             # Utility functions
```

### Pola Komponen JavaScript
```javascript
"use strict";

var ComponentName = function() {
    // Private variables
    var element;
    var form;

    // Private functions
    var handleValidation = function() {
        // Implementation
    };

    var handleSubmit = function() {
        // Implementation
    };

    return {
        init: function() {
            element = document.querySelector('#component');
            handleValidation();
            handleSubmit();
        }
    };
}();

// Initialize on DOM ready
KTUtil.onDOMContentLoaded(function() {
    ComponentName.init();
});
```

### Utility Functions (KTUtil)
```javascript
// DOM Manipulation
KTUtil.on(element, 'click', handler);
KTUtil.css(element, 'property', 'value');
KTUtil.data(element).set('key', value);

// Event Handler
KTEventHandler.trigger(element, 'kt.blockui.block', instance);
KTEventHandler.on(element, 'custom.event', callback);

// Cookie
KTCookie.set('key', 'value');
KTCookie.get('key');
```

### Pustaka Pihak Ketiga yang Digunakan
- **jQuery 3.6.0** - DOM manipulation
- **Bootstrap 5** - UI components
- **ApexCharts** - Grafik/data visualization
- **FullCalendar** - Kalender
- **DataTable** - Tabel lanjutan
- **FormValidation** - Validasi form
- **Quill** - Rich text editor
- **Moment.js** - Date manipulation
- **amCharts 5** - Maps dan charts

---

## 4. Struktur HTML & Layout

### Template Dasar (index.html)
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <base href=""/>
    <title>Page Title</title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link rel="shortcut icon" href="assets/media/logos/favicon.ico"/>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700"/>

    <!-- Vendor CSS (per halaman) -->
    <link href="assets/plugins/custom/[plugin]/[plugin].bundle.css" rel="stylesheet"/>

    <!-- Global CSS Bundle (semua halaman) -->
    <link href="assets/plugins/global/plugins.bundle.css" rel="stylesheet"/>
    <link href="assets/css/style.bundle.css" rel="stylesheet"/>
</head>
<body id="kt_app_body"
      data-kt-app-layout="dark-sidebar"
      data-kt-app-header-fixed="true"
      data-kt-app-sidebar-enabled="true"
      data-kt-app-sidebar-fixed="true"
      data-kt-app-toolbar-enabled="true"
      class="app-default">

    <!-- Layout partials di-inject di sini -->
    <!--layout-partial:layout/_default.html-->

    <!-- JS Global Bundle -->
    <script>
        var hostUrl = "assets/";
    </script>
    <script src="assets/plugins/global/plugins.bundle.js"></script>
    <script src="assets/js/scripts.bundle.js"></script>

    <!-- JS Vendor (per halaman) -->
    <script src="assets/plugins/custom/[plugin]/[plugin].bundle.js"></script>

    <!-- JS Custom (per halaman) -->
    <script src="assets/js/custom/[feature]/[script].js"></script>
</body>
</html>
```

### Layout Default Structure
```
div.app-root (kt_app_root)
└── div.app-page (kt_app_page)
    └── header (kt_app_header)
    └── div.app-wrapper (kt_app_wrapper)
        └── aside (kt_app_sidebar)
        └── div.app-main (kt_app_main)
            └── toolbar (kt_app_toolbar)
            └── content (kt_app_content)
            └── footer (kt_app_footer)
```

### Layout Partials
```
layout/partials/
├── _header.html              # Header section
├── _sidebar.html             # Sidebar navigation
├── _toolbar.html             # Toolbar/sub-header
├── _content.html             # Main content area
├── _footer.html              # Footer section
├── _page-title.html          # Page title breadcrumb
├── header/
│   ├── _navbar.html          # Top navigation bar
│   └── _menu/
│       ├── _menu.html        # Menu container
│       └── __*.html          # Menu items (underscore = nested)
└── sidebar/
    ├── _logo.html            # Sidebar logo
    ├── _menu.html            # Sidebar menu
    └── _footer.html           # Sidebar footer
```

---

## 5. Patokan Pengembangan Fitur Baru

### Langkah-langkah Membuat Halaman Baru

1. **Buat file HTML** di folder yang sesuai:
   - `pages/` → halaman umum
   - `apps/` → aplikasi spesifik
   - `authentication/` → halaman auth
   - `account/` → halaman akun

2. **Ikuti template HTML** dengan:
   - Meta tags lengkap
   - CSS bundles (global + plugin-specific)
   - Layout configuration via data attributes
   - JS bundles (global + vendor + custom)

3. **Buat JavaScript custom** di `assets/js/custom/[folder]/[feature].js`:
   ```javascript
   "use strict";
   var FeatureName = function() {
       return {
           init: function() {
               // Initialization code
           }
       };
   }();
   KTUtil.onDOMContentLoaded(function() {
       FeatureName.init();
   });
   ```

### Konvensi Naming

| Jenis | Format | Contoh |
|-------|--------|--------|
| Folder | kebab-case | `user-management/` |
| File HTML | kebab-case | `user-list.html` |
| File JS | kebab-case | `user-list.js` |
| CSS Class | prefix-based | `btn-primary`, `app-sidebar` |
| JS Variable | camelCase | `userList`, `handleClick` |
| JS Component | PascalCase | `UserManager`, `DataTable` |

### Data Attributes Pattern
```html
<!-- Component initialization -->
<div data-kt-component="true"
     data-kt-option="value">
</div>

<!-- Drawer/Modal trigger -->
<button data-kt-drawer-target="#drawer"
        data-kt-drawer-activate="{default: true, lg: false}">
    Open
</button>

<!-- Menu configuration -->
<div data-kt-menu="true"
     data-kt-menu-trigger="click"
     data-kt-menu-placement="bottom">
</div>
```

---

## 6. Best Practices

### CSS
1. Gunakan CSS variables untuk konsistensi tema
2. Ikuti hierarchy: global → plugin → page-specific
3. Gunakan prefix `kt-` untuk custom class
4. Manfaatkan Bootstrap utilities untuk layout cepat

### JavaScript
1. Selalu gunakan `"use strict"`
2. Bungkus komponen dalam IIFE
3. Expose hanya method `init()` sebagai public API
4. Gunakan `KTUtil.onDOMContentLoaded()` untuk inisialisasi
5. Event-driven architecture untuk komunikasi antar komponen

### HTML
1. Gunakan layout partials untuk komponen reusable
2. Konfigurasi layout via data attributes di body
3. Komentar `<!--begin::-->` dan `<!--end::-->` untuk sectioning
4. Prefix IDs dengan `kt_app_` untuk konsistensi

### Asset Loading
1. Load CSS di `<head>`
2. Load JS sebelum `</body>`
3. Global bundles → vendor bundles → custom scripts
4. Lazy load plugin-specific assets

---

## 7. Komponen Yang Tersedia

### UI Components
- Buttons, Badges, Pills
- Cards, Stats, Widgets
- Forms (input, select, checkbox, radio, file upload)
- Tables (datatables)
- Modals & Drawers
- Dropdowns & Menus
- Tabs & Pills
- Accordion
- Toasts & Alerts
- Progress bars
- Spinners & loaders

### Advanced Components
- FullCalendar (kalender)
- ApexCharts (grafik)
- amCharts (maps, advanced charts)
- CKEditor/Quill (rich text)
- DataTables (tabel interaktif)
- FormValidation
- Cropperjs (image crop)
- Drag&Drop
- Chat UI
- File Manager

### Layout Variations
- Dark/Light sidebar
- Fixed/static header
- Toolbar enabled/disabled
- Sidebar hoverable
- RTL support
- Compact/extended modes

---

## 8. Roadmap Implementasi Sistem

### Fase 1: Setup Dasar
- [ ] Copy structure template ke proyek baru
- [ ] Konfigurasi base URL dan asset path
- [ ] Setup routing (sesuai backend)
- [ ] Integrasi dengan backend API

### Fase 2: Layout & Navigation
- [ ] Sesuaikan brand (logo, colors, fonts)
- [ ] Konfigurasi menu sidebar
- [ ] Setup header/toolbar
- [ ] Implementasi auth layout

### Fase 3: Core Features
- [ ] Dashboard utama
- [ ] User management
- [ ] Role/permission
- [ ] Settings

### Fase 4: Advanced Features
- [ ] Notification system
- [ ] File management
- [ ] Reporting/analytics
- [ ] Integrasi third-party services

---

## File Referensi Penting

| File | Deskripsi |
|------|-----------|
| `index.html` | Template halaman utama |
| `layout/_default.html` | Layout default |
| `layout/partials/_sidebar.html` | Sidebar navigation |
| `layout/partials/header/_menu/` | Menu definitions |
| `assets/css/style.bundle.css` | CSS utama |
| `assets/js/scripts.bundle.js` | JS framework core |

---

## Catatan Penting

1. **License**: Metronic adalah template berbayar dari Keenthemes
2. **Versi**: v8.3.0 menggunakan Bootstrap 5
3. **Support**: https://keenthemes.com/metronic
4. **Framework agnostic**: Dapat diintegrasikan dengan backend apa pun

---

## 9. Template Kode (Cheat Sheet)

### Template Halaman Baru (HTML)
```html
<!DOCTYPE html>
<html lang="id">
<head>
    <base href=""/>
    <title>Nama Halaman - Nama Aplikasi</title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="description" content="Deskripsi halaman"/>
    <link rel="shortcut icon" href="assets/media/logos/favicon.ico"/>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700"/>

    <!-- Vendor CSS (jika perlu plugin khusus) -->
    <!-- <link href="assets/plugins/custom/[plugin]/[plugin].bundle.css" rel="stylesheet"/> -->

    <!-- Global CSS Bundle (WAJIB semua halaman) -->
    <link href="assets/plugins/global/plugins.bundle.css" rel="stylesheet"/>
    <link href="assets/css/style.bundle.css" rel="stylesheet"/>
</head>
<body id="kt_app_body"
      data-kt-app-layout="dark-sidebar"
      data-kt-app-header-fixed="true"
      data-kt-app-sidebar-enabled="true"
      data-kt-app-sidebar-fixed="true"
      data-kt-app-toolbar-enabled="true"
      class="app-default">

<!--layout-partial:layout/_default.html-->

    <!-- Page Content -->
    <!--begin::Content-->
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Judul Halaman</h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">Home</li>
                        <li class="breadcrumb-item"><span class="bullet text-gray-400 fw-bold mx-1"></span></li>
                        <li class="breadcrumb-item text-muted">Module</li>
                        <li class="breadcrumb-item"><span class="bullet text-gray-400 fw-bold mx-1"></span></li>
                        <li class="breadcrumb-item text-dark">Halaman</li>
                    </ul>
                </div>
            </div>
        </div>
        <!--end::Toolbar-->

        <!--begin::Post-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <!-- Konten halaman di sini -->
            </div>
        </div>
        <!--end::Post-->
    </div>
    <!--end::Content-->

    <!--begin::Javascript-->
    <script>var hostUrl = "assets/";</script>

    <!-- Global Javascript Bundle (WAJIB semua halaman) -->
    <script src="assets/plugins/global/plugins.bundle.js"></script>
    <script src="assets/js/scripts.bundle.js"></script>

    <!-- Vendor JS (jika perlu plugin khusus) -->
    <!-- <script src="assets/plugins/custom/[plugin]/[plugin].bundle.js"></script> -->

    <!-- Custom JS (per halaman) -->
    <script src="assets/js/custom/[module]/[page].js"></script>
    <!--end::Javascript-->
</body>
</html>
```

### Template Komponen JavaScript
```javascript
"use strict";

var FeatureName = function() {
    // Private variables
    var element;
    var form;
    var dataTable;

    // Private functions
    var initElements = function() {
        element = document.querySelector('#kt_feature_element');
    };

    var initDataTable = function() {
        dataTable = $('#kt_table').DataTable({
            // Konfigurasi DataTable
        });
    };

    var handleForm = function() {
        form = document.querySelector('#kt_form');
        if (!form) return;

        // Form handling logic
    };

    var handleEvents = function() {
        // Event listeners
        KTUtil.on(document, '[data-kt-feature-action]', 'click', function(e) {
            e.preventDefault();
            // Handle action
        });
    };

    // Public methods
    return {
        init: function() {
            initElements();
            initDataTable();
            handleForm();
            handleEvents();
        },
        refresh: function() {
            // Method publik untuk refresh data
        }
    };
}();

// Initialize on DOM ready
KTUtil.onDOMContentLoaded(function() {
    FeatureName.init();
});
```

### Template Card Widget
```html
<!--begin::Card-->
<div class="card card-xl-stretch mb-xl-8">
    <!--begin::Header-->
    <div class="card-header border-0 pt-5">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold text-dark">Judul Card</span>
            <span class="text-muted mt-1 fw-semibold fs-7">Deskripsi singkat</span>
        </h3>
        <div class="card-toolbar">
            <button type="button" class="btn btn-sm btn-light btn-active-light-primary">
                Action
            </button>
        </div>
    </div>
    <!--end::Header-->

    <!--begin::Body-->
    <div class="card-body py-3">
        <!-- Konten card -->
    </div>
    <!--end::Body-->
</div>
<!--end::Card-->
```

### Template Form dengan Validasi
```html
<!--begin::Form-->
<form id="kt_form" class="form">
    <!--begin::Input group-->
    <div class="fv-row mb-7">
        <label class="required form-label">Nama Lengkap</label>
        <input type="text" name="name" class="form-control mb-2" placeholder="Masukkan nama"/>
    </div>
    <!--end::Input group-->

    <!--begin::Input group-->
    <div class="fv-row mb-7">
        <label class="required form-label">Email</label>
        <input type="email" name="email" class="form-control mb-2" placeholder="email@contoh.com"/>
    </div>
    <!--end::Input group-->

    <!--begin::Actions-->
    <div class="text-end">
        <button type="button" class="btn btn-light me-2" data-kt-form-cancel="true">Batal</button>
        <button type="submit" class="btn btn-primary">
            <span class="indicator-label">Simpan</span>
            <span class="indicator-progress">Please wait...
                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
            </span>
        </button>
    </div>
    <!--end::Actions-->
</form>
<!--end::Form-->
```

### Template DataTable dengan Actions
```html
<!--begin::Table container-->
<div class="table-responsive">
    <!--begin::Header-->
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <span class="svg-icon svg-icon-1 position-absolute ms-6">
                    <!-- Search icon SVG -->
                </span>
                <input type="text" data-kt-table-filter="search" class="form-control form-control-solid w-250px ps-15" placeholder="Search..."/>
            </div>
        </div>
        <div class="card-toolbar">
            <div class="d-flex justify-content-end" data-kt-table-toolbar="base">
                <button type="button" class="btn btn-light-primary" data-kt-table-action="add">
                    <span class="svg-icon svg-icon-2">
                        <!-- Plus icon SVG -->
                    </span>
                    Add New
                </button>
            </div>
        </div>
    </div>
    <!--end::Header-->

    <!--begin::Body-->
    <div class="card-body py-4">
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table">
            <thead>
                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                    <th class="w-10px pe-2">
                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_table .form-check-input"/>
                        </div>
                    </th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 fw-semibold">
                <!-- Data rows -->
            </tbody>
        </table>
    </div>
    <!--end::Body-->
</div>
<!--end::Table container-->
```

### Template Modal
```html
<!--begin::Modal - Add/Edit-->
<div class="modal fade" id="kt_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Judul Modal</h2>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-auto" data-bs-dismiss="modal" aria-label="Close">
                    <span class="svg-icon svg-icon-1"></span>
                </div>
            </div>
            <div class="modal-body">
                <!-- Modal content -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" data-kt-modal-action="submit">Simpan</button>
            </div>
        </div>
    </div>
</div>
<!--end::Modal-->
```

### Template Tombol dengan Loading State
```html
<button type="submit" class="btn btn-primary" data-kt-indicator="on">
    <span class="indicator-label">Submit</span>
    <span class="indicator-progress">
        Please wait...
        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
    </span>
</button>
```

```javascript
// Handle form submit with loading state
var handleSubmit = function() {
    var submitButton = form.querySelector('[data-kt-indicator="on"]');

    submitButton.setAttribute('data-kt-indicator', 'on');
    submitButton.disabled = true;

    // AJAX request
    fetch('/api/endpoint', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        submitButton.removeAttribute('data-kt-indicator');
        submitButton.disabled = false;
        // Handle success
    })
    .catch(error => {
        submitButton.removeAttribute('data-kt-indicator');
        submitButton.disabled = false;
        // Handle error
    });
};
```

---

## 10. Contoh Implementasi Lengkap

### Contoh: User List Page

**File: `apps/user-management/users.html`**
```html
<!DOCTYPE html>
<html lang="id">
<head>
    <base href=""/>
    <title>Users - User Management</title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link rel="shortcut icon" href="assets/media/logos/favicon.ico"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700"/>
    <link href="assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet"/>
    <link href="assets/plugins/global/plugins.bundle.css" rel="stylesheet"/>
    <link href="assets/css/style.bundle.css" rel="stylesheet"/>
</head>
<body id="kt_app_body"
      data-kt-app-layout="dark-sidebar"
      data-kt-app-header-fixed="true"
      data-kt-app-sidebar-enabled="true"
      data-kt-app-sidebar-fixed="true"
      data-kt-app-toolbar-enabled="true"
      class="app-default">

<!--layout-partial:layout/_default.html-->

    <!--begin::Content-->
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">User Management</h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">Apps</li>
                        <li class="breadcrumb-item"><span class="bullet text-gray-400 fw-bold mx-1"></span></li>
                        <li class="breadcrumb-item text-dark">Users</li>
                    </ul>
                </div>
            </div>
        </div>
        <!--end::Toolbar-->

        <!--begin::Post-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <!--begin::Table-->
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <div class="d-flex align-items-center position-relative my-1">
                                <span class="svg-icon svg-icon-1 position-absolute ms-6">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M21.7 18.9L18.6 15.8C18.2 15.4 17.7 15.2 17.2 15.2H16.2C16.9 14.3 17.4 13.2 17.4 12C17.4 9.2 15.2 7 12.4 7C9.6 7 7.4 9.2 7.4 12C7.4 14.8 9.6 17 12.4 17C13.6 17 14.7 16.5 15.6 14.8V15.8C15.6 16.3 15.8 16.8 16.2 17.2L19.3 20.3C20.1 21.1 21.4 21.1 22.2 20.3C23 19.5 23 18.2 22.2 17.4L21.7 18.9ZM10.4 12C10.4 10.9 11.3 10 12.4 10C13.5 10 14.4 10.9 14.4 12C14.4 13.1 13.5 14 12.4 14C11.3 14 10.4 13.1 10.4 12Z" fill="black"/>
                                    </svg>
                                </span>
                                <input type="text" data-kt-user-table-filter="search" class="form-control form-control-solid w-250px ps-15" placeholder="Search User"/>
                            </div>
                        </div>
                        <div class="card-toolbar">
                            <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_user">
                                    Add User
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body py-4">
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_users">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th class="w-10px pe-2">
                                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_table_users .form-check-input"/>
                                        </div>
                                    </th>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Last Login</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold" id="kt_table_users_body">
                                <!-- Data akan di-load via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <!--end::Table-->
            </div>
        </div>
        <!--end::Post-->
    </div>
    <!--end::Content-->

    <!-- Modal Add User -->
    <div class="modal fade" id="kt_modal_add_user" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Add New User</h2>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-auto" data-bs-dismiss="modal">
                        <span class="svg-icon svg-icon-1">x</span>
                    </div>
                </div>
                <div class="modal-body">
                    <form id="kt_modal_add_user_form">
                        <div class="fv-row mb-7">
                            <label class="required form-label">Full Name</label>
                            <input type="text" name="fullname" class="form-control" placeholder="Enter name"/>
                        </div>
                        <div class="fv-row mb-7">
                            <label class="required form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="Enter email"/>
                        </div>
                        <div class="fv-row mb-7">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select">
                                <option value="admin">Administrator</option>
                                <option value="user">User</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="kt_modal_add_user_submit">Save</button>
                </div>
            </div>
        </div>
    </div>

    <script>var hostUrl = "assets/";</script>
    <script src="assets/plugins/global/plugins.bundle.js"></script>
    <script src="assets/js/scripts.bundle.js"></script>
    <script src="assets/plugins/custom/datatables/datatables.bundle.js"></script>
    <script src="assets/js/custom/apps/user-management/users.js"></script>
</body>
</html>
```

**File: `assets/js/custom/apps/user-management/users.js`**
```javascript
"use strict";

var KtUsers = function() {
    var table;
    var dataTable;
    var form;

    var initDataTable = function() {
        dataTable = $(table).DataTable({
            info: false,
            order: [],
            pageLength: 10,
            lengthChange: false,
            columnDefs: [
                { orderable: false, targets: 0 },
                { orderable: false, targets: 5 }
            ]
        });
    };

    var handleSearch = function() {
        var filterSearch = document.querySelector('[data-kt-user-table-filter="search"]');
        filterSearch.addEventListener('keyup', function(e) {
            dataTable.search(e.target.value).draw();
        });
    };

    var handleDeleteRows = function() {
        var deleteButtons = table.querySelectorAll('[data-kt-user-table-filter="delete_row"]');

        deleteButtons.forEach(d => {
            d.addEventListener('click', function(e) {
                e.preventDefault();
                var parent = e.target.closest('tr');

                // SweetAlert2 untuk konfirmasi
                Swal.fire({
                    text: "Are you sure you want to delete?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete!",
                    cancelButtonText: "No, cancel"
                }).then(function(result) {
                    if (result.value) {
                        // Hapus row
                        dataTable.row($(parent)).remove();
                        dataTable.draw();
                    }
                });
            });
        });
    };

    var handleAddUserModal = function() {
        var submitButton = document.querySelector('#kt_modal_add_user_submit');

        submitButton.addEventListener('click', function(e) {
            e.preventDefault();

            // Validasi form
            var formData = new FormData(form);
            var name = formData.get('fullname');
            var email = formData.get('email');

            // Tambah ke table
            var newRow = dataTable.row.add([
                '<input type="checkbox" class="form-check-input"/>',
                '<div class="d-flex align-items-center">' +
                '<div class="symbol symbol-50px me-3">' +
                '<span class="symbol-label bg-light-primary text-primary fw-bold">' + name.charAt(0) + '</span>' +
                '</div>' +
                '<div class="d-flex justify-content-start flex-column">' +
                '<a href="#" class="text-dark fw-bold text-hover-primary fs-6">' + name + '</a>' +
                '<span class="text-muted fw-semibold text-muted d-block fs-7">' + email + '</span>' +
                '</div></div>',
                formData.get('role'),
                '2 hours ago',
                '<span class="badge badge-light-success">Active</span>',
                '<a href="#" class="btn btn-light btn-active-light-primary btn-sm">Edit</a> ' +
                '<a href="#" data-kt-user-table-filter="delete_row" class="btn btn-light btn-active-light-danger btn-sm">Delete</a>'
            ]).draw();

            // Close modal
            var modalEl = document.querySelector('#kt_modal_add_user');
            var modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();

            // Reset form
            form.reset();
        });
    };

    return {
        init: function() {
            table = document.querySelector('#kt_table_users');
            form = document.querySelector('#kt_modal_add_user_form');

            if (!table) return;

            initDataTable();
            handleSearch();
            handleDeleteRows();
            handleAddUserModal();
        }
    };
}();

KTUtil.onDOMContentLoaded(function() {
    KtUsers.init();
});
```

---

## Referensi

- [Metronic Documentation](https://keenthemes.com/metronic)
- [Bootstrap 5 Docs](https://getbootstrap.com/docs/5.0)
- [jQuery Documentation](https://api.jquery.com)
- [DataTables Documentation](https://datatables.net)
