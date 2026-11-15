# Dokumentasi Teknis — Katalog Order UMKM (Dapur TH Sant)

Dokumen ini menjelaskan arsitektur, skema database, alur data, dan keputusan desain. Untuk cara pakai lihat [`MANUAL-PENGGUNA.md`](MANUAL-PENGGUNA.md); untuk endpoint lihat [`API.md`](API.md).

---

## 1. Ringkasan Teknis

| Aspek | Keterangan |
|---|---|
| Framework | Laravel 12.69 (PHP ^8.2) |
| Pola | MVC + service logic di Model, bukan di Controller |
| Database | MySQL 8, 12 migrasi, utf8mb4 |
| Autentikasi web | Session guard bawaan Laravel (tabel `users`, kolom `password_hash`) |
| Autentikasi API | Sanctum personal access token |
| Otorisasi | Middleware `role:ADMIN,OWNER` |
| Tampilan | Blade, 25 view, Bootstrap 5 (CDN) + CSS kustom; Vite/Tailwind untuk 3 halaman |
| Penyimpanan file | `public` disk (gambar produk) dan `local`/private disk (bukti transfer) |
| Pengujian | PHPUnit 11 (2 feature test) |
| Total berkas PHP | 67 (app, routes, database, views) |

---

## 2. Arsitektur Lapisan

```
Request
  │
  ├─ routes/web.php ............. 54 route (public, auth, admin)
  ├─ routes/api.php ............. 2 route (checkout publik, verifikasi admin)
  │
  ├─ Middleware
  │   ├─ auth / auth:sanctum
  │   ├─ RoleMiddleware (role:ADMIN,OWNER)
  │   └─ throttle:* (login, checkout, upload, status)
  │
  ├─ Controller (validasi input, orkestrasi, response)
  │   ├─ CatalogController / CartController ...... baca katalog, sesi keranjang
  │   ├─ OrderController .......................... checkout, status, CRUD pesanan
  │   ├─ PaymentController ........................ upload, verifikasi, CRUD pembayaran
  │   ├─ AdminController .......................... dashboard, produk, kategori, promo, kontak
  │   ├─ AuthController ........................... login/logout
  │   └─ Api\{Checkout,PaymentVerify}Controller ... versi JSON
  │
  ├─ Model (aturan domain: stok, status, diskon)
  │   ├─ Order::{restoreStock, cancelWithReason, reactivate, calculateTotal}
  │   ├─ Payment::{markVerified, markRejected}
  │   └─ Promo::{isValid, calculateDiscount, scopeActive}
  │
  └─ Database (MySQL, transaksi + lockForUpdate)
```

**Prinsip:** aturan bisnis yang menyentuh uang/stok diletakkan di model agar jalur web dan API tidak bisa berperilaku berbeda.

---

## 3. Skema Database

### 3.1 Relasi

```
categories 1 ──── n products
promos     1 ──── n orders
orders     1 ──── n order_items ──── n 1 products
orders     1 ──── 1 payments
users (ADMIN/OWNER)  ── managed_by ─▶ orders
users 1 ──── n personal_access_tokens
settings (key-value, JSON)
```

### 3.2 Tabel

**users**
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint PK | |
| name | string | |
| email | string | unik, dipakai login |
| password_hash | string | hash bcrypt (nama kolom kustom, bukan `password`) |
| role | enum(`ADMIN`,`OWNER`) | dasar otorisasi |
| phone_number | string nullable | |
| remember_token | string nullable | migrasi terpisah |
| timestamps | | |

**categories**
| Kolom | Tipe |
|---|---|
| id, name, slug (unik), timestamps | |

**products**
| Kolom | Tipe | Catatan |
|---|---|---|
| id | PK | |
| category_id | FK → categories | |
| name, slug | string | slug unik, dibuat otomatis |
| description | text | |
| price | decimal(12,2) | |
| stock | integer | dipotong saat checkout, dikembalikan saat batal |
| image_url | string | path relatif di disk `public` |
| is_active | boolean | produk nonaktif tidak tampil & tidak bisa dibeli |
| timestamps | | |

**promos**
| Kolom | Tipe | Catatan |
|---|---|---|
| id | PK | |
| code | string | disimpan huruf besar |
| discount_type | enum(`FIXED`,`PERCENT`) | |
| discount_amount | decimal(12,2) | persen (0–100) atau nominal rupiah |
| min_order_amount | decimal(12,2) | syarat minimal subtotal |
| is_active | boolean | |
| valid_until | date | berlaku sampai akhir hari tersebut |
| timestamps | | |

