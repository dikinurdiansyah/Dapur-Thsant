# API Documentation — Katalog Order UMKM

Dokumentasi endpoint JSON yang tersedia saat ini.

**Base URL**

| Lingkungan | Base URL |
|---|---|
| Lokal | `http://127.0.0.1:8000/api` |
| Produksi | `https://domain-anda.com/api` |

**Header umum**

```
Accept: application/json
Content-Type: application/json
```

Autentikasi memakai **Laravel Sanctum personal access token**:

```
Authorization: Bearer <token>
```

---

## Daftar Endpoint

| # | Method | Endpoint | Auth | Rate limit | Keterangan |
|---|---|---|---|---|---|
| 1 | `POST` | `/api/checkout` | Publik | 10/menit per IP | Membuat pesanan |
| 2 | `POST` | `/api/admin/payment/{payment}/verify` | Sanctum + role `ADMIN`/`OWNER` | — | Verifikasi/tolak pembayaran |
| 3 | `GET` | `/health` (bukan `/api`) | Publik | — | Health check aplikasi & DB |

> **Catatan:** `App\Http\Controllers\Api\ProductController` sudah ada di kode tetapi **belum didaftarkan** di `routes/api.php`, sehingga endpoint produk (`GET /api/products`, `GET /api/products/{slug}`) **belum aktif**. Jika dibutuhkan, daftarkan sebagai route publik di `routes/api.php`.

---

## 1. Membuat Pesanan

```
POST /api/checkout
```

### Body

| Field | Tipe | Wajib | Aturan |
|---|---|---|---|
| `customer_name` | string | ✅ | maks 255 |
| `customer_phone` | string | ✅ | maks 20 |
| `fulfillment_method` | string | — | `DELIVERY` (default) atau `PICKUP` |
| `shipping_address` | string | ✅ jika `DELIVERY` | — |
| `pickup_note` | string | ✅ jika `PICKUP` | maks 255 |
| `payment_method` | string | ✅ | `TRANSFER_BANK` atau `COD` |
| `payment_provider` | string | ✅ jika `TRANSFER_BANK` | `BCA`, `MANDIRI`, `GOPAY`, `OVO`, `SHOPEEPAY`, `QRIS` |
| `items` | array | ✅ | minimal 1 item |
| `items[].product_id` | integer | ✅ | harus ada di tabel `products` |
| `items[].quantity` | integer | ✅ | minimal 1 |

### Contoh Request

```bash
curl -X POST http://127.0.0.1:8000/api/checkout \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "Budi Santoso",
    "customer_phone": "081234567890",
    "fulfillment_method": "DELIVERY",
    "shipping_address": "Jl. Merdeka No. 10, Bandung",
    "payment_method": "TRANSFER_BANK",
    "payment_provider": "BCA",
    "items": [
      { "product_id": 1, "quantity": 2 },
      { "product_id": 3, "quantity": 1 }
    ]
  }'
```

### Response `201 Created`

```json
{
  "status": "success",
  "message": "Pesanan berhasil dibuat",
  "data": {
    "id": 21,
    "order_ref": "ORD-8FJ2K1M9QX",
    "customer_name": "Budi Santoso",
    "customer_phone": "081234567890",
    "shipping_address": "Jl. Merdeka No. 10, Bandung",
    "fulfillment_method": "DELIVERY",
    "subtotal_amount": "45000.00",
    "discount_amount": "0.00",
    "total_amount": "57000.00",
    "payment_method": "TRANSFER_BANK",
    "payment_provider": "BCA",
    "status": "MENUNGGU_KONFIRMASI",
    "order_items": [
      {
        "id": 40,
        "order_id": 21,
        "product_id": 1,
        "price_at_purchase": "15000.00",
        "quantity": 2,
        "subtotal": "30000.00"
      }
    ]
  }
}
```

**Perhitungan total:** `subtotal + biaya kemasan Rp2.000 + ongkir Rp10.000 (DELIVERY)` dan `ongkir = 0` untuk `PICKUP`. Diskon dari kode promo tidak tersedia pada endpoint API ini.

### Response Error

| Status | Kondisi | Contoh body |
|---|---|---|
| `422` | Validasi gagal | `{"message":"The customer name field is required.","errors":{...}}` |
| `400` | Stok tidak cukup / produk nonaktif / produk tidak ditemukan | `{"status":"error","message":"Stok produk Dimsum Ayam tidak mencukupi."}` |
| `404` | Produk pada pesanan tidak ditemukan | `{"status":"error","message":"Produk pada pesanan tidak ditemukan."}` |
| `429` | Melebihi 10 request/menit | `{"message":"Too Many Requests"}` |

### Perilaku Stok

