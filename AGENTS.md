# 🤖 AGENTS.md — Teras Memori

> **Panduan dan aturan kerja untuk AI Coding Agent pada project Teras Memori.**

---

## 📌 Tentang Dokumen Ini

`AGENTS.md` adalah pedoman utama bagi AI Coding Agent yang bekerja pada repository **Teras Memori**.

Agent harus membaca dan memahami dokumen ini **sebelum melakukan perubahan apa pun pada kode**.

> 🎯 **Prinsip utama:**
> **Pahami → Periksa → Ubah seperlunya → Uji → Verifikasi → Laporkan**

---

# 🏗️ 1. Gambaran Project

**Teras Memori** adalah platform layanan kreatif yang menghubungkan pelanggan dengan layanan seperti:

* 📸 Photography
* 🎥 Videography
* 🎨 Design
* ✂️ Editing
* 💬 Real-time Chat
* 🖼️ Portfolio
* ⭐ Review & Testimonial
* 📦 Order Management
* 💳 Payment

Arsitektur utama:

```text
┌───────────────────────────────┐
│         Vue Frontend          │
│                               │
│ Vue 3 · Pinia · Router        │
│ Tailwind CSS · Axios          │
└───────────────┬───────────────┘
                │
                │ REST API
                ▼
┌───────────────────────────────┐
│        Laravel Backend        │
│                               │
│ Laravel · Sanctum · API       │
│ Spatie Permission             │
└───────────────┬───────────────┘
                │
        ┌───────┴────────┐
        ▼                ▼
┌──────────────┐  ┌──────────────┐
│   Database   │  │ Broadcasting │
│              │  │    / Chat    │
└──────────────┘  └──────────────┘
```

---

# 🧰 2. Teknologi

### Backend

| Teknologi             | Fungsi                  |
| --------------------- | ----------------------- |
| **Laravel 12**        | Backend & REST API      |
| **PHP 8.2+**          | Bahasa pemrograman      |
| **Laravel Sanctum**   | Authentication          |
| **Spatie Permission** | Role & permission       |
| **Laravel Reverb**    | Real-time communication |
| **PHPUnit**           | Testing                 |
| **Laravel Pint**      | Code formatting         |

### Frontend

| Teknologi           | Fungsi           |
| ------------------- | ---------------- |
| **Vue 3**           | UI framework     |
| **Vite**            | Build tool       |
| **Vue Router**      | Routing          |
| **Pinia**           | State management |
| **Axios**           | HTTP client      |
| **Tailwind CSS v4** | Styling          |
| **Laravel Echo**    | Real-time client |

---

# 🚨 3. Aturan Utama

AI Agent **WAJIB** mengikuti aturan berikut.

### 3.1 Jangan Mengubah Tanpa Memahami

Sebelum mengubah kode:

```text
Baca kode
   ↓
Pahami alur
   ↓
Cari dependensi
   ↓
Identifikasi dampak
   ↓
Baru lakukan perubahan
```

Jangan langsung mengubah file hanya berdasarkan nama atau asumsi.

---

### 3.2 Gunakan Perubahan Minimum

Utamakan:

```text
Perubahan kecil
      ↓
Targeted fix
      ↓
Testing
```

Hindari:

```text
Masalah kecil
      ↓
Rewrite seluruh fitur
      ↓
Banyak file berubah
      ↓
Potensi bug baru
```

Jika satu file cukup untuk menyelesaikan masalah, jangan mengubah banyak file tanpa alasan.

---

### 3.3 Jangan Merusak Fitur yang Sudah Ada

Sebelum mengubah fitur, perhatikan dampaknya terhadap:

* Authentication
* Authorization
* Services
* Orders
* Payments
* Customers
* Reviews
* Portfolio
* Chat
* File Upload
* Dashboard
* API
* Database

Perubahan pada satu bagian tidak boleh dilakukan tanpa mempertimbangkan dependensinya.

---

# 🔐 4. Keamanan & Privasi

## ⛔ Informasi yang DILARANG Diekspos

AI Agent **TIDAK BOLEH** menampilkan atau menyimpan informasi berikut:

* Password
* API Key
* Access Token
* Bearer Token
* OAuth Credential
* Webhook Secret
* Payment Secret
* Xendit Secret Key
* Database Password
* Laravel `APP_KEY`
* Private Key
* SSH Key
* Personal Access Token
* Session Data
* Credential pengguna

