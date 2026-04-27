# SQL Infrastructure — Best Practices

> Panduan ini ditulis dengan bahasa high-level agar mudah dipahami oleh junior programmer maupun AI model. Gunakan file ini sebagai referensi saat merancang dan membangun database untuk sistem baru. Panduan bersifat **database-agnostic** (berlaku untuk PostgreSQL, MySQL, SQLite, dll) kecuali disebutkan sebaliknya.

---

## 1. Naming Convention — Konsisten di Seluruh Database

| Kategori | Convention | Contoh |
|---|---|---|
| Nama tabel | snake_case, **plural** | `users`, `order_items`, `access_logs` |
| Nama kolom | snake_case | `created_at`, `is_active`, `full_name` |
| Primary key | `id` | `id` (di setiap tabel) |
| Foreign key | `<tabel_singular>_id` | `user_id`, `order_id`, `category_id` |
| Index | `idx_<tabel>_<kolom>` | `idx_users_email`, `idx_orders_created_at` |
| Unique constraint | `uq_<tabel>_<kolom>` | `uq_users_email` |
| Check constraint | `chk_<tabel>_<deskripsi>` | `chk_orders_total_positive` |
| Junction table | `<tabel1>_<tabel2>` (alphabetical) | `roles_users`, `posts_tags` |
| Boolean kolom | prefix `is_` / `has_` / `can_` | `is_active`, `has_verified`, `can_login` |
| Timestamp kolom | suffix `_at` | `created_at`, `updated_at`, `deleted_at` |

**Aturan:**
- Jangan gunakan reserved word SQL sebagai nama tabel/kolom (`order`, `user`, `group`, `select`, dll). Jika terpaksa, gunakan bentuk plural (`orders`, `users`, `groups`)
- Jangan gunakan prefix tabel di nama kolom (❌ `user_name` di tabel `users`, ✅ `name` di tabel `users`)
- Konsisten — pilih satu convention dan pakai di seluruh project

---

## 2. Primary Key — Pilih Strategi yang Tepat

### Auto-Increment Integer

```sql
-- ✅ Cocok untuk: sistem internal, tabel yang tidak di-expose ke publik
CREATE TABLE users (
  id SERIAL PRIMARY KEY,  -- PostgreSQL
  -- id INT AUTO_INCREMENT PRIMARY KEY,  -- MySQL
  name TEXT NOT NULL
);
```

### UUID

```sql
-- ✅ Cocok untuk: API publik, distributed system, keamanan (tidak bisa ditebak)
CREATE TABLE users (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),  -- PostgreSQL
  name TEXT NOT NULL
);
```

**Kapan pakai yang mana?**

| Strategi | Kelebihan | Kekurangan | Cocok untuk |
|---|---|---|---|
| Auto-increment | Cepat, hemat storage, mudah di-debug | Bisa ditebak, tidak aman untuk URL publik | Admin panel, internal system |
| UUID | Tidak bisa ditebak, aman untuk distributed | Lebih besar (16 bytes), index lebih lambat | Public API, multi-tenant |

**Tips:** Jika ragu, gunakan auto-increment untuk development dan evaluasi kebutuhan UUID saat production planning.

---

## 3. Kolom Wajib — Setiap Tabel Harus Punya

Setiap tabel **wajib** memiliki kolom-kolom ini:

```sql
CREATE TABLE contoh (
  id SERIAL PRIMARY KEY,
  -- ... kolom data ...
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW()
);
```

| Kolom | Tujuan |
|---|---|
| `id` | Identitas unik setiap row |
| `created_at` | Kapan data dibuat — untuk audit trail dan debugging |
| `updated_at` | Kapan data terakhir diubah — untuk sync dan cache invalidation |

**Opsional tapi direkomendasikan:**

