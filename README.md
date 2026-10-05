# 📸 Teras Memori

<p align="center">
  <strong>Platform Pemesanan Layanan Kreatif Berbasis Web</strong>
</p>

<p align="center">
  <em>Pesan. Kelola. Berkomunikasi. Wujudkan Memori.</em>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-red?style=for-the-badge&logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/Vue.js-3.x-42b883?style=for-the-badge&logo=vue.js" alt="Vue.js">
  <img src="https://img.shields.io/badge/Tailwind_CSS-v4-06b6d4?style=for-the-badge&logo=tailwindcss" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Pinia-4.x-ffd859?style=for-the-badge&logo=pinia" alt="Pinia">
</p>

<p align="center">
  <img src="https://img.shields.io/github/actions/workflow/status/rhesapnjtn/teras-memori/ci.yml?style=flat-square&label=CI" alt="CI">
  <img src="https://img.shields.io/github/license/rhesapnjtn/teras-memori?style=flat-square" alt="License">
</p>

---

## 🖼️ Tentang Teras Memori

**Teras Memori** adalah platform digital untuk pemesanan dan pengelolaan **layanan kreatif** seperti fotografi, videografi, editing, desain, dan berbagai kebutuhan visual lainnya.

Platform ini dirancang untuk mempertemukan pelanggan dengan penyedia layanan kreatif melalui proses yang lebih **terstruktur, transparan, dan terintegrasi**.

Tidak hanya menyediakan sistem pemesanan, Teras Memori juga mencakup pengelolaan pesanan, pelacakan status, komunikasi real-time, pengiriman file, pembayaran, portofolio, hingga pengelolaan pelanggan melalui dashboard admin.

> 🎯 **Tujuan utama:** mengubah proses pemesanan jasa kreatif yang biasanya dilakukan secara manual melalui chat menjadi sebuah workflow digital yang terorganisir.

---

## ✨ Fitur Utama

### 👤 Untuk Pengunjung & Pelanggan

| Fitur                   | Deskripsi                                                                       |
| ----------------------- | ------------------------------------------------------------------------------- |
| 🏠 **Landing Page**     | Menampilkan layanan, portofolio, testimoni, dan informasi bisnis secara menarik |
| 🎨 **Katalog Layanan**  | Menampilkan berbagai layanan kreatif beserta deskripsi dan harga                |
| 🖼️ **Portofolio**      | Showcase hasil pekerjaan untuk membantu pelanggan menentukan pilihan            |
| 🛒 **Pemesanan Online** | Pelanggan dapat membuat pesanan secara online tanpa proses manual               |
| 🔎 **Order Tracking**   | Melacak status pesanan menggunakan informasi pesanan                            |
| 👥 **Customer Portal**  | Area pelanggan untuk melihat pesanan dan aktivitas terkait                      |
| 📁 **File Upload**      | Pelanggan dapat mengirim file yang dibutuhkan untuk proses pengerjaan           |
| 💬 **Real-Time Chat**   | Komunikasi langsung antara pelanggan dan admin                                  |
| ⭐ **Testimoni**         | Pelanggan dapat memberikan ulasan terhadap layanan                              |

---

### 🛠️ Untuk Admin

| Fitur                        | Deskripsi                                                         |
| ---------------------------- | ----------------------------------------------------------------- |
| 📊 **Dashboard Overview**    | Ringkasan statistik pesanan, pelanggan, pendapatan, dan aktivitas |
| 📦 **Manajemen Pesanan**     | Mengelola pesanan dan memperbarui status pengerjaan               |
| 🎨 **Manajemen Layanan**     | CRUD layanan, harga, deskripsi, dan status layanan                |
| 👥 **Manajemen Pelanggan**   | Melihat data pelanggan dan histori aktivitas                      |
| 🖼️ **Manajemen Portofolio** | Mengelola karya yang ditampilkan pada website                     |
| ⭐ **Manajemen Ulasan**       | Memoderasi testimoni pelanggan                                    |
| 💬 **Chat Management**       | Inbox terpusat untuk komunikasi dengan pelanggan                  |
| 🔐 **Role & Permission**     | Pengaturan hak akses berdasarkan role                             |
| 📁 **File Management**       | Mengelola file yang dikirim dalam proses pemesanan                |
| 💳 **Pembayaran**            | Pengelolaan informasi dan status pembayaran                       |

---

# 🏗️ Arsitektur Sistem

Teras Memori menggunakan pendekatan **full-stack web application** dengan Laravel sebagai backend dan Vue sebagai frontend.