Contoh **JANGAN**:

```env
XENDIT_SECRET_KEY=xnd_production_xxxxxxxxx
```

Gunakan:

```env
XENDIT_SECRET_KEY=your_secret_key
```

---

# 🛡️ 5. Perlindungan Data Pribadi

Teras Memori dapat menangani data pelanggan.

Data berikut harus dianggap **sensitif**:

* Nama pelanggan
* Email
* Nomor telepon
* Alamat
* Detail order
* Data pembayaran
* File pelanggan
* Chat
* Review
* Informasi akun

### Dilarang

Jangan memasukkan data asli pelanggan ke:

* README
* Dokumentasi
* Screenshot
* Git commit
* GitHub Issue
* Pull Request
* Seeder
* Factory
* Test
* API example
* Log

Gunakan data dummy.

Contoh:

```json
{
  "name": "Example Customer",
  "email": "customer@example.com",
  "phone": "080000000000"
}
```

---

# 🔒 6. File Environment

Jangan membaca dan menampilkan isi `.env` kepada user kecuali benar-benar diperlukan untuk diagnosis dan **selalu sensor nilai rahasia**.

Jangan commit:

```text
.env
.env.*
```

Dokumentasi hanya boleh menggunakan contoh:

```env
APP_NAME=Teras_Memori
APP_ENV=local
APP_URL=http://localhost

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=teras_memori
DB_USERNAME=your_username
DB_PASSWORD=your_password

PAYMENT_SECRET_KEY=your_secret_key
```

---

# 🌐 7. Backend Laravel

Ikuti struktur Laravel yang sudah digunakan project.

Contoh:

```text
app/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
│
├── Models/
├── Services/
├── Policies/
└── ...

routes/
├── web.php
└── api.php

database/
├── migrations/
├── factories/
└── seeders/

tests/
├── Feature/
└── Unit/
```

Jangan memindahkan atau merombak struktur project tanpa alasan yang jelas.

---

# 🎯 8. Controller

Controller harus tetap sederhana.

Hindari menaruh business logic yang panjang langsung di Controller.

Gunakan pola:

```text
Request
   ↓
Controller
   ↓
Service / Business Logic
   ↓
Model
   ↓
Database
```

Jika project sudah memiliki Service Layer, gunakan Service tersebut daripada membuat logic duplikat.

---

# ✅ 9. Validasi

Validasi frontend **bukan pengganti validasi backend**.

Data penting harus tetap divalidasi di Laravel.

Untuk validasi kompleks, gunakan Form Request.

Contoh:

```php
public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email'],
    ];
}
```

Jangan mempercayai data dari frontend secara langsung.

---

# 🔑 10. Authentication

Authentication menggunakan **Laravel Sanctum**.

Gunakan mekanisme authentication yang sudah tersedia.

Contoh:

```php
Route::middleware('auth:sanctum')->group(function () {
    // Protected routes
});
```

Jangan membuat sistem authentication baru jika Sanctum sudah memenuhi kebutuhan.

---

# 👤 11. Authorization

Authorization harus dilakukan di backend.

Frontend hanya boleh digunakan untuk mengatur tampilan.

> ⚠️ Menyembunyikan tombol di frontend **bukan security mechanism**.

Gunakan sistem permission yang sudah tersedia dari:

```text
Spatie Laravel Permission
```

Jangan membuat role atau permission baru tanpa memeriksa sistem yang sudah ada.

---

# 📡 12. REST API

Backend API adalah sumber kebenaran data aplikasi.

Contoh endpoint:

```text
GET     /api/...
POST    /api/...
PUT     /api/...
PATCH   /api/...
DELETE  /api/...
```

Gunakan response yang konsisten.

Contoh:

```json
{
  "message": "Order berhasil dibuat.",
  "data": {}
}
```

Jangan pernah mengirim informasi internal seperti:

* SQL query
* Stack trace
* Database credential
* Server path sensitif
* Secret configuration

kepada client production.

---

# 🖥️ 13. Frontend Vue

Gunakan struktur dan pola yang sudah digunakan project.

```text
Vue
│
├── Pages
│
├── Components
│
├── Stores
│
├── Router
│
└── Axios
       │
       ▼
   Laravel API
```