| Kolom | Tujuan | Kapan dipakai |
|---|---|---|
| `deleted_at` | Soft delete (data tidak benar-benar dihapus) | Sistem yang butuh data recovery |
| `created_by` | Siapa yang membuat data | Sistem dengan audit trail |
| `updated_by` | Siapa yang terakhir mengubah | Sistem dengan audit trail |

---

## 4. Tipe Data — Pilih yang Paling Tepat

### Aturan Umum

```sql
-- ✅ Gunakan tipe data yang paling spesifik dan hemat
email    TEXT NOT NULL          -- atau VARCHAR(255)
age      SMALLINT NOT NULL      -- bukan INTEGER, karena umur tidak > 32767
price    NUMERIC(12, 2) NOT NULL -- bukan FLOAT, karena uang harus presisi
is_active BOOLEAN NOT NULL DEFAULT TRUE
```

### Cheat Sheet Tipe Data

| Kebutuhan | Tipe Data | Jangan Pakai |
|---|---|---|
| Teks pendek (nama, email) | `VARCHAR(n)` atau `TEXT` | `CHAR(n)` (padding spasi) |
| Teks panjang (deskripsi, konten) | `TEXT` | `VARCHAR(10000)` |
| Angka bulat kecil (umur, qty) | `SMALLINT` | `BIGINT` |
| Angka bulat standar (ID, count) | `INTEGER` | `BIGINT` (kecuali perlu) |
| Angka bulat besar (populasi, byte) | `BIGINT` | — |
| Uang / harga | `NUMERIC(p, s)` / `DECIMAL` | ❌ `FLOAT` / `DOUBLE` (tidak presisi) |
| Ya/Tidak | `BOOLEAN` | ❌ `TINYINT` / `CHAR(1)` |
| Tanggal saja | `DATE` | `VARCHAR` untuk tanggal |
| Waktu + timezone | `TIMESTAMP WITH TIME ZONE` | ❌ `TIMESTAMP` tanpa timezone |
| JSON data | `JSONB` (PostgreSQL) | `TEXT` untuk simpan JSON |
| Enum/status | `VARCHAR` + CHECK constraint | ❌ Database ENUM type (sulit di-migrate) |

**⚠️ Aturan Penting:**
- **Jangan pakai `FLOAT`/`DOUBLE` untuk uang.** Gunakan `NUMERIC` / `DECIMAL`
- **Selalu pakai timezone** untuk timestamp. Tanpa timezone = bug di production
- **Hindari database ENUM type.** Gunakan `VARCHAR` + `CHECK` constraint atau reference table — lebih mudah di-migrate

```sql
-- ❌ Jangan pakai ENUM type
CREATE TYPE status AS ENUM ('active', 'inactive');  -- sulit diubah

-- ✅ Gunakan VARCHAR + CHECK
CREATE TABLE users (
  status VARCHAR(20) NOT NULL DEFAULT 'active'
    CHECK (status IN ('active', 'inactive', 'suspended'))
);

-- ✅ Atau reference table (lebih fleksibel)
CREATE TABLE statuses (
  id SERIAL PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE
);
```

---

## 5. Constraints — Jaga Integritas Data di Level Database

Jangan hanya validasi di aplikasi. **Database harus menjadi last line of defense.**

### NOT NULL

```sql
-- ✅ Default-nya semua kolom harus NOT NULL
name TEXT NOT NULL,
email TEXT NOT NULL,

-- Hanya gunakan NULL jika memang ada kasus "tidak ada data"
deleted_at TIMESTAMP WITH TIME ZONE  -- NULL = belum dihapus
```

### UNIQUE

```sql
-- ✅ Kolom yang harus unik
email TEXT NOT NULL UNIQUE,

-- ✅ Composite unique (kombinasi kolom harus unik)
UNIQUE (user_id, role_id)  -- satu user tidak boleh punya role yang sama 2x
```

### FOREIGN KEY

```sql
-- ✅ Selalu definisikan foreign key untuk relasi
CREATE TABLE posts (
  id SERIAL PRIMARY KEY,
  author_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  title TEXT NOT NULL
);
```