**orders**
| Kolom | Tipe | Catatan |
|---|---|---|
| id | PK | |
| order_ref | string | `ORD-` + 10 karakter acak, dipakai sebagai kode lacak (bukan ID berurutan) |
| customer_name, customer_phone | string | |
| shipping_address | text | untuk PICKUP berisi catatan pengambilan |
| fulfillment_method | enum(`DELIVERY`,`PICKUP`) | |
| promo_id | FK nullable → promos | |
| subtotal_amount, discount_amount, total_amount | decimal(12,2) | |
| payment_method | enum(`TRANSFER_BANK`,`COD`) | |
| payment_provider | string nullable | BCA/MANDIRI/GOPAY/OVO/SHOPEEPAY/QRIS |
| status | enum | `MENUNGGU_KONFIRMASI`,`DIPROSES`,`DIKIRIM`,`SELESAI`,`DIBATALKAN` |
| rejection_reason | text nullable | alasan pembatalan/penolakan |
| managed_by | FK nullable → users | |
| timestamps | | |

**order_items**
`id, order_id (FK), product_id (FK), price_at_purchase decimal(12,2), quantity int, subtotal decimal(12,2)`

Harga disimpan saat transaksi (`price_at_purchase`) agar riwayat pesanan tidak berubah saat harga produk diubah.

**payments**
| Kolom | Tipe | Catatan |
|---|---|---|
| id | PK | |
| order_id | FK → orders | satu pembayaran per order |
| amount_paid | decimal(12,2) | untuk upload guest diisi dari `order.total_amount` (server-side) |
| receipt_image_path | string | path privat di `storage/app/private/receipts` |
| payment_status | enum(`PENDING_REVIEW`,`VERIFIED`,`REJECTED`) | |
| admin_notes | text nullable | alasan penolakan |
| verified_at | datetime nullable | |
| created_at | timestamp | **tanpa** `updated_at` (`$timestamps = false`) |

**settings**
`id, key (unik), value (JSON cast), timestamps` — dipakai untuk `outlet_info` (halaman kontak publik).

**personal_access_tokens**, **cache**, **jobs** — tabel bawaan Laravel/Sanctum.

---

## 4. Alur Data Kritis

### 4.1 Checkout (web & API)

```
1. Validasi input (nama, telepon, fulfillment, metode bayar, item)
2. DB::beginTransaction()
3. Untuk setiap item:
     Product::lockForUpdate()->find(id)
     cek is_active dan stock >= quantity  → gagal = exception
     decrement stock
4. Hitung subtotal; promo (web): calculateDiscount bila valid
5. Hitung total: subtotal − diskon + kemasan 2.000 + ongkir 10.000 (DELIVERY)
6. Order::create (order_ref otomatis via booted hook)
7. OrderItem::create per baris
8. DB::commit()
9. Arahkan ke halaman sukses dengan kode referensi
   (web: session last_order_id; tidak disimpan di halaman sukses publik)
```

Jika langkah mana pun gagal → `rollBack()`; stok tidak berkurang.

### 4.2 Upload Bukti Transfer (guest)

```
1. Order diambil dari session('last_order_id')  ← bukan order_id dari form
2. Validasi: order ada, metode TRANSFER_BANK, bukan DIBATALKAN
3. Validasi file: image, jpeg/png/jpg, max 2048 KB, dimensi ≤ 4000px
4. Tolak bila order sudah punya payment
5. Simpan file ke disk 'local' (storage/app/private/receipts)
6. Payment::create(amount_paid = order.total_amount, PENDING_REVIEW)
7. Order → DIPROSES
8. Redirect ke halaman status
```

Mengambil order dari sesi mencegah pelanggan menempelkan bukti bayar ke pesanan orang lain.

### 4.3 Verifikasi Pembayaran

```
PaymentController@verify (web) / PaymentVerifyController (API)
1. Cek payment_status masih PENDING_REVIEW  → jika tidak: pesan ramah / 409
2. VERIFIED: Payment::markVerified()
     ├─ update payment: VERIFIED + verified_at
     └─ update order: SELESAI
   REJECTED: Payment::markRejected(note)
     ├─ update payment: REJECTED + admin_notes
     └─ Order::cancelWithReason(note)  → stok kembali (idempoten)
3. RuntimeException dari model ditangkap → pesan, bukan 500
```