### Components

Gunakan component untuk UI yang dapat digunakan kembali.

### Pages

Page bertanggung jawab terhadap tampilan dan koordinasi fitur.

### Stores

Gunakan Pinia untuk state yang memang membutuhkan shared state.

Jangan membuat state global baru jika state lokal sudah cukup.

---

# 🧭 14. Routing

Gunakan Vue Router.

Secara umum:

```text
Public
│
├── Home
├── Services
├── Portfolio
├── About
└── Login
      │
      ▼
Authenticated
│
└── Dashboard
    ├── Overview
    ├── Services
    ├── Orders
    ├── Customers
    ├── Portfolio
    ├── Reviews
    └── Chat
```

Route yang membutuhkan authentication harus tetap menggunakan guard yang tersedia.

Jangan melewati authentication hanya untuk membuat halaman dapat diakses.

---

# 💳 15. Payment

Payment adalah bagian yang **sangat sensitif**.

Secret payment hanya boleh berada di backend.

```text
Vue
 │
 │ Request
 ▼
Laravel
 │
 │ Secret Key
 ▼
Payment Provider
```

Jangan pernah:

```text
Vue → Payment Secret
```

Jangan percaya frontend ketika mengirim:

```json
{
  "payment": "success"
}
```

Status pembayaran harus diverifikasi oleh backend.

---

# 📦 16. Order Flow

Business flow order harus dijaga.

## 🔐 Authentication Wajib

**Setiap user WAJIB login sebelum dapat membuat order.**

User yang belum login hanya dapat mengakses fitur publik seperti:

* Melihat Home
* Melihat Services
* Melihat Portfolio
* Melihat informasi umum

User **tidak boleh** melakukan:

* Membuat order
* Checkout
* Memulai pembayaran
* Mengakses order miliknya

sebelum berhasil melakukan authentication.

Flow utama:

```text
Customer
   │
   ▼
Buka Website
   │
   ▼
Lihat Layanan
   │
   ▼
Pilih Layanan
   │
   ▼
Sudah Login?
   │
   ├── Tidak ──► Login / Register
   │                  │
   │                  ▼
   │              Berhasil Login
   │                  │
   └──────────────────┘
                      │
                      ▼
                  Buat Order
                      │
                      ▼
                   Checkout
                      │
                      ▼
                  Pembayaran
                      │
                      ▼
               Payment Confirmed
                      │
                      ▼
                  Processing
                      │
                      ▼
                  Pengerjaan
                      │
                      ▼
                   Completed
                      │
                      ▼
                    Review
```

### Authentication Rules

AI Agent harus memastikan:

* User memiliki account.
* User harus login sebelum membuat order.
* Guest tidak dapat mengakses checkout.
* Guest tidak dapat membuat order melalui API.
* Guest tidak dapat memulai pembayaran.
* Authentication harus diverifikasi oleh backend.
* Frontend boleh melakukan redirect ke halaman Login.
* Setelah login berhasil, user dapat melanjutkan flow order.
* Endpoint order harus menggunakan authentication middleware.
* User hanya dapat melihat dan mengelola order miliknya sendiri kecuali memiliki permission yang sesuai.

Contoh backend:

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
});
```

### Frontend Flow

```text
Pilih Layanan
      │
      ▼
Cek Authentication
      │
      ├── Guest
      │    │
      │    ▼
      │  Login
      │    │
      │    ▼
      │  Register jika belum memiliki akun
      │    │
      │    ▼
      │  Authentication berhasil
      │
      ▼
Checkout
      │
      ▼
Create Order
      │
      ▼
Payment
```

> ⚠️ Frontend authentication guard hanya digunakan untuk UX.
> Security sebenarnya harus tetap diterapkan menggunakan **Laravel middleware dan authorization di backend**.

### Order Ownership

```text
Authenticated User
        │
        ▼
      Order
        │
   ┌────┼────┬─────────┐
   ▼    ▼    ▼         ▼