**ON DELETE options:**

| Option | Efek | Kapan dipakai |
|---|---|---|
| `CASCADE` | Hapus child saat parent dihapus | Comment → Post (hapus post = hapus comment) |
| `SET NULL` | Set FK jadi NULL saat parent dihapus | Post → Author (hapus author, post tetap ada) |
| `RESTRICT` | Tolak penghapusan parent jika masih ada child | Order → Customer (jangan hapus customer yang punya order) |
| `SET DEFAULT` | Set FK ke nilai default | Jarang dipakai |

**Aturan:** Selalu tentukan `ON DELETE` behavior. Default-nya `RESTRICT`, yang mungkin bukan yang kamu mau.

### CHECK

```sql
-- ✅ Validasi data di level database
price NUMERIC(12, 2) NOT NULL CHECK (price >= 0),
quantity INTEGER NOT NULL CHECK (quantity > 0),
status VARCHAR(20) NOT NULL CHECK (status IN ('draft', 'published', 'archived')),
email TEXT NOT NULL CHECK (email LIKE '%@%.%')
```

---

## 6. Index — Percepat Query yang Sering Dipakai

### Aturan Dasar

```sql
-- ✅ Index kolom yang sering di-WHERE, JOIN, atau ORDER BY
CREATE INDEX idx_users_email ON users (email);
CREATE INDEX idx_orders_created_at ON orders (created_at);
CREATE INDEX idx_posts_author_id ON posts (author_id);

-- ✅ Composite index untuk query dengan multiple filter
-- Urutan kolom PENTING: kolom yang paling sering di-filter pertama
CREATE INDEX idx_orders_user_status ON orders (user_id, status);
```

### Kapan Perlu Index?

| Perlu Index ✅ | Tidak Perlu Index ❌ |
|---|---|
| Kolom di `WHERE` clause yang sering dipakai | Tabel kecil (< 1000 rows) |
| Kolom di `JOIN` (foreign key) | Kolom yang jarang di-query |
| Kolom di `ORDER BY` dengan dataset besar | Kolom dengan banyak NULL |
| Kolom `UNIQUE` (otomatis di-index) | Tabel yang lebih sering write daripada read |

### Jenis Index

| Jenis | Kapan Dipakai | Contoh |
|---|---|---|
| B-tree (default) | Perbandingan (`=`, `>`, `<`, `BETWEEN`) | Mayoritas use case |
| Hash | Hanya equality (`=`) | Lookup by exact value |
| GIN | Full-text search, JSONB, array | Kolom `JSONB`, search |
| GiST | Geospatial, range | PostGIS, IP range |

**⚠️ Jangan over-index:**
- Setiap index memperlambat `INSERT`, `UPDATE`, `DELETE`
- Mulai tanpa index → ukur query yang lambat → tambahkan index yang dibutuhkan
- Gunakan `EXPLAIN ANALYZE` untuk memastikan index benar-benar dipakai

---

## 7. Normalisasi — Hindari Data Duplikat

### Aturan Praktis

Targetkan **Third Normal Form (3NF)** sebagai standar minimum:

```sql
-- ❌ Denormalized — data duplikat
CREATE TABLE orders (
  id SERIAL PRIMARY KEY,
  customer_name TEXT NOT NULL,       -- duplikat dari tabel customers
  customer_email TEXT NOT NULL,      -- duplikat dari tabel customers
  customer_phone TEXT NOT NULL,      -- duplikat dari tabel customers
  product_name TEXT NOT NULL,        -- duplikat dari tabel products
  product_price NUMERIC NOT NULL,    -- duplikat dari tabel products
  quantity INTEGER NOT NULL
);

-- ✅ Normalized — data dipisah ke tabel yang tepat
CREATE TABLE customers (
  id SERIAL PRIMARY KEY,
  name TEXT NOT NULL,
  email TEXT NOT NULL UNIQUE,
  phone TEXT
);

CREATE TABLE products (
  id SERIAL PRIMARY KEY,
  name TEXT NOT NULL,
  price NUMERIC(12, 2) NOT NULL
);

CREATE TABLE orders (
  id SERIAL PRIMARY KEY,
  customer_id INTEGER NOT NULL REFERENCES customers(id),
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW()
);

CREATE TABLE order_items (
  id SERIAL PRIMARY KEY,
  order_id INTEGER NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
  product_id INTEGER NOT NULL REFERENCES products(id),
  quantity INTEGER NOT NULL CHECK (quantity > 0),
  unit_price NUMERIC(12, 2) NOT NULL  -- snapshot harga saat order dibuat
);
```

**Kapan boleh denormalize?**
- Reporting / analytics table (read-only, di-generate dari data normalized)
- Cache table untuk performa
- Snapshot data yang tidak boleh berubah (contoh: `unit_price` di `order_items` — harga saat order, bukan harga saat ini)

---

## 8. Soft Delete vs Hard Delete

### Soft Delete (Recommended untuk data penting)

```sql
CREATE TABLE users (
  id SERIAL PRIMARY KEY,
  name TEXT NOT NULL,
  email TEXT NOT NULL UNIQUE,
  deleted_at TIMESTAMP WITH TIME ZONE,  -- NULL = aktif, ada nilai = dihapus
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW()
);

-- ✅ "Hapus" user
UPDATE users SET deleted_at = NOW() WHERE id = 1;

-- ✅ Query hanya data aktif
SELECT * FROM users WHERE deleted_at IS NULL;

-- ✅ Buat view untuk kemudahan
CREATE VIEW active_users AS
  SELECT * FROM users WHERE deleted_at IS NULL;
```

### Hard Delete (Untuk data yang memang boleh hilang)

```sql
-- ✅ Hapus permanen
DELETE FROM session_logs WHERE created_at < NOW() - INTERVAL '90 days';
```

**Kapan pakai yang mana?**

| Soft Delete ✅ | Hard Delete ✅ |
|---|---|
| Data bisnis utama (user, order, product) | Session logs, temporary data |
| Data yang mungkin perlu di-restore | Cache entries |
| Data yang terkait regulasi (audit) | Data testing / staging |

---

## 9. Migration — Kelola Perubahan Schema dengan Aman

### Aturan Utama

1. **Jangan pernah ubah database production secara manual** (no ad-hoc `ALTER TABLE`)
2. **Semua perubahan schema harus melalui migration file**
3. **Migration file yang sudah dijalankan JANGAN diedit** — buat migration baru
4. **Commit migration file ke version control (git)**
5. **Review migration sebelum apply ke production**

### Pattern Migration yang Aman

```sql
-- ✅ Menambah kolom (aman, tidak lock tabel lama)
ALTER TABLE users ADD COLUMN phone TEXT;

-- ✅ Menambah kolom dengan default (aman di PostgreSQL 11+)
ALTER TABLE users ADD COLUMN is_verified BOOLEAN NOT NULL DEFAULT FALSE;

-- ⚠️ Rename kolom (bisa break aplikasi jika tidak hati-hati)
-- Lakukan dalam beberapa tahap:
-- 1. Tambah kolom baru
-- 2. Migrate data
-- 3. Update aplikasi untuk pakai kolom baru
-- 4. Hapus kolom lama (di migration berikutnya)

-- ⚠️ Menghapus kolom (irreversible!)
-- Pastikan tidak ada code yang masih menggunakan kolom ini
ALTER TABLE users DROP COLUMN old_column;
```

### Checklist Sebelum Migration ke Production

- [ ] Migration sudah di-test di staging/development
- [ ] Sudah backup database sebelum apply
- [ ] Migration tidak mengunci tabel terlalu lama (untuk tabel besar)
- [ ] Rollback plan sudah disiapkan
- [ ] Tim sudah di-notify tentang perubahan schema

