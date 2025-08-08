# Clevio PRO — Laravel Breeze + n8n Chat (UI Only)

Kerangka proyek untuk membuat **Login (Laravel Breeze)** dan **Dashboard UI** yang memuat tabel Agent dari Postgres (skema Prisma) serta **embed chat n8n**. 
Laravel hanya mengurus **autentikasi** dan **UI**. Data agent dibaca langsung dari DB Postgres kamu (tabel `Agent`).

> Login user akan disimpan di **DB yang sama** (Postgres yang sama), namun memakai tabel Laravel `users` bawaan Breeze (tidak bentrok dengan `"User"` milik Prisma).

---

## 0) Prasyarat
- PHP **8.2+**
- Composer **2+**
- Node.js **20+** dan npm
- PostgreSQL (pakai DB yang sama dengan skema Prisma kamu)
- URL Webhook **n8n** untuk Chat (node **Chat Trigger** aktif, Allowed Origins diisi domain Laravel kamu)

---

## 1) Buat project Laravel + Breeze (Blade)

```bash
# 1. Buat project
composer create-project laravel/laravel clevio-pro
cd clevio-pro

# 2. Install Breeze (Blade)
composer require laravel/breeze --dev
php artisan breeze:install blade

# 3. Install dependencies FE
npm install
```

> Referensi Breeze: https://santrikoding.com/tutorial-laravel-breeze

---

## 2) Konfigurasi `.env` (pakai DB Postgres yang sama)
Edit `.env`:

```env
APP_NAME="Clevio PRO"
APP_URL=http://localhost:8000

# koneksi PG ke DB yang sama dgn Prisma
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_db
DB_USERNAME=your_user
DB_PASSWORD=your_pass

# Chat widget n8n
VITE_N8N_WEBHOOK_URL="https://your-n8n-domain/webhook/XXXXXXXX"
```

> Breeze akan membuat tabel `users` untuk login. Ini **tidak** mengubah tabel Prisma kamu (`"User"` & `"Agent"`).

Jalankan migrasi untuk tabel `users`:
```bash
php artisan migrate
```

Opsional, buat user uji:
```bash
        php artisan tinker
>>> \App\Models\User::create(['name' => 'Demo', 'email' => 'demo@example.com', 'password' => bcrypt('password')]);
```

---

## 3) Tambahkan paket Chat n8n (widget)
```bash
npm i @n8n/chat
```

> Widget ini akan di-embed di dashboard. Kita memakai **Import Embed** sesuai dokumentasi @n8n/chat.

---

## 4) Salin kerangka file ini ke project Laravel kamu
Salin seluruh isi folder `scaffold/` ke root project Laravel (overwrite bila diminta):

```
scaffold/
├─ app/Casts/PgArray.php
├─ app/Http/Controllers/DashboardController.php
├─ app/Models/Agent.php
├─ resources/js/chat.js
├─ resources/views/dashboard.blade.php
├─ resources/views/layouts/navigation.blade.php   (custom dropdown)
└─ routes/web.dashboard.php
```

Setelah menyalin:
- Merge `routes/web.dashboard.php` ke `routes/web.php` kamu (atau copy isi file ini ke bawah group `auth`).
- Pastikan `resources/js/app.js` **mengimpor** `./chat.js` (lihat langkah 5).

---

## 5) Wire FE (Vite) untuk Chat Widget
Tambahkan import berikut di **resources/js/app.js** (paling bawah):

```js
import './chat';
```

Jalankan dev server:
```bash
npm run dev
php artisan serve
```

Buka `http://localhost:8000/login`, login, lalu ke **Dashboard** (`/dashboard`).

---

## 6) n8n — Chat Trigger & CORS
Di workflow n8n kamu:
1. Gunakan **Chat Trigger** sebagai pemicu.
2. Aktifkan workflow (**Active**).
3. Di **Allowed Origins (CORS)** masukkan domain Laravel kamu (mis. `http://localhost:8000` atau domain produksi).
4. (Opsional) Nyalakan **Streaming responses** untuk efek mengetik.

> Lihat opsi widget & contoh embed di halaman NPM `@n8n/chat`.

---

## 7) Build produksi
```bash
npm run build
php artisan config:cache
php artisan route:cache
```

Deploy seperti biasa (Nginx/Apache/Valet/Forge).

---

## Catatan Teknis
- **Agent Model** membaca dari tabel Prisma `"Agent"` (case-sensitive). Jika tabel kamu berbeda (mis. `agents`), ubah properti `$table`.
- Kolom `tools` bertipe **text[]** di Postgres → dicasting ke array dengan `PgArray`.
- UI menggunakan Tailwind (sudah ada dari Breeze). Tabel dan layout meniru wireframe di gambar.
- Tombol **Run / Edit / Delete** hanya placeholder (arahkan ke endpoint n8n jika diperlukan).
- Untuk mode **fullscreen chat**, ubah `{ mode: 'fullscreen', target: '#n8n-chat' }`.

---

## Struktur yang ditambahkan (ringkas)
- `DashboardController@index` → query Agent milik user (ownerId = auth()->id())
- `dashboard.blade.php` → tabel Manage Agent + container `#n8n-chat`
- `navigation.blade.php` → dropdown **Profile / My Plan / Logout**

---

## Referensi
- @n8n/chat (NPM): https://www.npmjs.com/package/@n8n/chat
- Chat Trigger node: https://docs.n8n.io/integrations/builtin/core-nodes/n8n-nodes-langchain.chattrigger/
- Tutorial Breeze (Indonesia): https://santrikoding.com/tutorial-laravel-breeze

Selamat membangun! 🎉