# Pertanggungjawaban Individu & Peta Bukti Artefak

Dokumen ini memetakan delapan butir tugas pada portal UKK ke kondisi nyata proyek, beserta bukti yang bisa ditunjukkan saat penilaian.

**Proyek:** Katalog Order UMKM — Dapur TH Sant
**Repository:** `dikinurdiansyah/katalog-order-umkm-saturn` (branch `main`)
**Stack:** Laravel 12 · MySQL 8 · Blade · Bootstrap 5 · Vite/Tailwind
**Periode:** RPL 2026/2027

**Tim Pengembang**

| Nama | Peran | Fokus utama |
|---|---|---|
| **Diki Nurdiansyah** | Frontend | Tampilan Blade, CSS/aset, UX alur pelanggan & admin |
| **Fauzan Aufa** | Backend | Skema database, controller, logika stok/pembayaran, API, keamanan, deploy |

---

## Ringkasan Status

| # | Tugas | Status | Bukti utama |
|---|---|---|---|
| 1 | Final Bug Fixing (Release Candidate) | ✅ Selesai & terverifikasi | Daftar perbaikan + hasil uji di §1 |
| 2 | Production / Staging Deployment | 🟡 Siap deploy, belum dijalankan di server | `DEPLOY.md`, `.env.production.example` |
| 3 | Project Documentation (Manual/Tech Docs) | ✅ Selesai | `docs/MANUAL-PENGGUNA.md`, `docs/TEKNIS.md` |
| 4 | Repository README Lengkap | ✅ Selesai | `README.md` |
| 5 | API Documentation | ✅ Selesai | `docs/API.md` |
| 6 | Persiapan Slide Presentasi | ✅ Kerangka selesai | `docs/SLIDE-OUTLINE.md` (perlu dipindah ke PowerPoint/Canva) |
| 7 | Demo Rehearsal (Latihan Demo) | ✅ Naskah siap, latihan perlu dijalankan | `docs/DEMO-SCRIPT.md` |
| 8 | Pertanggungjawaban Individu | ✅ Dokumen ini | `docs/PERTANGGUNGJAWABAN.md` |

Keterangan: ✅ = artefak sudah ada di repositori; 🟡 = artefak panduan sudah ada, eksekusi bergantung pada akses server.

---

## 1. Final Bug Fixing (Release Candidate)

### Bug yang diperbaiki

| # | Bug | Dampak | Perbaikan |
|---|---|---|---|
| 1 | Pembatalan pesanan mengembalikan stok dua kali | Stok tidak sesuai fisik | `Order::cancelWithReason()` idempoten + `lockForUpdate` dalam transaksi |
| 2 | Pesanan batal tidak bisa diaktifkan kembali | Admin macet | `Order::reactivate()` memotong stok ulang, gagal rapi bila stok kurang (`StockException`) |
| 3 | Perubahan status pembayaran tersebar di controller | Inkonsistensi status order | `Payment::markVerified()` / `markRejected()` sebagai satu-satunya jalur transisi |
| 4 | Perubahan baris pembayaran + pembatalan order tidak atomik | Data setengah jadi bila gagal | `PaymentController@update` dibungkus `DB::transaction`; file bukti lama dihapus **setelah** commit |
| 5 | Klik ganda verifikasi berpotensi error 500 | Admin melihat error, data tidak jelas | Guard status + tangkap `RuntimeException` → pesan ramah (web 302) dan 409 (API) |
| 6 | Endpoint API verifikasi selalu 401 | API tidak bisa dipakai | Trait `Laravel\Sanctum\HasApiTokens` ditambahkan ke model `User` |
| 7 | `created_at`/`updated_at` tidak terisi pada `Category` & `Promo` | Data tidak terlacak waktu | Konfigurasi timestamps model |
| 8 | Slug produk/kategori bisa duplikat / tidak valid | Konflik URL & error 500 | Slug dihitung sebelum validasi + aturan unik |
| 9 | Orphan receipt (file tanpa baris pembayaran) | Sampah penyimpanan | File dihapus saat data pembayaran/pesanan dihapus |
| 10 | Session binding di halaman sukses publik | Bukti bayar bisa ditempel ke pesanan orang lain | Order guest diambil dari sesi, bukan dari `order_id` form |

### Hasil pengujian

| Uji | Hasil |
|---|---|
| Lint seluruh file PHP (`php -l`) | **0 error** |
| `php artisan test` | **2 passed** |
| Skenario stok (10 kasus) | sesuai harapan |
| Klik ganda verifikasi via web | **302** + pesan "sudah diproses", bukan 500; order tetap `SELESAI`, stok tetap |
| Klik ganda verifikasi via API | **409** dengan pesan jelas |
| Tolak pembayaran (form admin) | **302**, payment `REJECTED`, order `DIBATALKAN`, stok kembali **48 → 50**, alasan tersimpan |
| PUT kedua pada status final | **302**, bukan 500 |
| API: tanpa token / tanpa `status` / sukses / ganda | **401 / 422 / 200 / 409** |
| Log setelah pengujian | tanpa `ERROR`/`SQLSTATE` |

