# DESIGN DOCUMENT - TERAS MEMORI

## 1. Project Overview

**Teras Memori** merupakan sebuah platform layanan jasa kreatif berbasis web yang dibangun menggunakan Laravel 12 dan Vue 3. Aplikasi ini bertujuan untuk mempermudah proses pemesanan layanan fotografi, videografi, editing, desain, hingga kebutuhan kreatif lainnya secara digital.

Sistem ini dibagi menjadi 3 bagian utama, yaitu:
- **Landing Page Publik** – Menampilkan informasi layanan, portofolio, testimoni pelanggan, serta informasi kontak perusahaan.
- **Customer Portal** – Area khusus pelanggan untuk melakukan pemesanan, melacak status pesanan, mengunggah file pesanan, serta berkomunikasi secara real-time dengan tim admin melalui fitur chat.
- **Admin Dashboard** – Panel manajemen untuk mengelola seluruh data (Layanan, Portofolio, Pesanan, Pelanggan, Ulasan, dan Chat) beserta monitoring aktivitas secara terpusat.

## 2. Problem Statement

Proses pemesanan jasa kreatif konvensional umumnya masih menggunakan media komunikasi seperti WhatsApp atau DM media sosial. Hal tersebut menyebabkan:
- Riwayat pesanan menjadi sulit terlacak dan terorganisir.
- Komunikasi antara klien dan penyedia jasa tersebar di berbagai channel.
- Sulitnya memantau status pesanan (Pending, Diproses, Selesai, Dibatalkan) secara terstruktur.
- Tidak adanya sistem terpusat untuk mengelola portofolio dan testimoni sebagai media branding.

Teras Memori hadir untuk menjawab permasalahan tersebut dengan menghadirkan sistem pemesanan terintegrasi, terstruktur dan dapat dilacak secara transparan oleh pelanggan maupun admin.

## 3. Goals & Objectives

### Goals
- Membangun platform pemesanan jasa kreatif yang terintegrasi, profesional dan user-friendly.
- Menyediakan sistem pelacakan pesanan yang transparan bagi pelanggan.
- Mempermudah admin dalam mengelola pesanan, pelanggan dan komunikasi dalam satu dashboard terpusat.

### Objectives
- Mengimplementasikan sistem pemesanan (Order Management) dengan alur status yang jelas.
- Menyediakan fitur Order Tracking yang dapat diakses publik berdasarkan Nomor Pesanan dan Email.
- Menghadirkan fitur Chat Real-Time antara pelanggan dan admin untuk mempercepat komunikasi.
- Menyajikan portofolio dan ulasan sebagai bentuk social proof untuk meningkatkan kepercayaan pelanggan.
- Menerapkan sistem Role-Based Access Control (RBAC) untuk memisahkan hak akses publik, pelanggan dan admin.

## 4. Target User

| User | Deskripsi |
|---|---|
| **Pengunjung (Guest)** | Pengguna umum yang mengunjungi website untuk melihat layanan, portofolio, testimoni dan melakukan pemesanan tanpa perlu login. |
| **Pelanggan (Customer)** | Pengguna yang telah melakukan pemesanan dan dapat mengakses portal pelanggan untuk melacak pesanan, mengunggah file, serta melakukan chat dengan admin. |
| **Admin** | Pengelola sistem yang bertanggung jawab untuk mengelola layanan, portofolio, pesanan, pelanggan, ulasan dan menjawab chat masuk melalui dashboard. |

## 5. Tech Stack

