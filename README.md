# Katalog Order UMKM — Dapur TH Sant

Aplikasi web katalog dan pemesanan untuk UMKM makanan (Dapur TH Sant). Pelanggan dapat melihat katalog, memasukkan produk ke keranjang, checkout **tanpa perlu mendaftar akun**, mengunggah bukti transfer, dan memantau status pesanan lewat kode referensi. Admin/Owner mengelola produk, kategori, promo, pesanan, dan verifikasi pembayaran melalui panel admin.

Dibuat dengan **Laravel 12**, **MySQL**, dan **Blade**; antarmuka memakai **Bootstrap 5** (CDN) dengan CSS kustom, serta bundel aset **Vite + Tailwind** pada halaman tertentu.

---

## Daftar Isi

- [Fitur](#fitur)
- [Teknologi](#teknologi)
- [Kebutuhan Sistem](#kebutuhan-sistem)
- [Instalasi Lokal](#instalasi-lokal)
- [Akun & Login](#akun--login)
- [Alur Bisnis](#alur-bisnis)
- [Status Pesanan & Pembayaran](#status-pesanan--pembayaran)
- [Struktur Proyek](#struktur-proyek)
- [Perintah Berguna](#perintah-berguna)
- [Pengujian](#pengujian)
- [API](#api)
- [Deployment](#deployment)
- [Dokumentasi Lain](#dokumentasi-lain)
- [Pemecahan Masalah](#pemecahan-masalah)
- [Lisensi](#lisensi)

---

## Fitur

### Sisi Pelanggan (tanpa login)
- **Katalog produk** dengan pencarian nama, filter kategori, pengurutan (terbaru/harga/abjad), paginasi 12 item, dan indikator stok. Produk nonaktif atau stok habis otomatis tidak ditampilkan.
- **Detail produk** lengkap dengan gambar, deskripsi, kategori, produk terkait, dan tombol tambah ke keranjang.
- **Keranjang belanja** berbasis sesi: tambah, ubah jumlah, hapus, kosongkan.
- **Checkout tamu**: isi data pengiriman, pilih **DELIVERY** (ongkir Rp10.000) atau **PICKUP**, pilih metode bayar **TRANSFER_BANK** (BCA/Mandiri/GoPay/OVO/ShopeePay/QRIS) atau **COD**.
- **Kode promo** dengan tipe potongan persen atau nominal, minimal belanja, dan masa berlaku.
- **Halaman sukses** dengan rincian pesanan dan kode referensi (format `ORD-XXXXXXXXXX`).
- **Unggah bukti transfer** (JPG/PNG, maksimal 2 MB) yang terikat pada sesi pesanan, bukan pada ID kiriman form.
- **Pelacakan status** lewat kode referensi di `/status` atau tautan langsung `/status/{kode}`.
- **Halaman kontak/outlet** yang datanya diambil dari pengaturan admin.

### Sisi Admin/Owner (login)
- **Dashboard** ringkasan pesanan, pendapatan, dan stok menipis.
- **CRUD produk** (gambar disimpan di disk `public`, slug otomatis, validasi slug unik).
- **CRUD kategori** dan **CRUD promo**.
- **Manajemen pesanan**: daftar, detail, ubah status, hapus (stok dikembalikan).
- **Manajemen pembayaran**: catat pembayaran, ubah, verifikasi/tolak, lihat bukti transfer.
- **Pengaturan kontak/outlet** yang langsung tampil di halaman publik.
- **Role-based access**: `ADMIN` dan `OWNER` (middleware `role`).

### Keamanan & Ketahanan Data
- Transaksi database + `lockForUpdate()` saat memotong stok → tidak ada stok minus pada checkout bersamaan.
- Pembatalan pesanan mengembalikan stok **tepat satu kali** (`Order::cancelWithReason()` idempoten).
- Reaktivasi pesanan yang dibatalkan memotong stok lagi dan gagal dengan pesan ramah bila stok kurang (`StockException`).
- Verifikasi/tolak pembayaran dijaga di level model (`Payment::markVerified()`, `Payment::markRejected()`) sehingga klik ganda tidak merusak data.
- Bukti transfer disimpan **privat** (`storage/app/private`), hanya bisa dibuka admin lewat route ber-otentikasi.
- Rate limit pada endpoint rawan: login (5/menit), checkout (10/menit), upload bukti (5/menit), halaman status (30/menit).
- Registrasi publik ditutup; akun admin dibuat lewat perintah `admin:create`.

---

## Teknologi

| Komponen | Versi / Keterangan |
|---|---|
| PHP | ^8.2 — diuji pada PHP 8.2.12 |
| Framework | Laravel 12.69.2 |
| Database | MySQL 8 (utf8mb4) |
| Autentikasi API | Laravel Sanctum 4 (personal access token) |
| Tampilan | Blade + Bootstrap 5.3 (CDN) + CSS kustom |
| Aset | Vite 7 + Tailwind CSS 4 (`resources/css/app.css`) |
| Pengujian | PHPUnit 11 |
| Formatter | Laravel Pint |

---

## Kebutuhan Sistem

- PHP 8.2+ dengan ekstensi `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd`, `curl`, `xml`.
- Composer 2.
- Node.js 20+ dan npm (untuk `npm run build`).
- MySQL 8 (XAMPP/Laragon/MariaDB juga bisa).

---

## Instalasi Lokal

```bash
# 1. Dependensi PHP & JS
composer install
npm install

# 2. Konfigurasi environment
cp .env.example .env
php artisan key:generate

# 3. Buat database (contoh)
#    CREATE DATABASE db_katalog_order_online_umkm_2
#      CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 4. Sesuaikan kredensial DB di .env, lalu:
php artisan migrate
php artisan db:seed          # data contoh (lokal saja)
php artisan storage:link     # agar gambar produk bisa diakses

# 5. Aset frontend
npm run build                # atau: npm run dev (mode watch)

# 6. Akun admin
php artisan admin:create --name="Admin" --email=admin@umkm.com --password=password --role=ADMIN

# 7. Jalankan
php artisan serve
```

Buka `http://127.0.0.1:8000`.

**Alternatif satu perintah** (khusus mesin baru, membuat `.env` + key + migrasi + storage link + build aset):

```bash
composer setup
```

### Variabel Environment Penting

| Variabel | Lokal | Produksi |
|---|---|---|
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | **`false`** |
| `APP_URL` | `http://127.0.0.1:8000` | `https://domain-anda.com` |
| `DB_*` | MySQL lokal | MySQL server |
| `SESSION_DRIVER` | `file` | `file` |
| `SESSION_SECURE_COOKIE` | `false` | **`true`** (wajib HTTPS) |
| `FILESYSTEM_DISK` | `local` | `local` |
| `CACHE_STORE` | `database` | `database` |
| `QUEUE_CONNECTION` | `database` | `database` (tanpa worker, lihat catatan) |
| `MAIL_MAILER` | `log` | `log` |

Template produksi tersedia di `.env.production.example`.

> **Catatan queue:** aplikasi belum memakai job/mail/notification, jadi **tidak perlu** `queue:work` di server. Nilai `database` hanya default Laravel.

---

## Akun & Login

Registrasi publik **ditutup**. Akun dibuat lewat perintah:

```bash
php artisan admin:create \
  --name="Nama Admin" \
  --email=admin@umkm.com \
  --password=password \
  --role=ADMIN \
  --phone=08123456789
```

- `--role` menerima `ADMIN` atau `OWNER`.
- Tanpa opsi (mode interaktif), perintah akan menanyakan nama, email, password, dan telepon.
- Syarat: email unik, password minimal 8 karakter.

Akun dari seeder (hanya untuk pengembangan lokal): `admin@umkm.com` / `password` dan `owner@umkm.com` / `password`.

---

## Alur Bisnis

```
Pelanggan                         Sistem                            Admin
---------                         ------                            -----
Lihat katalog  ───────────────▶  Produk aktif
Tambah ke keranjang ──────────▶  Sesi keranjang
Checkout (data + promo) ──────▶  Transaksi: cek stok
                                  ├─ lockForUpdate produk
                                  ├─ potong stok
                                  ├─ buat order (ORD-XXXX)
                                  └─ buat order_items
                                 Status: MENUNGGU_KONFIRMASI
                                                                     Lihat pesanan
Unggah bukti transfer ────────▶  Payment PENDING_REVIEW
                                  Status order: DIPROSES
                                                                     Verifikasi ──▶ VERIFIED
                                                                       order: SELESAI
                                                                     Tolak ──────▶ REJECTED
                                                                       order: DIBATALKAN
                                                                       stok dikembalikan
Pantau /status ───────────────▶  Status + rincian + pembayaran
```

Poin penting:
- **Nominal pembayaran ditentukan server** (`order.total_amount`), bukan dari form, sehingga tidak bisa dimanipulasi pelanggan.
- Pembatalan oleh admin (status → `DIBATALKAN`) mengembalikan stok satu kali.
- Menolak pembayaran juga membatalkan pesanan dan mengembalikan stok (idempoten, tidak dobel).

---

## Status Pesanan & Pembayaran

**Status pesanan (`orders.status`)**

| Nilai | Arti |
|---|---|
| `MENUNGGU_KONFIRMASI` | Pesanan dibuat, menunggu konfirmasi/pembayaran |
| `DIPROSES` | Bukti bayar diunggah / sedang disiapkan |
| `DIKIRIM` | Sedang dikirim |
| `SELESAI` | Pembayaran terverifikasi & pesanan selesai |
| `DIBATALKAN` | Dibatalkan admin atau pembayaran ditolak (stok kembali) |

**Status pembayaran (`payments.payment_status`)**

| Nilai | Arti |
|---|---|
| `PENDING_REVIEW` | Menunggu verifikasi admin |
| `VERIFIED` | Terverifikasi (order → `SELESAI`) |
| `REJECTED` | Ditolak (order → `DIBATALKAN`, stok kembali) |

Transisi `DIBATALKAN` ⇄ status lain dijaga di model: kembali dari `DIBATALKAN` akan **memotong stok lagi** dan gagal jika stok tidak cukup.

---

## Struktur Proyek

```
app/
├─ Console/Commands/CreateAdminCommand.php   perintah admin:create
├─ Exceptions/StockException.php             exception domain stok
├─ Http/
│  ├─ Controllers/
│  │  ├─ AdminController.php                 dashboard, produk, kategori, promo, kontak
│  │  ├─ Api/{CheckoutController, PaymentVerifyController, ProductController}.php
│  │  ├─ AuthController.php                  login/logout
│  │  ├─ CartController.php                  keranjang berbasis sesi
│  │  ├─ CatalogController.php               katalog publik
│  │  ├─ HealthController.php                /health
│  │  ├─ OrderController.php                 checkout, status, manajemen pesanan
│  │  └─ PaymentController.php               upload bukti, verifikasi, CRUD pembayaran
│  └─ Middleware/RoleMiddleware.php          role:ADMIN,OWNER
├─ Models/{Category,Order,OrderItem,Payment,Product,Promo,Setting,User}.php
database/migrations/                         12 migrasi
resources/views/                             25 blade (katalog, cart, admin, auth, dsb.)
routes/{web.php, api.php, console.php}
docs/                                        dokumentasi (API, manual, teknis, demo)
```

Logika stok & pembayaran sengaja diletakkan di **model** (`Order`, `Payment`), bukan di controller, agar semua jalur (web, API, admin) berperilaku sama.

---

## Perintah Berguna

```bash
php artisan serve                 # server pengembangan
npm run dev                       # vite watch
npm run build                     # build aset produksi
php artisan migrate               # migrasi
php artisan migrate:fresh --seed  # reset DB + data contoh (lokal!)
php artisan storage:link          # publikasi gambar produk
php artisan admin:create          # buat akun admin/owner
php artisan route:list            # daftar 54 route
php artisan optimize              # cache config+route+view (produksi)
php artisan optimize:clear        # bersihkan cache
./vendor/bin/pint                 # format kode
```

---

## Pengujian

```bash
php artisan test
```

Saat ini berisi 2 feature test dasar (aplikasi merespons 200 dan health check). Pengujian manual yang sudah dijalankan untuk alur kritis: stok 10 skenario, checkout bersamaan, klik ganda verifikasi (web: 302 pesan ramah; API: 409), penolakan pembayaran (stok kembali), dan endpoint API (401/422/200/409).

Lint seluruh file PHP:

```bash
find app routes database config bootstrap -name '*.php' -exec php -l {} \;
```

---

## API

Ringkas (dokumentasi lengkap: [`docs/API.md`](docs/API.md)):

| Method | Endpoint | Auth | Keterangan |
|---|---|---|---|
| `POST` | `/api/checkout` | publik (10 req/menit) | Buat pesanan dari daftar item |
| `POST` | `/api/admin/payment/{payment}/verify` | token Sanctum + `role:ADMIN,OWNER` | Verifikasi/tolak pembayaran |
| `GET` | `/health` | publik | Status aplikasi + koneksi DB |

Token dibuat dengan `$user->createToken('nama')->plainTextToken` dan dikirim sebagai `Authorization: Bearer <token>`.

---

## Deployment

Panduan lengkap: [`DEPLOY.md`](DEPLOY.md). Ringkasnya:

```bash
composer install --no-dev --optimize-autoloader
cp .env.production.example .env && php artisan key:generate
php artisan migrate --force
php artisan storage:link
npm ci && npm run build          # WAJIB: public/build tidak masuk git
php artisan admin:create ...
php artisan optimize
```

Empat hal yang paling sering membuat error di server: **`public/build` belum di-build**, **`storage:link` belum dijalankan**, **permission `storage/` & `bootstrap/cache/`**, dan **`SESSION_SECURE_COOKIE=true` padahal akses masih HTTP** (login seolah gagal).

---

## Dokumentasi Lain

| Dokumen | Isi |
|---|---|
| [`DEPLOY.md`](DEPLOY.md) | Langkah deploy produksi/staging, konfigurasi PHP/Nginx, checklist, rollback |
| [`docs/API.md`](docs/API.md) | Dokumentasi endpoint API + contoh request/response |
| [`docs/MANUAL-PENGGUNA.md`](docs/MANUAL-PENGGUNA.md) | Buku manual admin & pelanggan |
| [`docs/TEKNIS.md`](docs/TEKNIS.md) | Arsitektur, skema database, alur data, keputusan desain |
| [`docs/DEMO-SCRIPT.md`](docs/DEMO-SCRIPT.md) | Skenario demo 10 menit + antisipasi pertanyaan |
| [`docs/SLIDE-OUTLINE.md`](docs/SLIDE-OUTLINE.md) | Kerangka slide presentasi |
| [`docs/PERTANGGUNGJAWABAN.md`](docs/PERTANGGUNGJAWABAN.md) | Pertanggungjawaban individu & peta bukti artefak |

---

## Pemecahan Masalah

| Gejala | Penyebab umum | Solusi |
|---|---|---|
| Semua halaman 500, log `ViteManifestNotFoundException` | `public/build` belum ada (di-ignore git) | `npm ci && npm run build` |
| Gambar produk rusak (404) | symlink storage belum dibuat | `php artisan storage:link` |
| Login berhasil tapi kembali ke halaman login | `SESSION_SECURE_COOKIE=true` diakses via HTTP | set `false` atau pasang HTTPS |
| `SQLSTATE[HY000] [2002] Connection refused` | MySQL mati / kredensial salah | nyalakan MySQL, cek `DB_*` |
| Upload bukti bayar gagal / 413 | limit PHP/Nginx terlalu kecil | `upload_max_filesize=8M`, `post_max_size=10M`, `client_max_body_size 10m` |
| API selalu 401 | token salah/terhapus, atau user tidak punya trait `HasApiTokens` | buat token baru, cek `Authorization: Bearer` |
| Perubahan `.env` tidak berpengaruh | config ter-cache | `php artisan optimize:clear` |

---

## Tim Pengembang

| Nama | Peran |
|---|---|
| Diki Nurdiansyah | Frontend (tampilan, aset, UX) |
| Fauzan Aufa | Backend (database, logika bisnis, API, deployment) |

## Lisensi

Proyek ini dibuat untuk keperluan tugas UKK RPL dan pengembangan UMKM. Kode aplikasi milik pemilik proyek; Laravel sendiri berlisensi MIT.