**Artefak bukti:** tangkapan layar hasil uji, `git log --oneline`, dan `storage/logs/laravel.log` yang bersih.

---

## 2. Production / Staging Deployment

**Sudah disiapkan**
- `DEPLOY.md`: langkah lengkap (clone → composer → env → migrate → storage:link → build → admin:create → optimize), konfigurasi Nginx & PHP, checklist go-live, rollback, backup.
- `.env.production.example`: template produksi terpisah dari `.env.example` lokal.
- `.env.example`: template lokal (MySQL lokal, `SESSION_SECURE_COOKIE=false`).
- `composer setup`: instalasi satu perintah termasuk `storage:link`.
- `php artisan admin:create`: pembuatan akun admin tanpa seeder.
- `bootstrap/app.php`: `trustProxies(at: '*')` untuk reverse proxy/HTTPS.
- Health check `GET /health` untuk memantau aplikasi + koneksi database.

**Belum dijalankan** (butuh akses server/domain): deploy nyata ke VPS/staging dan uji HTTPS.

**Bukti saat penilaian:** jalankan `curl https://domain/health` dan tunjukkan halaman login produksi, atau tunjukkan `DEPLOY.md` beserta screenshot server bila sudah tersedia.

---

## 3. Project Documentation (Manual & Tech Docs)

| Dokumen | Isi |
|---|---|
| `docs/MANUAL-PENGGUNA.md` | Panduan pelanggan (katalog → checkout → upload bukti → lacak status) dan admin (produk, kategori, promo, pesanan, pembayaran, kontak), alur harian, larangan |
| `docs/TEKNIS.md` | Arsitektur lapisan, skema 11 tabel, alur data kritis (checkout, upload, verifikasi, pembatalan), keputusan desain + alasan, keamanan, keterbatasan, rencana lanjutan |

---

## 4. Repository README Lengkap

`README.md` memuat: deskripsi & fitur, teknologi, kebutuhan sistem, instalasi lokal, akun & login, alur bisnis (diagram), status pesanan/pembayaran, struktur proyek, perintah berguna, pengujian, ringkasan API, deployment, tautan dokumentasi lain, tabel pemecahan masalah, lisensi.

---

## 5. API Documentation

`docs/API.md` memuat seluruh endpoint aktif:

| Method | Endpoint | Auth |
|---|---|---|
| `POST` | `/api/checkout` | publik (throttle 10/menit) |
| `POST` | `/api/admin/payment/{payment}/verify` | Sanctum + role `ADMIN`/`OWNER` |
| `GET` | `/health` | publik |

Isi: aturan validasi per field, contoh `curl`, contoh response sukses, tabel kode error (400/401/403/404/409/422/429/500), efek samping ke stok & status, serta catatan bahwa `Api\ProductController` belum terdaftar di route.

---

## 6. Persiapan Slide Presentasi

`docs/SLIDE-OUTLINE.md`: 14 slide (judul, masalah, solusi, fitur pelanggan, fitur admin, alur, arsitektur, ERD, konsistensi stok, klik ganda, keamanan, pengujian, deployment, penutup) + lampiran dan tips penyajian.

**Sisa pekerjaan:** memindahkan kerangka ke PowerPoint/Canva dan menempelkan screenshot aplikasi.

---

## 7. Demo Rehearsal

`docs/DEMO-SCRIPT.md`: checklist H-30 menit, struktur 10 menit per menit, naskah per bagian, antisipasi 10 pertanyaan penguji, dan rencana cadangan bila demo gagal.

**Sisa pekerjaan:** latihan minimal 2× dengan stopwatch dan menyiapkan rekaman video cadangan.

---

## 8. Pertanggungjawaban Individu

### Pembagian Tugas

| Area | Diki Nurdiansyah (Frontend) | Fauzan Aufa (Backend) |
|---|---|---|
| Analisis kebutuhan | Ikut merumuskan alur pelanggan & tata letak panel | Ikut merumuskan aturan status pesanan/pembayaran |
| Basis data | — | 12 migrasi, 8 model, relasi, cast, index |
| Backend | — | Controller, middleware role, `admin:create`, `/health` |
| Logika domain | — | Idempotensi stok, transisi pembayaran, promo |
| Keamanan | Masking/penyajian data sensitif di UI | File privat, sesi, validasi, rate limit |
| API | Konsumsi & uji response dari sisi klien | Endpoint JSON, Sanctum, 409 |
| Frontend | 25 view Blade, CSS, aset, responsif | Menyediakan data & validasi untuk view |
| Kualitas | Uji tampilan lintas ukuran layar, cek alur klik | Lint, feature test, uji stok & klik ganda |
| Dokumentasi | Manual pengguna, kerangka slide, skrip demo | README, dokumen teknis, API, panduan deploy |
| Demo | Menjalankan demo & narasi | Menyiapkan data uji & penanganan masalah teknis |

### Kontribusi Diki Nurdiansyah — Frontend