```text
┌─────────────────────────────────────────────┐
│                  CUSTOMER                   │
│                                             │
│        Browser / Mobile Web Browser         │
└──────────────────────┬──────────────────────┘
                       │
                       │ HTTP / WebSocket
                       ▼
┌─────────────────────────────────────────────┐
│                VUE 3 FRONTEND               │
│                                             │
│  Vue Router │ Pinia │ Axios │ Tailwind CSS  │
└──────────────────────┬──────────────────────┘
                       │
                       │ REST API
                       ▼
┌─────────────────────────────────────────────┐
│              LARAVEL 12 BACKEND             │
│                                             │
│ Controllers │ Services │ Models │ Policies  │
│                                             │
│ Sanctum │ Permission │ Broadcasting         │
└──────────────┬─────────────────┬────────────┘
               │                 │
               ▼                 ▼
        ┌──────────────┐   ┌───────────────┐
        │   Database   │   │  Broadcasting │
        │              │   │               │
        │ MySQL /      │   │ Reverb /      │
        │ PostgreSQL / │   │ Pusher        │
        │ SQLite       │   │               │
        └──────────────┘   └───────────────┘
```

> Catatan: pilihan database dapat disesuaikan dengan environment development maupun production.

---

# 🛠️ Teknologi yang Digunakan

## Backend

* **Laravel 12** — Framework utama backend
* **Laravel Sanctum** — Autentikasi berbasis API
* **Spatie Laravel Permission** — Role & permission management
* **Laravel Reverb** — WebSocket broadcasting
* **Pusher** — Alternatif broadcasting driver
* **PHPUnit** — Automated testing
* **Laravel Pint** — Code style & formatting

## Frontend

* **Vue 3** — Reactive frontend framework
* **Vue Router** — SPA routing
* **Pinia** — State management
* **Axios** — HTTP client
* **Tailwind CSS v4** — UI styling
* **Vite** — Frontend build tool
* **Laravel Echo** — WebSocket client
* **Pusher.js** — Real-time communication

## Development Tools

* **Laravel Tinker** — Interactive application debugging
* **Laravel Pail** — Real-time application logs
* **Concurrently** — Menjalankan beberapa development process sekaligus
* **Git & GitHub** — Version control dan collaboration
* **GitHub Actions** — Continuous Integration

---

# 📋 Persyaratan Sistem

Sebelum menjalankan project, pastikan environment sudah memiliki:

| Software |                      Versi Minimum |      Rekomendasi |
| -------- | ---------------------------------: | ---------------: |
| PHP      |                                8.2 |             8.2+ |
| Composer |                                2.x |    Versi terbaru |
| Node.js  |                               18.x |         20.x LTS |
| NPM      |                                9.x |    Versi terbaru |
| Database | SQLite 3 / MySQL 8 / PostgreSQL 14 | Sesuai kebutuhan |

---

# 🚀 Instalasi

## 1. Clone Repository

```bash
git clone https://github.com/rhesapnjtn/teras-memori.git
cd teras-memori
```

## 2. Install Dependency

### PHP

```bash
composer install
```

### JavaScript

```bash
npm install
```

---

## 3. Konfigurasi Environment

Salin file environment:

```bash
cp .env.example .env
```

Kemudian generate application key:

```bash
php artisan key:generate
```

> ⚠️ **Jangan pernah commit file `.env` ke repository.**

Pastikan konfigurasi environment lokal seperti database, mail, broadcasting, storage, dan service lainnya disesuaikan dengan environment masing-masing.

---

# 🗄️ Konfigurasi Database

Teras Memori dapat digunakan dengan beberapa database yang didukung Laravel.

Untuk development, **SQLite** dapat digunakan sebagai pilihan sederhana karena tidak membutuhkan database server terpisah.

Contoh konfigurasi menggunakan SQLite:

```env
DB_CONNECTION=sqlite
```

Untuk MySQL atau PostgreSQL, gunakan konfigurasi environment sesuai database lokal Anda.

> 🔐 **Keamanan:** jangan menyimpan username, password, API key, token, connection string, atau credential database di dalam source code maupun README.

---

# 🌱 Migrasi & Seeder

Jalankan migrasi:

```bash
php artisan migrate
```

Jika project memiliki data dummy/seeder:

```bash
php artisan db:seed
```

Atau jalankan keduanya:

```bash
php artisan migrate --seed
```

---

# 📁 Storage

Buat symbolic link untuk Laravel Storage:

```bash
php artisan storage:link
```

Hal ini diperlukan apabila aplikasi menggunakan file upload seperti:

* Foto portofolio
* File pelanggan
* Thumbnail layanan
* Asset lainnya

---

# 💻 Menjalankan Project

## ⚡ Cara 1 — Concurrent Development

Untuk menjalankan beberapa service sekaligus:

```bash
composer run dev
```

Command tersebut dapat menjalankan beberapa proses development seperti:

```text
Laravel Server
     │
     ├── php artisan serve
     │
     ├── Queue Worker
     │
     ├── Laravel Pail
     │
     └── Vite HMR
```

Aplikasi biasanya dapat diakses melalui:

```text
http://localhost:8000
```

---

## 🧩 Cara 2 — Manual

Jika ingin menjalankan service secara terpisah, buka beberapa terminal.

### Terminal 1 — Laravel

```bash
php artisan serve
```

### Terminal 2 — Queue Worker

```bash
php artisan queue:listen --tries=1 --timeout=0
```

### Terminal 3 — Laravel Pail

```bash
php artisan pail --timeout=0
```

### Terminal 4 — Vite

```bash
npm run dev
```

---

# 💬 Real-Time Communication

Teras Memori mendukung komunikasi real-time menggunakan sistem broadcasting Laravel.

Salah satu konfigurasi yang dapat digunakan adalah **Laravel Reverb**.

Jalankan Reverb:

```bash
php artisan reverb:start
```

Arsitektur komunikasi:

```text
Customer
   │
   │ Send Message
   ▼
Laravel Backend
   │
   │ Broadcast Event
   ▼
Reverb / Pusher
   │
   ▼
Admin Dashboard
   │
   │ Real-Time Message
   ▼
Customer
```

Dengan pendekatan ini, pesan dapat diterima tanpa pengguna harus melakukan refresh halaman secara manual.

---

# 🔐 Autentikasi & Authorization

Teras Memori menggunakan beberapa lapisan keamanan:

### Laravel Sanctum

Digunakan untuk autentikasi aplikasi dan komunikasi API.

### Spatie Laravel Permission

Digunakan untuk mengatur:

```text
User
 │
 ├── Role
 │    │
 │    ├── Permission A
 │    ├── Permission B
 │    └── Permission C
 │
 └── Access Control
```

Dengan sistem ini, akses ke halaman dan fitur tertentu dapat dibatasi berdasarkan role dan permission.

---

# 🧪 Testing

Menjalankan seluruh test:

```bash
php artisan test
```

Menggunakan PHPUnit TestDox:

```bash
vendor/bin/phpunit --testdox
```

### Feature Tests

```bash
php artisan test --testsuite=Feature
```

### Unit Tests

```bash
php artisan test --testsuite=Unit
```

Testing menggunakan environment yang terisolasi agar tidak mengganggu data development utama.

---

# 🎨 Code Quality

Teras Memori menggunakan **Laravel Pint** untuk menjaga konsistensi style kode.

### Format kode

```bash
vendor/bin/pint
```

### Mengecek tanpa melakukan perubahan

```bash
vendor/bin/pint --test
```

Sebelum melakukan Pull Request, disarankan menjalankan:

```bash
vendor/bin/pint --test
php artisan test
npm run build
```

---

# 🔄 Continuous Integration

Project ini menggunakan **GitHub Actions** untuk membantu memastikan perubahan kode tetap memenuhi standar project.

Workflow CI dapat digunakan untuk melakukan pemeriksaan seperti:

```text
Push / Pull Request
        │
        ▼
GitHub Actions
        │
        ├── Install Dependencies
        │
        ├── Code Style Check
        │
        ├── Run Tests
        │
        └── Build Frontend
                │
                ▼
             PASS / FAIL
```

Tujuannya adalah menangkap error sebelum perubahan digabungkan ke branch utama.

---

# 🏭 Production Build

Build frontend:

```bash
npm run build
```

Untuk optimasi Laravel:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> Untuk deployment production, pastikan konfigurasi environment, database, queue, storage, broadcasting, HTTPS, dan service pendukung telah dikonfigurasi sesuai infrastruktur yang digunakan.

---

# 📂 Struktur Project

Gambaran umum struktur project:

```text
teras-memori/
│
├── app/
│   ├── Http/
│   ├── Models/
│   ├── Services/
│   └── ...
│
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
│
├── resources/
│   ├── js/
│   │   ├── components/
│   │   ├── pages/
│   │   ├── stores/
│   │   └── ...
│   │
│   └── css/
│
├── routes/
│   ├── web.php
│   ├── api.php
│   └── channels.php
│
├── tests/
│   ├── Feature/
│   └── Unit/
│
├── public/
├── storage/
├── composer.json
├── package.json
├── vite.config.js
└── README.md
```

Struktur aktual dapat berkembang mengikuti kebutuhan fitur dan arsitektur aplikasi.

---

# 🔒 Keamanan

Beberapa hal penting yang harus diperhatikan ketika menjalankan project:

### Jangan commit `.env`

```text
.env
```

harus berada di `.gitignore`.

### Jangan commit credential

Jangan memasukkan hal berikut ke repository:

```text
Database Password
API Key
Secret Key
Access Token
Private Key
SMTP Password
Cloud Credentials
Production Credentials
```

### Gunakan `.env.example`

Repository hanya perlu menyediakan contoh konfigurasi:

```env
APP_NAME=
APP_ENV=
APP_KEY=

DB_CONNECTION=

BROADCAST_CONNECTION=

MAIL_MAILER=
```

Tanpa memasukkan credential sebenarnya.

---

# 🗺️ Roadmap

Beberapa pengembangan yang dapat dilakukan ke depannya:

* [x] Landing Page
* [x] Katalog Layanan
* [x] Portofolio
* [x] Sistem Pemesanan
* [x] Customer Portal
* [x] Order Tracking
* [x] Admin Dashboard
* [x] Role & Permission
* [x] Real-Time Chat
* [x] File Upload
* [x] Testimoni
* [x] Automated Testing
* [x] Continuous Integration
* [ ] Payment Gateway Integration
* [ ] Notification System
* [ ] Email Notification
* [ ] Advanced Analytics
* [ ] Customer Invoice
* [ ] Production Deployment Automation
* [ ] Progressive Web App

---

# 📚 Dokumentasi

Dokumentasi teknis lebih lanjut tersedia pada:

**[DESIGN.md](./DESIGN.md)**

Dokumentasi tersebut mencakup beberapa bagian seperti:

* Arsitektur aplikasi
* Business flow
* Database design
* ERD
* API
* UI/UX
* Deployment
* Technical decisions

---

# 🤝 Kontribusi

Kontribusi sangat terbuka untuk pengembangan project ini.

### 1. Fork repository

Buat fork repository ke akun GitHub Anda.

### 2. Buat branch baru

```bash
git checkout -b feature/nama-fitur
```

### 3. Lakukan perubahan

Pastikan perubahan mengikuti struktur dan standar kode project.

### 4. Jalankan testing

```bash
php artisan test
```

### 5. Cek code style

```bash
vendor/bin/pint --test
```

### 6. Commit perubahan

```bash
git add .
git commit -m "feat: deskripsi perubahan"
```

### 7. Push branch

```bash
git push origin feature/nama-fitur
```

### 8. Buat Pull Request

Buat Pull Request menuju branch utama project.

---

# 🐛 Bug & Feature Request

Menemukan bug atau memiliki ide pengembangan?

Silakan buat **Issue** pada repository GitHub agar dapat didokumentasikan dan ditindaklanjuti.

---

# 📄 Lisensi

Project ini menggunakan lisensi **MIT License**.

Lihat file [LICENSE](./LICENSE) untuk informasi selengkapnya.

---

# ❤️ Acknowledgement

Teras Memori dibangun menggunakan berbagai teknologi open-source dan tools modern.

Terima kasih kepada komunitas:

* Laravel
* Vue.js
* Tailwind CSS
* Pinia
* Vite
* Spatie
* Pusher
* PHPUnit
* GitHub Actions

dan seluruh komunitas open-source yang terus menyediakan tools luar biasa untuk developer.

---

## 👨‍💻 Tentang Project

**Teras Memori** dikembangkan sebagai platform untuk mendigitalisasi proses bisnis layanan kreatif, mulai dari pelanggan menemukan layanan hingga pesanan selesai.

Project ini juga menjadi implementasi nyata dari berbagai konsep pengembangan aplikasi modern, seperti:

```text
Frontend Development
        +
Backend Development
        +
REST API
        +
Authentication
        +
Authorization
        +
Database
        +
Real-Time Communication
        +
File Management
        +
Automated Testing
        +
CI/CD
        ↓
   TERAS MEMORI
```

> **Teras Memori — Mengubah ide dan momen menjadi karya yang tak terlupakan. 📸**
