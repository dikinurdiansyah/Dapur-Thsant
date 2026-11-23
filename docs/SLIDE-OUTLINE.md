# Kerangka Slide Presentasi — Katalog Order UMKM (Dapur TH Sant)

14 slide, target **10 menit**. Setiap slide memuat: isi slide + catatan pembicara.

**Panduan visual:** konsisten 1 font (Inter/Calibri), warna utama cokelat–krem sesuai identitas Dapur TH Sant, maksimal 6 baris teks per slide, screenshot lebih banyak daripada teks.

---

## Slide 1 — Judul

**Isi**
- Judul: **Katalog Order UMKM — Dapur TH Sant**
- Subjudul: *Sistem Pemesanan & Verifikasi Pembayaran Berbasis Web*
- **Diki Nurdiansyah** — Frontend
- **Fauzan Aufa** — Backend
- Kelas, sekolah, tahun (2026/2027)

**Catatan:** "Selamat pagi. Kami dari tim pengembang aplikasi katalog dan pemesanan untuk UMKM dimsum Dapur TH Sant — saya Diki di bagian frontend, dan Fauzan di bagian backend."

---

## Slide 2 — Latar Belakang Masalah

**Isi (4 poin)**
- Pesanan masuk lewat chat → mudah terlewat & tidak tercatat rapi
- Pembayaran dicek manual, bukti transfer berserakan di galeri chat
- Stok sering bentrok saat dua pembeli memesan barang terakhir
- Tidak ada riwayat & status pesanan yang bisa dilihat pelanggan

**Catatan:** "Ini kondisi nyata toko rumahan. Masalah utamanya bukan jualan, tapi pencatatan."

---

## Slide 3 — Solusi

**Isi**
- Satu website: katalog → keranjang → checkout → unggah bukti → lacak status
- Pelanggan **tanpa akun**
- Admin: verifikasi pembayaran, kelola produk/promo, stok terjaga otomatis

**Catatan:** "Pelanggan tidak perlu daftar — ini keputusan desain, bukan kekurangan. Friksi rendah untuk pembeli pertama kali."

---

## Slide 4 — Fitur Utama (Sisi Pelanggan)

**Isi**
- Katalog + pencarian + filter kategori
- Keranjang sesi (tambah/ubah/hapus)
- Checkout: Delivery (ongkir Rp10.000) / Pickup, Transfer Bank / COD
- Kode promo (persen/nominal)
- Upload bukti transfer (maks 2 MB)
- Pelacakan status lewat kode referensi `ORD-XXXXXXXXXX`

**Catatan:** sebut promo `DEMO10` sebagai contoh nyata saat demo.

---

## Slide 5 — Fitur Utama (Sisi Admin)

**Isi**
- Dashboard ringkasan penjualan & stok
- CRUD produk, kategori, promo (slug otomatis)
- Manajemen pesanan + ubah status
- Verifikasi/tolak pembayaran (bukti transfer privat)
- Pengaturan kontak/outlet yang tampil publik
- Role `ADMIN` & `OWNER`

---

## Slide 6 — Alur Sistem (Diagram)

**Isi:** diagram sederhana 5 kotak berpanah

```
Katalog → Keranjang → Checkout → Upload Bukti → Verifikasi Admin → Selesai
                          │                            │
                    potong stok                 tolak = stok kembali
```

**Catatan:** "Stok dipotong saat checkout, bukan saat verifikasi — untuk mencegah overselling."

---

## Slide 7 — Arsitektur Teknologi

**Isi**
- Laravel 12 (PHP 8.2), MySQL 8
- Blade + Bootstrap 5 + Vite/Tailwind
- Sanctum untuk API token
- 12 migrasi, 54 route, 25 view
- Logika stok & pembayaran di model (bukan controller)

**Catatan:** sebut alasan: satu sumber kebenaran untuk web & API.

---

## Slide 8 — Skema Database

**Isi:** ERD ringkas

```
categories ─ products ─┐
promos ────────────────┼─ order_items ─ orders ─ payments
users (ADMIN/OWNER) ───┘        (1 order : 1 payment)
settings
```

**Catatan:** "order_items menyimpan harga saat transaksi, jadi riwayat tidak berubah saat harga produk naik."