1. **25 view Blade**: katalog, detail produk, keranjang, checkout, sukses, status pesanan, kontak, login, serta seluruh panel admin (dashboard, produk, kategori, promo, pesanan, pembayaran).
2. **Identitas visual**: Bootstrap 5 + CSS kustom bertema cokelat–krem, Bootstrap Icons, Google Fonts, tata letak responsif (mobile-first).
3. **Integrasi aset**: 3 halaman memakai `@vite`, termasuk build Tailwind lewat `npm run build`.
4. **UX form**: pesan validasi per field, flash success/error, konfirmasi hapus, indikator stok, badge status berwarna, tampilan bukti transfer.
5. **Konsistensi data di layar**: harga terformat rupiah, ringkasan biaya (subtotal, kemasan, ongkir, diskon), kode referensi ditampilkan menonjol setelah checkout.

**Bukti:** `resources/views/**` (25 file), `resources/css/app.css`, `vite.config.js`, screenshot halaman.

### Kontribusi Fauzan Aufa — Backend

1. **Basis data**: 12 migrasi (users, categories, products, promos, orders, order_items, payments, settings, cache, jobs, token) dan 8 model dengan relasi + cast.
2. **Controller**: Catalog, Cart, Order, Payment, Admin, Auth, serta `Api\CheckoutController` dan `Api\PaymentVerifyController`.
3. **Logika domain**: `Order::cancelWithReason()` (idempoten), `Order::reactivate()` (+`StockException`), `Payment::markVerified()`/`markRejected()`, `Promo::calculateDiscount()`.
4. **Konsistensi data**: `DB::transaction` + `lockForUpdate` pada checkout, pembatalan, verifikasi, dan perubahan pembayaran.
5. **Keamanan & API**: Sanctum + middleware `role:ADMIN,OWNER`, bukti bayar di disk privat, order guest dari sesi, nominal server-side, kode referensi acak, rate limit, respons 409 untuk klik ganda.
6. **Operasional**: perintah `admin:create`, health check, `composer setup`, template `.env` lokal/produksi, `trustProxies`, panduan deploy.

**Bukti:** `app/**`, `database/migrations/**`, `routes/**`, `config/**`, `bootstrap/app.php`, `DEPLOY.md`, `docs/API.md`, `docs/TEKNIS.md`, hasil `php artisan test`.

### Kompetensi yang dibuktikan

1. **Membangun aplikasi web utuh** — dari skema database sampai antarmuka dan deployment.
2. **Menjaga integritas data** — transaksi, penguncian baris, idempotensi.
3. **Menangani kasus batas** — klik ganda, dua admin bersamaan, stok tidak cukup, pesanan batal.
4. **Keamanan dasar** — otorisasi role, file privat, validasi input, rate limit.
5. **Dokumentasi & komunikasi teknis** — menulis dokumen yang bisa dipakai orang lain.

### Pernyataan

Kami menyatakan bahwa proyek ini dikerjakan bersama sesuai pembagian peran di atas. Masing-masing memahami bagian yang dikerjakan dan mampu menjelaskan alasan di balik keputusan teknisnya. Bagian yang belum selesai disebutkan secara jujur pada `docs/TEKNIS.md` §7, bukan disembunyikan.

**Diki Nurdiansyah — Frontend**

Saya mengerjakan seluruh antarmuka aplikasi (25 view Blade, CSS, aset, dan UX alur pelanggan maupun admin), serta menyusun manual pengguna, kerangka slide, dan skrip demo. Saya memahami bagaimana data dari backend ditampilkan, termasuk validasi, status pesanan, dan status pembayaran.

| | |
|---|---|
| Nama | Diki Nurdiansyah |
| Peran | Frontend |
| Kelas | ________________________ |
| Tanggal | ________________________ |
| Tanda tangan | ________________________ |

**Fauzan Aufa — Backend**

Saya mengerjakan skema database, controller, logika stok dan pembayaran, endpoint API, keamanan, serta konfigurasi deployment. Saya memahami transaksi, penguncian baris, dan idempotensi yang menjaga data pesanan tetap konsisten.

| | |
|---|---|
| Nama | Fauzan Aufa |
| Peran | Backend |
| Kelas | ________________________ |
| Tanggal | ________________________ |
| Tanda tangan | ________________________ |

---

## Lampiran — Daftar Artefak untuk Penilaian

| Artefak | Lokasi |
|---|---|
| Kode aplikasi | `app/`, `routes/`, `resources/views/`, `database/migrations/` |
| README | `README.md` |
| Panduan deploy | `DEPLOY.md` |
| Template env produksi | `.env.production.example` |
| Dokumentasi API | `docs/API.md` |
| Manual pengguna | `docs/MANUAL-PENGGUNA.md` |
| Dokumentasi teknis | `docs/TEKNIS.md` |
| Kerangka slide | `docs/SLIDE-OUTLINE.md` |
| Skrip demo | `docs/DEMO-SCRIPT.md` |
| Dokumen ini | `docs/PERTANGGUNGJAWABAN.md` |
| Riwayat perubahan | `git log --oneline` |
| Hasil uji | `php artisan test`, screenshot uji manual |