Service Payment Status Review
```

User tidak boleh:

* Melihat order milik user lain.
* Mengubah order milik user lain.
* Membatalkan order milik user lain.
* Mengakses payment detail milik user lain.
* Mengakses file milik user lain.

kecuali user memiliki role atau permission yang memang mengizinkannya.

### Sebelum Mengubah Order Flow

AI Agent wajib memeriksa:

* Model Order
* Controller
* Service
* Payment logic
* API routes
* Vue Router guard
* Pinia store
* Checkout page/component
* Payment logic frontend
* Database relationship
* Authorization / Policy
* Tests

Jangan menghapus requirement authentication hanya agar proses development atau testing menjadi lebih mudah.

---

# 💬 17. Real-Time Chat

Chat menggunakan sistem broadcasting yang sudah tersedia.

Flow:

```text
Customer
    │
    ▼
Kirim Pesan
    │
    ▼
Laravel API
    │
    ▼
Broadcast Event
    │
    ▼
Reverb / Broadcasting
    │
    ▼
Laravel Echo
    │
    ▼
Admin / Customer
```

Pastikan user hanya dapat mengakses percakapan yang memang menjadi haknya.

Jangan mengekspos private conversation melalui endpoint atau event broadcast.

---

# 📁 18. File Upload

File upload harus divalidasi di backend.

Validasi minimal:

* File type
* MIME type
* Extension
* Ukuran file
* Authorization
* Ownership

Jangan mempercayai nama file dari client.

Jangan menyimpan file pribadi ke public directory tanpa alasan yang jelas.

---

# 🗄️ 19. Database

Sebelum mengubah database:

```text
Periksa migration
      ↓
Periksa Model
      ↓
Periksa Relationship
      ↓
Periksa Factory
      ↓
Periksa Seeder
      ↓
Periksa Test
      ↓