---

## Slide 9 — Tantangan Teknis: Konsistensi Stok

**Isi**
- Masalah: dua checkout bersamaan → stok bisa minus / dobel
- Solusi:
  - `DB::transaction` + `SELECT ... FOR UPDATE`
  - potong stok di dalam transaksi
  - `cancelWithReason()` idempoten → stok kembali sekali
  - `reactivate()` memotong ulang, gagal dengan pesan bila stok kurang

**Catatan:** ini slide nilai jual teknis — tunjukkan kode model `Order` di lampiran, bukan di slide.

---

## Slide 10 — Tantangan Teknis: Klik Ganda Verifikasi

**Isi**
- Kasus: admin klik dua kali / dua admin bersamaan
- Sebelum: berpotensi error 500 dan data ganda
- Sesudah:
  - guard status di controller
  - `RuntimeException` di model
  - web → pesan ramah (HTTP 302)
  - API → HTTP **409** dengan pesan jelas

**Catatan:** "Diuji nyata: verifikasi kedua tetap 302/409, pesanan tetap satu kali selesai, stok tidak berubah."

---

## Slide 11 — Keamanan

**Isi**
- Bukti transfer di storage **privat**, dibuka lewat route ber-otentikasi
- Nominal pembayaran ditentukan server
- Order guest dari sesi → tidak bisa menempel bukti ke pesanan orang lain
- Kode referensi acak → tidak bisa ditebak
- CSRF, escaping Blade, `$fillable`, rate limit (login 5/menit, checkout 10/menit)
- `APP_DEBUG=false` di produksi

---

## Slide 12 — Pengujian

**Isi (tabel)**

| Uji | Hasil |
|---|---|
| Lint seluruh PHP | 0 error |
| `php artisan test` | 2 passed |
| 10 skenario stok | sesuai harapan |
| Checkout bersamaan | stok konsisten |
| Klik ganda verifikasi (web) | 302, bukan 500 |
| Klik ganda verifikasi (API) | 409 |
| Tolak pembayaran | stok kembali, order dibatalkan |
| API tanpa token / tanpa field / sukses | 401 / 422 / 200 |

**Catatan:** jujur sebutkan yang **belum** diuji: beban tinggi, server produksi nyata, test otomatis alur kritis.

---

## Slide 13 — Deployment

**Isi**
- Server: Nginx + PHP-FPM 8.2 + MySQL 8, HTTPS
- Langkah kunci: `migrate --force`, `storage:link`, `npm run build`, `optimize`
- Perhatian: `SESSION_SECURE_COOKIE` hanya untuk HTTPS; permission `storage/`
- Health check `/health`

**Catatan:** "Tiga penyebab error deploy paling umum: aset belum di-build, symlink storage belum dibuat, permission folder."

---

## Slide 14 — Penutup & Rencana Lanjutan

**Isi**
- Kesimpulan: alur pesan → verifikasi berjalan utuh dengan data konsisten
- Rencana: notifikasi WhatsApp/email, laporan penjualan & ekspor, payment gateway, test otomatis lebih lengkap
- Terima kasih + kontak

**Catatan:** akhiri dengan "Saya siap menjawab pertanyaan" — jangan menambahkan slide baru.

---

## Lampiran (tidak dipresentasikan, disiapkan untuk tanya jawab)

1. Screenshot kode `Order::cancelWithReason()` dan `Payment::markVerified()`.
2. Screenshot log bersih setelah uji klik ganda.
3. Screenshot `php artisan route:list`.
4. Screenshot `php artisan test` lulus.
5. Daftar route API + contoh `curl`.
6. Tautan dokumen: `README.md`, `DEPLOY.md`, `docs/API.md`, `docs/TEKNIS.md`.

---

## Tips Penyajian

- 1 slide ≈ 40 detik. Jika lewat 1 menit, potong narasi.
- Jangan membaca slide; slide = kata kunci, mulut = penjelasan.
- Siapkan **demo cadangan** (video) sebelum mulai.
- Saat ditanya hal yang belum dibuat, jawab: "Belum ada, dan ini rencana pengembangannya" — jangan mengarang.