### 4.4 Pembatalan & Reaktivasi Pesanan

`cancelWithReason()`:
- guard: bila sudah `DIBATALKAN` → return false (tidak menambah stok dua kali)
- dalam transaksi: `lockForUpdate` order, kembalikan stok semua item, set `DIBATALKAN` + alasan.

`reactivate()`:
- bila status bukan `DIBATALKAN` → cukup update status
- bila dari `DIBATALKAN` → cek ulang stok semua item (lock), potong stok, set status baru, hapus alasan
- bila stok kurang / produk terhapus → `StockException` dengan pesan spesifik.

---

## 5. Keputusan Desain & Alasannya

| Keputusan | Alasan |
|---|---|
| Checkout tanpa akun pelanggan | Target pengguna UMKM; menurunkan friksi pembelian. Data kontak dikumpulkan saat checkout. |
| `order_ref` acak, bukan ID | Mencegah enumerasi: pengunjung tidak bisa menebak pesanan orang lain dari angka berurutan. |
| Order guest diambil dari sesi, bukan field form | Menutup celah menempelkan bukti bayar ke pesanan milik orang lain. |
| Nominal pembayaran ditentukan server | Pelanggan tidak bisa mengubah nominal bayar menjadi lebih kecil. |
| Bukti transfer di disk privat + route ber-otentikasi | File berisi data pribadi; tidak boleh dapat diakses tanpa login. |
| Stok dipotong saat checkout (bukan saat verifikasi) | Menghindari overselling saat banyak pesanan masuk bersamaan. |
| `lockForUpdate` + transaksi | Mencegah dua checkout bersamaan membaca stok yang sama. |
| Logika stok di model, bukan controller | Satu sumber kebenaran untuk jalur web, API, admin; memudahkan idempotensi. |
| Guard + `RuntimeException` berlapis | Klik ganda tetap aman walaupun dua request lolos guard secara bersamaan. |
| Registrasi publik ditutup | Aplikasi B2C sederhana; akun admin dikelola internal. |
| `password_hash` sebagai nama kolom | Menegaskan isi kolom selalu hash (tidak pernah plaintext). |
| Rate limiting pada endpoint rawan | Mencegah spam checkout, brute force login, dan abuse upload. |

---

## 6. Keamanan

| Risiko | Mitigasi |
|---|---|
| SQL injection | Eloquent/query builder + bound parameter |
| XSS | Escaping Blade (`{{ }}`) |
| CSRF | Middleware `VerifyCsrfToken` bawaan + `@csrf` di semua form |
| Mass assignment | `$fillable` eksplisit per model |
| Akses tidak sah ke admin | `auth` + `role:ADMIN,OWNER` |
| Akses file bukti bayar | Disk privat + route `admin/payments/{payment}/receipt` ber-otentikasi |
| Brute force login | `throttle:5,1` |
| Spam pesanan | `throttle:10,1` pada checkout |
| Stok negatif | Validasi + `lockForUpdate` dalam transaksi |
| Token API bocor | Token disimpan ter-hash di DB, bisa dicabut per perangkat |
| Informasi error ke publik | `APP_DEBUG=false` di produksi |

---

## 7. Keterbatasan yang Diketahui

1. **Belum ada notifikasi otomatis** (email/WhatsApp) — pelanggan harus membuka `/status`.
2. **`Api\ProductController` belum terdaftar** di `routes/api.php`; endpoint produk belum aktif.
3. **Satu pembayaran per pesanan** — penggantian bukti dilakukan admin.
4. **Belum ada integrasi payment gateway** — verifikasi masih manual oleh admin.
5. **Bukti bayar terikat sesi** — jika sesi pelanggan hilang sebelum upload, admin harus upload manual.
6. **Test otomatis masih minimal** (2 feature test); pengujian alur kritis dilakukan manual.
7. **`GET /health` memakai koneksi DB langsung** — jangan dipakai sebagai liveness probe yang terlalu sering.

---

## 8. Rencana Pengembangan Lanjutan

- Notifikasi WhatsApp/email saat status berubah.
- Endpoint API produk + dokumentasi OpenAPI (Swagger).
- Laporan penjualan periode (harian/bulanan) dan ekspor CSV.
- Multi-outlet & manajemen stok per outlet.
- Upload ulang bukti bayar oleh pelanggan dengan token unik per pesanan.
- Test otomatis untuk stok, pembatalan, dan verifikasi ganda (feature test berbasis database).