Baru ubah
```

Jika migration sudah pernah digunakan, buat migration baru.

Jangan sembarangan mengubah migration lama.

Hindari operasi destruktif.

---

# 🔗 20. Eloquent Relationship

Gunakan relationship Laravel jika tersedia.

Contoh:

```php
public function customer()
{
    return $this->belongsTo(User::class);
}
```

Hindari query berulang yang menyebabkan N+1 problem.

Gunakan eager loading jika diperlukan:

```php
Order::with(['customer', 'service'])->get();
```

---

# 🎨 21. UI/UX

Teras Memori adalah platform layanan kreatif.

UI harus mempertahankan karakter:

* ✨ Modern
* 🧼 Clean
* 📐 Konsisten
* 📱 Responsive
* ♿ Accessible
* 🎯 Fokus pada user experience

Perhatikan:

* Typography
* Spacing
* Hierarchy
* Button
* Form
* Empty state
* Loading state
* Error state
* Success state
* Responsive layout

Jangan menambahkan warna, font, shadow, atau style baru secara acak.

Sebelum membuat component baru, periksa apakah component yang sudah ada dapat digunakan kembali.

---

# 📱 22. Responsive Design

Setiap fitur frontend baru harus diperiksa pada:

```text
📱 Mobile
💻 Tablet
🖥️ Desktop
```

Gunakan responsive utility Tailwind:

```text
sm:
md:
lg:
xl:
```

Hindari fixed width yang menyebabkan horizontal overflow.

---

# 🧪 23. Testing

Setiap perubahan penting harus memiliki atau memperbarui test jika diperlukan.

Area penting:

* Authentication
* Authorization
* Orders
* Payment
* API
* Validation
* Reviews
* Chat
* File Upload
* Business Logic

Jangan menghapus test hanya karena test tersebut gagal setelah perubahan.

Cari penyebab kegagalannya terlebih dahulu.

---

# 🧹 24. Code Formatting

Gunakan tool yang sudah tersedia.

Backend:

```bash
./vendor/bin/pint
```

Frontend:

```bash
npm run lint
```

atau command yang memang tersedia di `package.json`.

Jangan melakukan formatting terhadap seluruh repository jika tidak diperlukan.

---

# 🧪 25. Validasi Setelah Perubahan

Setelah perubahan backend, jalankan jika tersedia:

```bash
php artisan test
```

dan:

```bash
./vendor/bin/pint --test
```

Untuk frontend:

```bash
npm run build
```

dan command lint/test yang tersedia.

> ❗ Jangan mengatakan **"test berhasil"** jika test belum benar-benar dijalankan.

Jika test tidak dapat dijalankan, jelaskan alasannya.

---

# 📦 26. Dependency

Jangan menambahkan dependency hanya karena terlihat lebih mudah.

Sebelum menginstal package baru:

1. Periksa package yang sudah tersedia.
2. Periksa apakah Laravel/Vue dapat menyelesaikan kebutuhan tersebut.
3. Pertimbangkan maintenance.
4. Pertimbangkan security.
5. Pastikan dependency memang diperlukan.

Jangan melakukan major upgrade tanpa permintaan eksplisit.

---

# 🌍 27. Environment

Development harus menggunakan environment lokal jika memungkinkan.

Contoh:

```text
http://localhost
127.0.0.1
Local Database
Local API
```

Jangan secara tidak sengaja menghubungkan environment development ke:

```text
Production API
Production Database
Production Payment
```

Sebelum melakukan testing integration, periksa:

```text
✓ API URL
✓ Database
✓ APP_ENV
✓ Payment environment
```

---

# 📝 28. Logging

Logging harus aman.

### ❌ Jangan

```php
Log::info($request->all());
```

karena request dapat berisi informasi sensitif.

### ✅ Gunakan

```php
Log::info('Status order diperbarui', [
    'order_id' => $order->id,
    'status' => $order->status,
]);
```

Jangan pernah log:

```text
Password
Token
Secret
Authorization Header
Payment Credential
Session Data
Private Chat
```

---

# 🚫 29. Git Safety

Jangan menjalankan command Git yang destruktif tanpa permintaan eksplisit.

Hindari:

```bash
git reset --hard
git clean -fd
git push --force
git push --force-with-lease
git checkout -- .
git restore .
```

Jangan menghapus perubahan lokal milik user.

Sebelum mengedit:

```bash
git status
```

Perhatikan perubahan yang sudah ada.

Jangan mengubah atau menghapus pekerjaan user yang tidak berkaitan dengan task.

---

# 🌿 30. Commit Convention

Gunakan Conventional Commits.

Contoh:

```text
feat: menambahkan dashboard order
fix: memperbaiki validasi order
refactor: menyederhanakan payment service
docs: memperbarui dokumentasi API
test: menambahkan test order
style: memperbaiki tampilan dashboard
chore: memperbarui dependency
```

Commit harus:

* Fokus
* Deskriptif
* Tidak terlalu besar
* Tidak mengandung credential
* Tidak mengandung data pribadi

---

# 🔀 31. Pull Request

Pull Request harus menjelaskan:

### Perubahan

Apa yang dibuat atau diperbaiki?

### Alasan

Mengapa perubahan tersebut diperlukan?

### Testing

Contoh:

```text
Testing:
✓ php artisan test
✓ npm run build
✓ npm run lint
```

### Security

Pastikan tidak ada:

```text
❌ API Key
❌ Password
❌ Token
❌ Credential
❌ Data pribadi
```

---

# 🧠 32. Jangan Berasumsi

AI Agent **tidak boleh berasumsi** bahwa:

* Endpoint tertentu sudah tersedia.
* Database column tertentu sudah ada.
* Relationship tertentu sudah tersedia.
* Package tertentu sudah ter-install.
* Role tertentu sudah dibuat.
* Payment method tertentu aktif.
* Route tertentu tersedia.
* Production service dapat digunakan.

Periksa repository terlebih dahulu.

---

# 🔍 33. Sebelum Mengubah Kode

AI Agent harus mempertimbangkan:

```text
┌────────────────────────────────────┐
│ Apa yang diminta user?             │
├────────────────────────────────────┤
│ File mana yang terkait?            │
├────────────────────────────────────┤
│ API apa yang terdampak?             │
├────────────────────────────────────┤
│ Database apa yang terdampak?       │
├────────────────────────────────────┤
│ Apakah authentication terlibat?    │
├────────────────────────────────────┤
│ Apakah authorization terlibat?     │
├────────────────────────────────────┤
│ Apakah ada data sensitif?          │
├────────────────────────────────────┤
│ Apakah ada test yang relevan?      │
└────────────────────────────────────┘
```

---

# ⚠️ 34. Operasi Destruktif

AI Agent **TIDAK BOLEH** secara otomatis:

* Drop database
* Truncate table
* Menghapus order
* Menghapus file customer
* Menghapus data production
* Menonaktifkan authentication
* Menonaktifkan authorization
* Menghapus security middleware
* Mengubah credential production
* Menjalankan migration destruktif

Jika operasi destruktif benar-benar diperlukan:

> **Minta konfirmasi user terlebih dahulu.**

---

# 📚 35. Dokumentasi

Jika menambahkan fitur besar, dokumentasi harus diperbarui jika diperlukan.

Dokumentasi harus menjelaskan:

* Fungsi fitur
* Konfigurasi
* Environment variable
* API endpoint
* Business rule
* Cara menjalankan test

Jangan pernah memasukkan credential asli.

---

# 🧾 36. Dokumentasi API

Gunakan data dummy.

Contoh:

```http
GET /api/orders
Authorization: Bearer YOUR_TOKEN
Accept: application/json
```

Response:

```json
{
  "data": [
    {
      "id": 1,
      "status": "processing",
      "total": 500000
    }
  ]
}
```

Jangan menggunakan token asli.

---

# 🔄 37. Alur Kerja AI Agent

Setiap task harus mengikuti:

```text
          ┌──────────────┐
          │  Task User   │
          └──────┬───────┘
                 ↓
       ┌───────────────────┐
       │ Pahami Permintaan │
       └─────────┬─────────┘
                 ↓
       ┌───────────────────┐
       │ Inspect Repository│
       └─────────┬─────────┘
                 ↓
       ┌───────────────────┐
       │ Cari Dependencies │
       └─────────┬─────────┘
                 ↓
       ┌───────────────────┐
       │ Perubahan Minimum │
       └─────────┬─────────┘
                 ↓
       ┌───────────────────┐
       │ Review Perubahan  │
       └─────────┬─────────┘
                 ↓
       ┌───────────────────┐
       │ Jalankan Testing  │
       └─────────┬─────────┘
                 ↓
       ┌───────────────────┐
       │ Security Review   │
       └─────────┬─────────┘
                 ↓
       ┌───────────────────┐
       │ Laporkan Hasil    │
       └───────────────────┘