---

## 10. Query Performance — Hindari Query Lambat

### Aturan Dasar

```sql
-- ❌ Select semua kolom (membebani network dan memory)
SELECT * FROM users;

-- ✅ Select kolom yang dibutuhkan saja
SELECT id, name, email FROM users;

-- ❌ Query di dalam loop (N+1 problem)
-- Pseudocode:
-- for each order in orders:
--   SELECT * FROM users WHERE id = order.user_id

-- ✅ Satu query dengan JOIN atau IN
SELECT o.*, u.name as user_name
FROM orders o
JOIN users u ON o.user_id = u.id;

-- Atau batch query
SELECT * FROM users WHERE id IN (1, 2, 3, 4, 5);
```

### Pagination — Jangan OFFSET untuk Dataset Besar

```sql
-- ❌ OFFSET lambat untuk halaman besar (skip N rows)
SELECT * FROM products ORDER BY id LIMIT 20 OFFSET 10000;

-- ✅ Cursor-based pagination (konsisten cepat)
SELECT * FROM products
WHERE id > 10000  -- cursor dari halaman sebelumnya
ORDER BY id
LIMIT 20;
```

### EXPLAIN ANALYZE — Selalu Cek Query Lambat

```sql
-- ✅ Lihat query plan sebelum deploy query baru
EXPLAIN ANALYZE
SELECT * FROM orders
WHERE user_id = 123
  AND status = 'pending'
ORDER BY created_at DESC;

-- Perhatikan:
-- - Seq Scan → mungkin perlu index
-- - Rows (estimated vs actual) → statistik tabel perlu di-update
-- - Total cost → semakin kecil semakin baik
```

---

## 11. Security — Jaga Data Tetap Aman

### SQL Injection Prevention

```sql
-- ❌ JANGAN pernah concatenate input user ke query
-- Pseudocode:
-- query = "SELECT * FROM users WHERE email = '" + userInput + "'"

-- ✅ Selalu gunakan parameterized query / prepared statement
-- Pseudocode:
-- query = "SELECT * FROM users WHERE email = $1"
-- params = [userInput]
```

### Principle of Least Privilege

```sql
-- ✅ Buat role khusus untuk aplikasi
-- Jangan pakai superuser / root untuk koneksi aplikasi
CREATE ROLE app_user LOGIN PASSWORD 'strong_password';

-- Berikan hak akses minimum yang dibutuhkan
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO app_user;

-- Jangan berikan hak ALTER, DROP, CREATE ke role aplikasi
```

### Data Sensitif

```sql
-- ✅ Jangan simpan password dalam plaintext
-- Gunakan hash di level aplikasi (bcrypt, argon2)
password_hash TEXT NOT NULL,  -- bukan "password TEXT"

-- ✅ Pertimbangkan enkripsi untuk data sensitif
-- NIK, nomor kartu kredit, dll → enkripsi di level aplikasi sebelum simpan ke database
```

---

## 12. Seed Data — Data Awal yang Dibutuhkan Sistem

```sql
-- ✅ Seed data untuk reference table (roles, statuses, categories)
INSERT INTO roles (name, description) VALUES
  ('admin', 'Full system access'),
  ('editor', 'Can create and edit content'),
  ('viewer', 'Read-only access')
ON CONFLICT (name) DO NOTHING;  -- idempotent, aman dijalankan berulang

-- ✅ Seed data harus idempotent (bisa dijalankan berkali-kali tanpa error)
-- Gunakan ON CONFLICT DO NOTHING atau INSERT ... WHERE NOT EXISTS
```

**Aturan:**
- Seed file terpisah dari migration
- Seed harus idempotent
- Jangan seed data user/test di production
- Pisahkan seed untuk development dan production

---

## 13. Backup & Recovery — Wajib untuk Production

### Strategi Backup