Seluruh item diproses dalam satu transaksi database:

1. `SELECT ... FOR UPDATE` pada setiap produk.
2. Cek `is_active` dan `stock >= quantity`.
3. `decrement('stock')` per item.
4. Buat `orders` + `order_items`.
5. Jika salah satu item gagal → **semua** dibatalkan (`rollBack`), stok tidak berubah.

---

## 2. Verifikasi / Tolak Pembayaran (Admin)

```
POST /api/admin/payment/{payment}/verify
Authorization: Bearer <token>
```

| Parameter | Lokasi | Tipe | Wajib | Nilai |
|---|---|---|---|---|
| `payment` | path | integer | ✅ | ID pembayaran |
| `status` | body | string | ✅ | `VERIFIED` atau `REJECTED` |
| `rejection_note` | body | string | — | alasan penolakan |

### Membuat token

```php
$token = $user->createToken('nama-perangkat')->plainTextToken;
// simpan & kirim sebagai: Authorization: Bearer <token>
```

Pastikan model `User` memakai trait `Laravel\Sanctum\HasApiTokens`.

### Contoh — verifikasi

```bash
curl -X POST http://127.0.0.1:8000/api/admin/payment/8/verify \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 7|xxxxxxxxxxxxxxxxxxxxxxxx" \
  -d "status=VERIFIED"
```

```json
{
  "status": "success",
  "message": "Pembayaran diverifikasi dan order ditandai SELESAI.",
  "data": {
    "id": 8,
    "order_id": 21,
    "amount_paid": "57000.00",
    "payment_status": "VERIFIED",
    "verified_at": "2026-11-15T10:22:31.000000Z"
  }
}
```

Efek: `payments.payment_status = VERIFIED`, `payments.verified_at = now()`, `orders.status = SELESAI`. Stok **tidak** berubah.

### Contoh — tolak

```bash
curl -X POST http://127.0.0.1:8000/api/admin/payment/9/verify \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d "status=REJECTED" \
  -d "rejection_note=Bukti transfer tidak terbaca"
```

Efek: `payment_status = REJECTED`, `orders.status = DIBATALKAN`, `rejection_reason` terisi, dan **stok dikembalikan tepat satu kali**.

### Response Error

| Status | Kondisi | Body |
|---|---|---|
| `401` | Tanpa token / token tidak valid | `{"message":"Unauthenticated."}` |
| `403` | Role bukan `ADMIN`/`OWNER` | `{"message":"Akses ditolak."}` |
| `404` | ID pembayaran tidak ada | `{"message":"No query results for model [App\\Models\\Payment] 99"}` |
| `422` | `status` kosong / nilai tidak valid | `{"message":"The status field is required.","errors":{...}}` |
| `409` | Pembayaran sudah diproses (klik ganda) | `{"status":"error","message":"Pembayaran ini sudah diproses sebelumnya."}` |
| `409` | Order sudah dibatalkan | `{"status":"error","message":"Order sudah dibatalkan, pembayaran tidak bisa diverifikasi."}` |

Verifikasi hasil uji (15 Nov 2026): `401` tanpa token, `422` tanpa `status`, `200` verifikasi pertama, `409` pada percobaan kedua — tanpa error 500.

---

## 3. Health Check

```
GET /health
```

```json
{
  "status": "ok",
  "system_name": "Katalog UMKM Saturn Core System",
  "sprint_stage": "Sprint 1 - Foundation Ready",
  "database_status": "CONNECTED",
  "active_products_seeded": 8,
  "latency_ms": 1.42,
  "timestamp": "2026-11-15T10:30:00+07:00"
}
```

Bila database tidak terhubung: HTTP `500` dengan `{"status":"error","database_status":"DISCONNECTED"}`.

---

## 4. Rate Limit

| Endpoint | Batas |
|---|---|
| `POST /api/checkout` | 10 request/menit per IP |
| `POST /login` | 5 request/menit |
| `POST /checkout` (web) | 10 request/menit |
| `POST /payments/guest-upload` | 5 request/menit |
| `GET /status*` | 30 request/menit |

Response melebihi batas: HTTP `429` dengan header `Retry-After`.

---

## 5. Format Kode Error Umum

| Kode | Arti |
|---|---|
| `200` | Sukses |
| `201` | Data dibuat |
| `400` | Permintaan valid tapi tidak bisa diproses (mis. stok kurang) |
| `401` | Belum terautentikasi |
| `403` | Tidak punya hak akses (role) |
| `404` | Data tidak ditemukan |
| `409` | Konflik status (sudah diproses) |
| `422` | Validasi gagal |
| `429` | Rate limit |
| `500` | Kesalahan server (cek `storage/logs/laravel.log`) |