### Backend
| Teknologi | Versi | Keterangan |
|---|---|---|
| [Laravel](https://laravel.com/) | 12.x | PHP Framework utama untuk membangun API, logic bisnis, autentikasi dan broadcasting. |
| [PHP](https://www.php.net/) | ^8.2 | Bahasa pemrograman backend. |
| [Laravel Sanctum](https://laravel.com/docs/sanctum) | ^4.0 | Token-based authentication untuk API dan session-based untuk web. |
| [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) | ^6.25 | Role & Permission Management untuk RBAC. |
| [Laravel Reverb](https://reverb.laravel.com/) | ^1.0 | WebSocket server bawaan Laravel untuk real-time broadcasting. |
| [Pusher PHP Server](https://pusher.com/) | Sesuai config | Alternatif driver broadcasting (dapat dikonfigurasi via `.env`). |
| [Laravel Tinker](https://laravel.com/docs/artisan#tinker) | ^2.10.1 | REPL untuk debugging dan development. |
| [Laravel Pail](https://laravel.com/docs/logging#pail) | ^1.2.2 | Real-time log viewer untuk development. |

### Frontend
| Teknologi | Versi | Keterangan |
|---|---|---|
| [Vue.js](https://vuejs.org/) | 3.5.x | Progressive JavaScript Framework untuk membangun SPA. |
| [Vue Router](https://router.vuejs.org/) | 5.3.x | Routing untuk Single Page Application. |
| [Pinia](https://pinia.vuejs.org/) | ^4.0.3 | State Management terpusat untuk mengelola state aplikasi. |
| [Vite](https://vitejs.dev/) | ^7.0.7 | Build tool modern, cepat dan ringan. |
| [Laravel Vite Plugin](https://laravel.com/docs/vite) | ^2.0.0 | Integrasi seamless Laravel dengan Vite. |
| [Tailwind CSS](https://tailwindcss.com/) | v4.0.0 | Utility-first CSS Framework untuk membangun UI modern dan konsisten. |
| [Axios](https://axios-http.com/) | ^1.20.0 | HTTP Client untuk komunikasi dengan REST API Laravel. |
| [Laravel Echo](https://laravel.com/docs/broadcasting#client-side-installation) | ^2.4.0 | Client-side library untuk subscribe WebSocket channel. |
| [Pusher.js](https://github.com/pusher/pusher-js) | ^8.6.0 | Real-time client untuk Pusher/Reverb. |
| [@laravel/echo-vue](https://github.com/laravel/echo/tree/main/packages/vue) | ^2.4.0 | Vue integration untuk Laravel Echo. |

### Tools & Utilities
| Teknologi | Versi | Keterangan |
|---|---|---|
| [Composer](https://getcomposer.org/) | - | Dependency Manager untuk PHP. |
| [NPM](https://www.npmjs.com/) | - | Package Manager untuk JavaScript. |
| [Concurrently](https://github.com/open-cli-tools/concurrently) | ^9.0.1 | Menjalankan multiple process (server, queue, logs, vite) secara bersamaan saat development. |
| [PHPUnit](https://phpunit.de/) | ^11.5.50 | Unit & Feature Testing. |
| [Laravel Pint](https://laravel.com/docs/pint) | ^1.24 | Code style fixer berbasis PHP CS Fixer untuk menjaga konsistensi kode. |

## 6. System Architecture

Teras Memori menggunakan arsitektur **Monolith dengan pendekatan SPA (Single Page Application)**. Backend Laravel berperan sebagai REST API dan WebSocket Broadcaster, sedangkan Frontend Vue 3 berjalan sebagai SPA yang di-build oleh Vite.

### 6.1 High-Level Architecture

```text
┌─────────────────────────┐
│     Client (Browser)    │
│  Vue 3 SPA + Tailwind   │
│  Axios + Laravel Echo   │
└───────────┬─────────────┘
            │ HTTP/HTTPS (REST API)
            │ WebSocket (WS/WSS)
┌───────────▼─────────────┐
│   Laravel 12 Backend    │
│  ├─ Routes (API/Web)    │
│  ├─ Controllers         │
│  ├─ Models (Eloquent)   │
│  ├─ Events (Broadcast)  │
│  ├─ Requests (Validation)│
│  ├─ Resources (API Transform) │
└───────────┬─────────────┘
            │
┌───────────▼─────────────┐
│        Database         │
│  MySQL / PostgreSQL /  │
│        SQLite           │
└─────────────────────────┘
```

### 6.2 Real-Time Architecture (Chat)

Fitur chat menggunakan **Laravel Broadcasting** dengan event `ChatMessageSent` yang mengimplementasikan `ShouldBroadcastNow` agar pesan ter-broadcast secara instan.

Alur pengiriman pesan real-time:

1. Pelanggan mengirim pesan melalui endpoint `POST /api/chats/{chat:public_token}/messages`
2. `ChatController` membuat record `ChatMessage` dan memuat relasi `chat`
3. Event `App\Events\ChatMessageSent` di-dispatch
4. Event di-broadcast ke **2 channel berbeda** secara bersamaan:
   - **Channel Publik**: `chat.{public_token}` – Digunakan oleh pelanggan (guest/customer) untuk melihat pesan secara real-time tanpa autentikasi kompleks. Channel ini bersifat publik berdasarkan token unik chat.
   - **Private Channel**: `admin-chat.{chat_id}` – Digunakan oleh Admin yang terautentikasi (melalui Sanctum + auth middleware). Hanya user dengan role `admin`/`superadmin` yang dapat subscribe channel ini (di-authorize melalui `routes/channels.php`).
5. Frontend Vue menerima event `message.sent` via Laravel Echo dan melakukan update UI secara real-time tanpa perlu refresh halaman.

**Authorization Channel (`routes/channels.php`):**
```php
// Public Chat (Customer)
Broadcast::channel('chat.{chatId}', fn ($user, $chatId) => Chat::where('id', $chatId)->exists());

// Admin Chat (Private) - Hanya admin & superadmin
Broadcast::channel('admin-chat.{chatId}', function ($user, $chatId) {
    if (! $user) return false;
    return $user->hasAnyRole(['admin', 'superadmin']);
});
```

## 7. Database Design

### 7.1 Entity Relationship Diagram (ERD) - Ringkasan

| Model | Primary Key | Foreign Keys | Deskripsi |
|---|---|---|---|
| `users` | id | - | User admin/dashboard (memiliki roles via Spatie) |
| `customers` | id | - | Data pelanggan (name, email, phone, address) |
| `services` | id | - | Katalog layanan (name, description, price, image, is_active) |
| `portfolios` | id | - | Portofolio karya (title, description, image, category, link) |
| `orders` | id | customer_id | Pesanan pelanggan (order_number, status, total_amount, notes) |
| `order_items` | id | order_id, service_id | Item layanan dalam pesanan |
| `order_files` | id | order_id | File yang diunggah pelanggan terkait pesanan |
| `payments` | id | order_id | Data pembayaran pesanan (status, amount, method, proof) |
| `reviews` | id | customer_id, order_id | Ulasan/testimoni pelanggan |
| `chats` | id | customer_id | Percakapan chat (memiliki `public_token` unik untuk akses publik) |
| `chat_messages` | id | chat_id, user_id? (opsional) | Pesan dalam chat (sender bisa customer/user/admin) |

### 7.2 Relasi Antar Model

- `Customer` **hasMany** `Order`, `Chat`, `Review`
- `Service` **hasMany** `OrderItem`
- `Order` **belongsTo** `Customer`, **hasMany** `OrderItem`, `Payment`, `OrderFile`
- `OrderItem` **belongsTo** `Order`, `Service`
- `OrderFile` **belongsTo** `Order`
- `Payment` **belongsTo** `Order`
- `Review` **belongsTo** `Customer`, `Order`
- `Chat` **belongsTo** `Customer`, **hasMany** `ChatMessage` (memiliki `public_token` untuk akses publik customer tanpa auth)
- `ChatMessage` **belongsTo** `Chat`
- `User` (Authenticatable + Spatie HasRoles) – digunakan untuk akses Admin Dashboard

## 8. Features & Functional Requirements

### 8.1 Public Features (Guest)

| Fitur | Endpoint/API | Deskripsi |
|---|---|---|
| **Homepage** | `GET /` (SPA) | Landing page menampilkan hero section, layanan, portofolio, testimoni, CTA pemesanan. |
| **Daftar Layanan** | `GET /api/services` | Menampilkan semua layanan aktif (dapat difilter, diurutkan). |
| **Detail Layanan** | `GET /api/services/{service}` | Menampilkan detail informasi layanan beserta harga. |
| **Portofolio** | `GET /api/portfolios` | Galeri karya (portfolio) untuk menampilkan hasil pekerjaan. |
| **Detail Portofolio** | `GET /api/portfolios/{portfolio}` | Detail proyek portofolio. |
| **Buat Pesanan** | `POST /api/orders` | Form pemesanan publik (tanpa login wajib). Sistem akan membuat order & customer otomatis. |
| **Order Tracking** | `POST /api/orders/track` | Pelacakan status pesanan berdasarkan `order_number` dan `email` pelanggan. Bisa diakses publik. |
| **Kirim Ulasan** | `POST /api/reviews` | Pelanggan dapat mengirim testimoni/ulasan setelah pesanan terkait. |
| **Daftar Ulasan** | `GET /api/reviews` | Menampilkan testimoni publik di landing page. |
| **Buat Chat** | `POST /api/chats` | Membuat room chat baru antara pengunjung/pelanggan dengan admin. |
| **Akses Chat Publik** | `GET /api/chats/{chat:public_token}` | Mengakses percakapan berdasarkan public token (aman, tidak bisa di-enumerasi sembarangan). |
| **Kirim Pesan Chat (Publik)** | `POST /api/chats/{chat:public_token}/messages` | Mengirim pesan sebagai pelanggan (melalui public token). |
| **Ambil Pesan Chat** | `GET /api/chats/{chat:public_token}/messages` | Memuat histori pesan chat. |
| **Lookup Customer** | `POST /api/chats/lookup-customer` | Validasi data pelanggan untuk menghubungkan ke chat/pesanan. |

### 8.2 Customer Portal Features

Pelanggan dapat mengakses informasi pesanan dan berkomunikasi via chat berdasarkan data pesanan yang valid (melalui Order Tracking atau akses chat dengan public token).

### 8.3 Admin Dashboard Features

| Modul | Akses | Deskripsi |
|---|---|---|
| **Dashboard Overview** | `GET /dashboard` (Auth) | Ringkasan statistik (Total Orders, Revenue, Customers, Reviews, Pending/Completed Orders) + recent orders. |
| **Kelola Layanan (Services)** | CRUD (Auth + Role Admin) | Tambah, edit, hapus, aktif/nonaktifkan layanan beserta harga dan gambar. |
| **Kelola Pesanan (Orders)** | Read/Update (Auth + Role Admin) | Melihat daftar pesanan, detail pesanan, update status pesanan, kelola file pesanan & pembayaran. |
| **Kelola Pelanggan (Customers)** | CRUD/Read (Auth + Role Admin) | Manajemen data pelanggan, histori pesanan dan chat pelanggan. |
| **Kelola Portofolio (Portfolio)** | CRUD (Auth + Role Admin) | Upload dan kelola karya portofolio (gambar, kategori, deskripsi, link proyek). |
| **Kelola Ulasan (Reviews)** | Read/Manage (Auth + Role Admin) | Moderasi dan manajemen testimoni pelanggan. |
| **Manajemen Chat (Chat)** | Full Access (Auth + Role Admin) | Melihat semua room chat aktif, membalas pesan secara real-time via private channel `admin-chat.{chat_id}`, mengelola status chat. |
| **Autentikasi Admin** | `POST /api/login`, `POST /api/logout`, `GET /api/user` | Login/logout admin dengan Sanctum, proteksi route dashboard dengan middleware `auth`. |

## 9. User Interface (UI/UX) Design

### 9.1 Design Principles

- **Modern & Minimalist** – Mengedepankan clean layout, whitespace yang cukup, dan fokus pada konten (foto/portfolio).
- **Brand-Oriented** – Menggunakan tone warna soft (cream/pink) untuk menciptakan kesan elegan, lembut dan profesional sesuai nama "Teras Memori".
- **User-Centric** – Navigasi sederhana, alur pemesanan jelas (step-by-step), dan feedback yang informatif untuk user.
- **Responsive-First** – Fully responsive di Desktop, Tablet dan Mobile. Mengutamakan mobile experience tanpa mengorbankan desktop.
- **Accessibility** – Kontras warna cukup, penggunaan semantic HTML, focus states yang jelas dan readable typography.
- **Micro-Interaction** – Transisi halus, hover effect subtle, loading state dan toast/feedback untuk meningkatkan UX.

### 9.2 Design System

#### Color Palette

| Purpose | Color | Hex | Penggunaan |
|---|---|---|---|
| **Background Primary** | Soft Cream | `#FFF8FA` | Background utama aplikasi (landing & dashboard) – memberikan kesan hangat & elegan. |
| **Text Primary** | Charcoal | `#191919` | Warna teks utama untuk readability maksimal. |
| **Accent Primary** | Rose | `#E85D75` | Primary brand color untuk CTA, button, link, icon aktif, accent element. |
| **Accent Soft** | Blush | `#F4A6B8` | Background accent, decorative element, badge, soft highlight. |
| **Neutral Muted** | Slate Gray | `#8A7077` | Sub-heading, meta text, placeholder. |
| **Success** | Emerald | `#10B981` | Status completed/success, badge success. |
| **Warning** | Amber | `#F59E0B` | Status pending/warning. |
| **Info** | Blue | `#3B82F6` | Status confirmed/processing/info. |
| **Danger** | Red | `#EF4444` | Status cancelled/error, destructive action. |
| **Surface/Card** | White | `#FFFFFF` | Background card, modal, dropdown untuk kontras terhadap cream bg. |

#### Typography

- **Font Family**: `Instrument Sans` (primary sans-serif) – dipilih karena modern, clean, readable dan memiliki karakter elegan yang cocok untuk brand kreatif. Fallback: `ui-sans-serif, system-ui, sans-serif, Apple Color Emoji, Segoe UI Emoji`.
- **Scale & Weight**: Menggunakan scale Tailwind dengan weight 400–700 untuk menjaga hierarchy visual yang konsisten.
- **Letter Spacing**: Penggunaan `tracking-tight`/`tracking-[-0.04em]` untuk heading untuk kesan premium, `tracking-wide` untuk label/button uppercase.

#### Spacing, Radius & Elevation

- **Spacing**: Menggunakan scale Tailwind v4 (4, 6, 8, 10, 12, 14, 16, 20, 24...) untuk konsistensi spacing.
- **Border Radius**: `rounded-lg` (12px), `rounded-xl` (16px), `rounded-2xl` (24px), `rounded-full` untuk pill/badge & avatar.
- **Elevation**: Soft shadow (`shadow-sm`, `shadow-lg`, `shadow-xl`) dengan subtle opacity untuk menciptakan depth tanpa terkesan berat.

### 9.3 Layout Structure

#### A. PublicLayout.vue (`resources/js/layouts/PublicLayout.vue`)
Layout untuk seluruh halaman publik (Home, Services, Portfolio, About, Order, Track Order, Chat).

- **Sticky Header** dengan backdrop-blur, border bottom subtle, logo brand (Teras Memori), navigation utama responsive (desktop nav + mobile hamburger menu).
- **Active State** pada menu berdasarkan route aktif.
- **Smooth Mobile Menu** dengan overlay dan animasi fade/slide.
- **Footer** (opsional) untuk informasi tambahan, link sosial & copyright.
- **Background Decorative**: Grid pattern halus dan blob accent rose untuk menambah dimensi visual tanpa mengganggu konten.

#### B. DashboardLayout.vue (`resources/js/layouts/DashboardLayout.vue`)
Layout untuk Admin Dashboard dengan sidebar fixed.

- **Sidebar Fixed (280px)** – Menu terorganisir per section (Overview, Management, Engagement) dengan icon SVG inline, active state jelas, hover effect smooth.
- **Mobile Responsive** – Collapsible sidebar dengan overlay saat mobile, smooth slide-in/out transition.
- **Top Header/User Info** – Menampilkan nama, email, avatar initials user admin, dropdown/logout.
- **Content Area** – Main content dengan padding responsive, background tetap konsisten (#FFF8FA).
- **State Management** – Terintegrasi dengan `authStore` (Pinia) untuk cek auth & user info.

#### C. Auth Layout (Login.vue)
Halaman login terpisah dengan desain clean, centered form, background dekoratif (grid pattern + gradient blob rose soft) untuk kesan profesional.

### 9.4 Page Design Breakdown

| Halaman | Lokasi | Desain Fokus |
|---|---|---|
| **Home.vue** | `pages/public/Home.vue` | Hero section besar dengan headline kuat, CTA primary, showcase layanan (card grid), portofolio preview (masonry/grid), testimonials section, trust indicators. Menggunakan decorative blob rose & subtle grid background. |
| **Services.vue** | `pages/public/Services.vue` | Grid card layanan dengan gambar/icon, harga, deskripsi ringkas, CTA "Pesan Sekarang". Card dengan hover lift effect. |
| **Portfolio.vue** | `pages/public/Portfolio.vue` | Gallery/grid portofolio responsif (masonry atau grid clean), kategori filter (opsional), lightbox/detail link, fokus pada visual imagery. |
| **About.vue** | `pages/public/About.vue` | Story/tentang studio, nilai-nilai, tim (jika ada), layout split dengan image. |
| **Order.vue** | `pages/public/Order.vue` | Multi-step atau single form pemesanan yang jelas, pemilihan layanan, data pelanggan, catatan pesanan, validasi real-time, UX flow minim friction. |
| **OrderSuccess.vue** | `pages/public/OrderSuccess.vue` | Success state dengan order number, instruksi tracking, CTA ke track order/chat. Bersih & reassuring. |
| **TrackOrder.vue** | `pages/public/TrackOrder.vue` | Form sederhana (Order Number + Email), hasil tracking menampilkan status order dengan timeline/progress indicator, detail pesanan & file. |
| **Chat.vue (Public)** | `pages/public/Chat.vue` | Chat interface real-time, header chat, message bubble (customer vs admin), auto-scroll, typing state, connection status, mobile-first layout. Terintegrasi Laravel Echo (public channel). |
| **Login.vue** | `pages/auth/Login.vue` | Clean auth form, logo, remember/forgot (jika perlu), error handling jelas, background dekoratif konsisten. |
| **Dashboard.vue** | `pages/dashboard/Dashboard.vue` | KPI cards (statistik) dengan icon, trend, recent orders table, loading/error state. Card berbasis soft surface dengan border subtle. |
| **Orders.vue** | `pages/dashboard/Orders.vue` | Data table dengan filter status, search, pagination, action (view/detail, update status), bulk action (opsional), modal detail order. |
| **Customers.vue** | `pages/dashboard/Customers.vue` | Table/list pelanggan, search, detail customer (orders & chats), responsive table. |
| **Services.vue (Admin)** | `pages/dashboard/Services.vue` | CRUD table + modal form (create/edit), upload image, toggle active, price formatting IDR. |
| **Portfolio.vue (Admin)** | `pages/dashboard/Portfolio.vue` | Grid/list portofolio + modal upload/edit, preview gambar, kategori, drag/reorder (opsional). |
| **Reviews.vue (Admin)** | `pages/dashboard/Reviews.vue` | List ulasan dengan rating, moderasi (publish/unpublish), delete, customer info. |
| **Chat.vue (Admin)** | `pages/dashboard/Chat.vue` | Split view: sidebar list chat (active, unread, customer info, last message, timestamp) + main chat area. Real-time via private channel `admin-chat.{chatId}`, subscribe/unsubscribe per chat aktif, auto-scroll, mark as read, close/reopen chat, success/error toast. |

### 9.5 UI Components & Patterns

- **Card**: `bg-white rounded-2xl border border-[#191919]/10 shadow-sm hover:shadow-md transition-shadow`
- **Primary Button**: Solid rose (#E85D75), hover state lebih gelap, focus ring, disabled state jelas, icon optional.
- **Ghost/Secondary Button**: Transparent/bg soft, border subtle, text charcoal.
- **Badge/Status Pill**: Rounded full, warna sesuai status (success/warning/info/danger) dengan background soft.
- **Input/Form**: Border subtle, focus ring rose, error state merah, label floating/above, helper text.
- **Table**: Clean, striped/subtle, sticky header, responsive (horizontal scroll on mobile), empty state ilustratif.
- **Modal/Drawer**: Backdrop blur, slide/fade animation, close button, focus trap, responsive (full mobile).
- **Loading State**: Skeleton loader untuk list/card/table, spinner untuk button/action.
- **Empty State**: Icon + title + description + CTA.
- **Toast/Alert**: Success/error/info/warning dengan icon, auto-dismiss, posisi top-right.

### 9.6 Animation & Transitions

Menggunakan Vue `<Transition>` + Tailwind untuk performa optimal (CSS-based):
- **Fade**: `fade` untuk overlay/modal
- **Slide**: `slide-up`, `slide-in-right`, `slide-in-left` untuk panel/drawer
- **Scale**: Subtle scale on hover (`hover:scale-[1.01]`) untuk card/button untuk kesan interaktif tanpa berlebihan
- **Stagger**: List/grid entrance animation ringan (opsional) untuk perceived performance

## 10. API Specification (Ringkasan)

| Method | Endpoint | Auth | Deskripsi |
|---|---|---|---|
| `GET` | `/api/services` | Public | List layanan aktif |
| `GET` | `/api/services/{service}` | Public | Detail layanan |
| `GET` | `/api/portfolios` | Public | List portofolio |
| `GET` | `/api/portfolios/{portfolio}` | Public | Detail portofolio |
| `POST` | `/api/orders` | Public | Buat pesanan baru |
| `POST` | `/api/orders/track` | Public | Track order (order_number + email) |
| `GET` | `/api/reviews` | Public | List ulasan publik |
| `POST` | `/api/reviews` | Public | Kirim ulasan |
| `POST` | `/api/chats/lookup-customer` | Public | Lookup customer |
| `POST` | `/api/chats` | Public | Buat chat room |
| `GET` | `/api/chats/{chat:public_token}` | Public | Akses chat (public token) |
| `POST` | `/api/chats/{chat:public_token}/messages` | Public | Kirim pesan chat |
| `GET` | `/api/chats/{chat:public_token}/messages` | Public | Ambil pesan chat |
| `POST` | `/api/login` | Public | Login admin |
| `POST` | `/api/logout` | Auth (Sanctum) | Logout |
| `GET` | `/api/user` | Auth (Sanctum) | Info user terautentikasi |

Route admin (protected) dikelompokkan berdasarkan resource (Customers, Orders, Reviews, Chats) di `app/Http/Controllers/Api/Admin/`.

## 11. Development Workflow

### 11.1 Prerequisites
- PHP >= 8.2
- Composer 2.x
- Node.js >= 18.x / LTS (direkomendasikan v20+)
- NPM >= 9.x
- Database: MySQL 8+ / PostgreSQL 14+ / SQLite 3

### 11.2 Local Development Setup

```bash
# 1. Clone repository
git clone https://github.com/rhesapnjtn/teras-memori.git
cd teras-memori

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Setup database (SQLite contoh)
touch database/database.sqlite
# Atau konfigurasi MySQL/Postgres di .env

# 5. Jalankan migrasi
php artisan migrate

# 6. (Opsional) Jalankan seeder
php artisan db:seed

# 7. Link storage (untuk upload file)
php artisan storage:link

# 8. Jalankan aplikasi (mode development)
composer run dev
```

**Script `composer run dev`** akan menjalankan secara concurrent:
- `php artisan serve` – Laravel dev server
- `php artisan queue:listen --tries=1 --timeout=0` – Queue worker
- `php artisan pail --timeout=0` – Real-time log viewer
- `npm run dev` – Vite dev server (HMR enabled)

### 11.3 Testing

```bash
# Jalankan seluruh test (Unit + Feature)
php artisan test

# Atau via PHPUnit langsung
vendor/bin/phpunit

# Dengan format testdox (lebih readable)
vendor/bin/phpunit --testdox
```

**Konfigurasi Testing**: Menggunakan SQLite `:memory:` untuk isolasi dan kecepatan eksekusi test (terkonfigurasi di `phpunit.xml`).

### 11.4 Code Quality

```bash
# Cek code style (tanpa auto-fix) - digunakan di CI
vendor/bin/pint --test

# Auto-fix code style sesuai PSR-12/Laravel preset
vendor/bin/pint
```

### 11.5 Build Production

```bash
# Build frontend assets untuk production
npm run build

# Optimasi Laravel (opsional, direkomendasikan untuk production)
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

Assets hasil build akan tersimpan di `public/build/` dan di-served oleh Laravel melalui Vite manifest.

## 12. Deployment Guidelines

| Konfigurasi | Nilai | Keterangan |
|---|---|---|
| `APP_ENV` | `production` | Environment production |
| `APP_DEBUG` | `false` | Matikan debug di production |
| `APP_KEY` | Generated | Wajib ada, generate via `php artisan key:generate` |
| `BROADCAST_CONNECTION` | `reverb`/`pusher` | Pilih sesuai infra WebSocket |
| `QUEUE_CONNECTION` | `database`/`redis` | Gunakan redis untuk performa & reliability lebih baik |
| `CACHE_STORE` | `redis`/`database` | Cache untuk optimasi performa |
| `SESSION_DRIVER` | `database`/`redis` | Session driver |
| `FILESYSTEM_DISK` | `local`/`s3` | Untuk file upload (order files, portfolio images) |
| Permissions | 775/755 | `storage/`, `bootstrap/cache/` wajib writable |
| WebSocket (Reverb) | SSL/HTTPS | Gunakan WSS di production (port 443) dengan TLS |

## 13. Security Considerations

- **Sanctum Authentication** – Token-based untuk API, terpisah dari web session.
- **Role-Based Access Control** – Spatie Permission membatasi akses endpoint admin hanya untuk role `admin`/`superadmin`.
- **Route Model Binding** – Menggunakan `public_token` untuk chat publik (mengurangi risiko enumeration dibanding ID sequential).
- **Input Validation** – Seluruh request divalidasi via Form Request (`app/Http/Requests/*`).
- **API Resources** – Response API di-transform via API Resources untuk mengontrol data yang diekspos.
- **Mass Assignment Protection** – `$fillable`/$guarded` terdefinisi di setiap Model Eloquent.
- **CSRF/XSS Protection** – Laravel built-in, Axios mengirim `X-Requested-With` header.
- **Broadcast Auth** – Private channel di-authorize dengan middleware `auth:sanctum`.
- **File Upload** – Validasi tipe & ukuran file (disarankan ditambahkan di Form Request untuk production).
- **Environment Secrets** – `.env` tidak di-commit ke repository, gunakan `.env.example` sebagai template.

## 14. Future Enhancements

| Fitur | Prioritas | Deskripsi |
|---|---|---|
| **Email Notifications** | High | Kirim notifikasi update status order & pesan chat baru via email. |
| **WhatsApp Notification** | High | Integrasi WA API (e.g. WhatsApp Business API) untuk notifikasi order/chat. |
| **Payment Gateway Integration** | High | Integrasi Midtrans/Xendit/Tripay untuk pembayaran online. |
| **File Preview & Validation** | Medium | Preview file upload (image/pdf), validasi ekstensi & max size lebih ketat. |
| **Dashboard Analytics** | Medium | Grafik penjualan, conversion rate, chat response time, top services. |
| **Export Data** | Medium | Export orders/customers/reviews ke PDF/Excel (Laporan). |
| **PWA (Progressive Web App)** | Medium | Installable app, offline support ringan, push notification. |
| **Dark Mode** | Low | Theme toggle (light/dark) untuk dashboard. |
| **Multi-Language (i18n)** | Low | Dukungan Bahasa Indonesia & English. |
| **Chat Auto-Reply/AI Assistant** | Low | Bot untuk menjawab pertanyaan umum saat admin offline.

## 15. Versioning & Changelog

Project ini mengikuti semantic versioning. Detail changelog dapat dilihat di [CHANGELOG.md](./CHANGELOG.md) (jika tersedia).

## 16. License

Teras Memori adalah open-source software yang dilisensikan di bawah [MIT License](https://opensource.org/licenses/MIT).

## 17. Acknowledgements

Terima kasih kepada tim [Laravel](https://laravel.com/), [Vue.js](https://vuejs.org/), [Tailwind CSS](https://tailwindcss.com/) dan seluruh komunitas open-source atas tools dan framework yang luar biasa sehingga proyek ini dapat terwujud.

---

**Dibuat dengan ❤️ untuk mendigitalisasi layanan jasa kreatif menjadi lebih terorganisir, transparan dan profesional.**