| Jenis | Frekuensi | Kapan dipakai |
|---|---|---|
| Full backup | Harian (minimal) | Disaster recovery |
| Incremental backup | Setiap jam / real-time | Point-in-time recovery |
| Logical backup (pg_dump) | Sebelum migration besar | Rollback plan |

**Aturan:**
- Automasi backup — jangan manual
- Test restore secara berkala (backup yang tidak pernah di-test = tidak ada backup)
- Simpan backup di lokasi berbeda dari database (different server / cloud region)
- Definisikan retention policy (berapa lama backup disimpan)

---

## 14. Environment-Specific Rules

| Aspek | Development | Staging | Production |
|---|---|---|---|
| Data | Fake / seed data | Copy dari production (anonymized) | Real data |
| Drop table | ✅ Boleh | ⚠️ Hati-hati | ❌ Jangan |
| Direct SQL | ✅ Boleh | ⚠️ Limit access | ❌ Melalui migration saja |
| Backup | Tidak wajib | Direkomendasikan | **Wajib** |
| Superuser access | ✅ Boleh | ❌ Tidak | ❌ Tidak |
| SSL connection | Optional | ✅ Ya | ✅ Wajib |

---

## 15. Checklist Sebelum Deploy Database

### Desain Schema
- [ ] Semua tabel punya `id`, `created_at`, `updated_at`
- [ ] Naming convention konsisten (snake_case, plural untuk tabel)
- [ ] Semua kolom punya tipe data yang tepat (tidak ada FLOAT untuk uang)
- [ ] `NOT NULL` di semua kolom kecuali yang memang boleh NULL
- [ ] Foreign key terdefinisi untuk semua relasi
- [ ] `ON DELETE` behavior sudah ditentukan di setiap FK
- [ ] CHECK constraint untuk validasi data penting

### Performance
- [ ] Index di kolom yang sering di-WHERE, JOIN, ORDER BY
- [ ] Tidak ada over-indexing (setiap index punya tujuan jelas)
- [ ] Query yang kompleks sudah di-cek dengan `EXPLAIN ANALYZE`
- [ ] Tidak ada `SELECT *` di query production

### Security
- [ ] Tidak ada hardcoded credential di kode
- [ ] Aplikasi tidak pakai superuser untuk koneksi
- [ ] Password di-hash, data sensitif di-enkripsi
- [ ] Parameterized query digunakan (bukan string concatenation)

### Operations
- [ ] Migration file sudah di-test dan di-commit
- [ ] Backup strategy sudah dikonfigurasi
- [ ] Seed data production sudah disiapkan
- [ ] Rollback plan ada untuk setiap migration

---

## Quick Reference — Do & Don't

```sql
-- ❌ DON'T
SELECT * FROM users;                                          -- select semua kolom
UPDATE users SET status = 'inactive';                         -- update tanpa WHERE
DELETE FROM orders;                                           -- delete tanpa WHERE
CREATE TABLE Order (User VARCHAR(50));                        -- reserved word, no snake_case
price FLOAT NOT NULL;                                         -- FLOAT untuk uang
created_at TIMESTAMP;                                         -- tanpa timezone
"SELECT * FROM users WHERE id = '" + input + "'"              -- SQL injection

-- ✅ DO
SELECT id, name, email FROM users WHERE is_active = TRUE;     -- select spesifik + filter
UPDATE users SET status = 'inactive' WHERE id = 1;            -- selalu pakai WHERE
DELETE FROM orders WHERE id = 1;                              -- selalu pakai WHERE
CREATE TABLE orders (user_id INTEGER NOT NULL);               -- proper naming
price NUMERIC(12, 2) NOT NULL;                                -- NUMERIC untuk uang
created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW();   -- timezone + default
"SELECT * FROM users WHERE id = $1", [input]                  -- parameterized query
```

---

> **Catatan:** File ini adalah living document. Update sesuai kebutuhan tim dan project.