```

---

# 📋 38. Checklist Sebelum Selesai

Sebelum menyatakan task selesai:

### Code

* [ ] Perubahan sesuai permintaan.
* [ ] Tidak ada perubahan tidak perlu.
* [ ] Tidak ada duplicate logic.
* [ ] Tidak merusak fitur existing.

### Security

* [ ] Tidak ada password.
* [ ] Tidak ada API key.
* [ ] Tidak ada token.
* [ ] Tidak ada secret.
* [ ] Tidak ada data pribadi.
* [ ] Tidak ada credential di source code.

### Backend

* [ ] Validation benar.
* [ ] Authorization benar.
* [ ] API response aman.
* [ ] Database tidak rusak.

### Frontend

* [ ] UI konsisten.
* [ ] Responsive.
* [ ] Loading state tersedia jika diperlukan.
* [ ] Error state ditangani.
* [ ] Authentication guard tetap berjalan.

### Testing

* [ ] Test relevan dijalankan.
* [ ] Build berhasil jika diperlukan.
* [ ] Tidak ada error baru.

### Git

* [ ] Tidak ada file sensitif.
* [ ] Tidak ada perubahan user yang terhapus.
* [ ] Diff sudah diperiksa.

---

# 🧑‍💻 39. Format Laporan Setelah Task

Setelah menyelesaikan task, gunakan format:

```text
## Perubahan

- Menambahkan ...
- Memperbaiki ...
- Mengubah ...

## Testing

- `php artisan test`
- `npm run build`

## Security

- Tidak ada credential yang ditambahkan.
- Tidak ada data pribadi yang diekspos.

## Catatan

- ...
```

Jika ada test yang gagal, jelaskan **test mana yang gagal dan alasannya**.

Jangan menyatakan semuanya berhasil jika masih terdapat error.

---

# 🏁 40. Prinsip Utama

> ## **Pahami sebelum mengubah.**
>
> ## **Ubah sesedikit mungkin.**
>
> ## **Jangan pernah mengekspos data sensitif.**
>
> ## **Pertahankan business logic.**
>
> ## **Selalu validasi perubahan sebelum menyatakan selesai.**

---

<div align="center">

**Teras Memori**

*Creative Service Platform*

`Laravel` · `Vue` · `Pinia` · `Tailwind CSS` · `Sanctum`

</div>
