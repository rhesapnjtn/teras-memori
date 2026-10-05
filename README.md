# Teras Memori

[![CI](https://github.com/rhesapnjtn/teras-memori/actions/workflows/ci.yml/badge.svg)](https://github.com/rhesapnjtn/teras-memori/actions/workflows/ci.yml)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-green.svg)](https://vuejs.org)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind%20CSS-v4-blue.svg)](https://tailwindcss.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

**Teras Memori** adalah platform pemesanan layanan jasa kreatif berbasis web yang dibangun dengan Laravel 12 + Vue 3. Platform ini mempermudah proses pemesanan, pelacakan status pesanan, komunikasi real-time, serta pengelolaan layanan kreatif secara terintegrasi dan profesional.

## 🎯 Tentang Proyek

Teras Memori hadir untuk mendigitalisasi layanan jasa fotografi, videografi, editing, desain, dan kebutuhan kreatif lainnya. Dengan sistem yang terpusat, pelanggan dapat memesan layanan, melacak status pesanan, mengunggah file, serta berkomunikasi langsung dengan admin melalui fitur chat real-time. Sementara itu, admin dapat mengelola seluruh aktivitas melalui dashboard yang ringkas dan mudah digunakan.

## ✨ Fitur Utama

### Untuk Pengunjung & Pelanggan
- **Landing Page Modern** – Tampilan elegan dan responsif untuk memamerkan layanan & portofolio
- **Katalog Layanan** – Daftar layanan lengkap dengan deskripsi, harga, dan detail
- **Galeri Portofolio** – Showcase hasil karya untuk meningkatkan kepercayaan pelanggan
- **Sistem Pemesanan Online** – Form pemesanan sederhana tanpa perlu registrasi
- **Order Tracking** – Lacak status pesanan secara publik berdasarkan Nomor Pesanan & Email
- **Customer Portal** – Akses informasi pesanan, unggah file, dan histori chat
- **Chat Real-Time** – Komunikasi langsung dengan admin via WebSocket
- **Testimoni Publik** – Ulasan pelanggan yang ditampilkan di landing page

### Untuk Admin Dashboard
- **Overview Dashboard** – Statistik pesanan, pelanggan, pendapatan & aktivitas terkini
- **Manajemen Layanan** – CRUD layanan, harga, status aktif/nonaktif
- **Manajemen Pesanan** – Update status pesanan, kelola item, file & pembayaran
- **Manajemen Pelanggan** – Data pelanggan, histori pesanan & chat
- **Manajemen Portofolio** – Kelola karya portofolio dengan gambar & kategori
- **Manajemen Ulasan** – Moderasi testimoni pelanggan
- **Chat Management** – Inbox real-time terpusat dengan private channel
- **Role-Based Access** – Akses terbatas menggunakan Spatie Laravel Permission
- **Autentikasi Aman** – Login admin dengan Laravel Sanctum

## 🛠️ Tech Stack

### Backend
- [Laravel 12](https://laravel.com) – Full-stack PHP framework
- [Laravel Sanctum](https://laravel.com/docs/sanctum) – API & Web Authentication
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) – Role & Permission
- [Laravel Reverb](https://reverb.laravel.com) – Native WebSocket Broadcasting
- [Pusher](https://pusher.com) – Alternative WebSocket Driver (configurable)
- [PHPUnit](https://phpunit.de) – Testing

### Frontend
- [Vue 3](https://vuejs.org) – Composition API, Reactive & Performant
- [Vue Router 5](https://router.vuejs.org) – SPA Routing
- [Pinia 4](https://pinia.vuejs.org) – Intuitive State Management
- [Vite 7](https://vitejs.dev) – Lightning-fast Build Tool
- [Tailwind CSS v4](https://tailwindcss.com) – Modern Utility-first CSS
- [Laravel Echo](https://laravel.com/docs/broadcasting#client-side-installation) + [Pusher.js](https://github.com/pusher/pusher-js) – Real-time Client
- [Axios](https://axios-http.com) – Promise-based HTTP Client

### Dev Tools
- [Laravel Pint](https://laravel.com/docs/pint) – Automated Code Style
- [Concurrently](https://github.com/open-cli-tools/concurrently) – Run multiple dev processes
- [Laravel Tinker](https://laravel.com/docs/tinker) & [Laravel Pail](https://laravel.com/docs/pail) – Debugging Tools

## 📋 Prasyarat

- [PHP](https://php.net) >= 8.2
- [Composer](https://getcomposer.org) >= 2.x
- [Node.js](https://nodejs.org) >= 18.x (LTS v20.x direkomendasikan)
- [NPM](https://www.npmjs.com) >= 9.x
- Database: [MySQL](https://mysql.com) 8.0+, [PostgreSQL](https://postgresql.org) 14+, atau [SQLite](https://sqlite.org) 3.x

## 🚀 Instalasi

### 1. Clone Repository
```bash
git clone https://github.com/rhesapnjtn/teras-memori.git
cd teras-memori
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Konfigurasi Environment
```bash
# Copy .env.example ke .env
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Konfigurasi Database

**Opsi A: SQLite (Rekomendasi untuk Development)**
```bash
# Buat file database
touch database/database.sqlite

# Edit .env
DB_CONNECTION=sqlite
DB_DATABASE=/full/path/ke/database.sqlite
```

**Opsi B: MySQL**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=teras_memori
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Migrasi & Seeding
```bash
# Jalankan migrasi database
php artisan migrate

# (Opsional) Jalankan seeder untuk data dummy
php artisan db:seed
```

### 6. Storage Link
```bash
php artisan storage:link
```

## 💻 Menjalankan Aplikasi

### Opsi 1: Concurrent (Rekomendasi)
Jalankan semua service development sekaligus dengan single command:

```bash
composer run dev
```

Perintah ini akan menjalankan:
- Laravel Dev Server (`php artisan serve`)
- Queue Worker (`php artisan queue:listen`)
- Log Viewer Pail (`php artisan pail`)
- Vite Dev Server dengan HMR (`npm run dev`)

### Opsi 2: Manual (Terpisah)
Buka 4 terminal berbeda:

```bash
# Terminal 1 - Laravel Server
php artisan serve

# Terminal 2 - Queue Worker
php artisan queue:listen --tries=1 --timeout=0

# Terminal 3 - Pail Logs
php artisan pail --timeout=0

# Terminal 4 - Vite
npm run dev
```

Aplikasi bisa diakses di: [http://localhost:8000](http://localhost:8000)

## 🔌 Konfigurasi Real-Time (Broadcasting)

### Menggunakan Laravel Reverb (Native)
```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
```

Jalankan Reverb server:
```bash
php artisan reverb:start
```

### Menggunakan Pusher
```env
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=mt1
```

Frontend akan otomatis membaca konfigurasi dari `VITE_REVERB_*` / `PUSHER_*` di file `.env`.

## 🧪 Testing

```bash
# Jalankan semua test
php artisan test

# Jalankan dengan testdox (lebih informatif)
vendor/bin/phpunit --testdox

# Hanya Feature Tests
php artisan test --testsuite=Feature

# Hanya Unit Tests
php artisan test --testsuite=Unit
```

Testing menggunakan **SQLite in-memory** untuk eksekusi cepat & terisolasi.

## 🎨 Code Quality

```bash
# Auto-fix code style dengan Laravel Pint
vendor/bin/pint

# Cek code style tanpa memperbaiki (digunakan di CI)
vendor/bin/pint --test
```

## 🏗️ Build untuk Production

```bash
# Build frontend assets
npm run build

# Optimasi Laravel (direkomendasikan)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 📚 Dokumentasi

Dokumentasi lengkap terkait arsitektur, design system, ERD, API, dan UI/UX tersedia di:
- **[DESIGN.md](./DESIGN.md)** – Technical Design Document (arsitektur, flow, DB design, UI/UX, deployment)

## 🤝 Kontribusi

Kontribusi sangat kami apresiasi! Silakan ikuti langkah berikut:

1. **Fork** repository ini
2. Buat branch baru: `git checkout -b feature/nama-fitur`
3. Commit perubahan: `git commit -m "feat: deskripsi fitur"`
4. Push ke branch: `git push origin feature/nama-fitur`
5. Buat **Pull Request** ke branch `main`

Pastikan kode sudah mengikuti style guide (`vendor/bin/pint`) dan semua test lolos sebelum mengajukan PR.

## 🐛 Issue

Jika menemukan bug atau memiliki saran fitur, silakan buat [Issue Baru](https://github.com/rhesapnjtn/teras-memori/issues).

## 📄 Lisensi

Proyek ini dilisensikan di bawah [MIT License](https://opensource.org/licenses/MIT). Lihat file [LICENSE](./LICENSE) untuk detail lebih lanjut.

## 🙏 Acknowledgement

Dibangun dengan ❤️ menggunakan [Laravel](https://laravel.com), [Vue.js](https://vuejs.org), dan [Tailwind CSS](https://tailwindcss.com).
