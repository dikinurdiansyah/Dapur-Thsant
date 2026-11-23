# Buku Manual Pengguna — Katalog Order UMKM (Dapur TH Sant)

Panduan penggunaan aplikasi untuk **Pelanggan** dan **Admin/Owner**.

---

## Bagian A — Untuk Pelanggan

Pelanggan **tidak perlu membuat akun atau login**. Cukup buka website.

### A1. Melihat Katalog

1. Buka halaman utama (`/`).
2. Produk tampil dalam bentuk kartu berisi gambar, nama, harga, dan sisa stok.
3. Gunakan kolom pencarian atau filter kategori untuk mempersempit pilihan.
4. Klik kartu produk untuk melihat detail (`/products/{id}`).

### A2. Menambah ke Keranjang

1. Dari katalog atau halaman detail, klik **Tambah ke Keranjang**.
2. Bila jumlah melebihi stok, muncul pesan *"Stok tidak mencukupi."*
3. Buka ikon keranjang di kanan atas untuk melihat isi keranjang.
4. Di halaman keranjang (`/cart`) Anda bisa:
   - mengubah jumlah (tombol **+ / −**),
   - menghapus satu item (**Hapus**),
   - mengosongkan seluruh keranjang.

> Keranjang disimpan di sesi browser. Menghapus cookie/berganti browser akan mengosongkan keranjang.

### A3. Checkout

1. Klik **Checkout** dari halaman keranjang.
2. Isi data:
   - **Nama penerima** (wajib)
   - **Nomor telepon/WhatsApp** (wajib)
   - **Metode penerimaan**:
     - **Delivery** → isi **alamat lengkap** (ongkir Rp10.000)
     - **Ambil di tempat (Pickup)** → isi **catatan/pesan pengambilan** (tanpa ongkir)
   - **Metode pembayaran**:
     - **Transfer Bank** → pilih bank/e-wallet: BCA, Mandiri, GoPay, OVO, ShopeePay, QRIS
     - **COD / Bayar di tempat**
   - **Kode promo** (opsional) — contoh: masukkan kode yang diberikan admin.
3. Ringkasan biaya: `subtotal + kemasan Rp2.000 + ongkir (bila delivery) − diskon`.
4. Klik **Buat Pesanan**.

Bila berhasil, Anda diarahkan ke halaman sukses yang menampilkan **kode referensi** (contoh `ORD-8FJ2K1M9QX`). **Simpan kode ini.**

### A4. Mengunggah Bukti Transfer

Hanya untuk pesanan dengan metode **Transfer Bank**.

1. Pada halaman sukses pesanan, klik **Upload Bukti Pembayaran**.
2. Pilih foto/screenshot bukti transfer:
   - format **JPG/PNG/JPEG**
   - ukuran **maksimal 2 MB**
   - dimensi maksimal 4000 × 4000 piksel
3. Klik **Kirim**.

Setelah berhasil:
- status pesanan otomatis menjadi **DIPROSES**,
- status pembayaran menjadi **Menunggu verifikasi**,
- Anda diarahkan ke halaman status pesanan.

Hal yang perlu diketahui:
- Satu pesanan hanya bisa memiliki **satu bukti pembayaran**. Ingin mengganti? Hubungi admin.
- Bukti bayar hanya bisa diunggah dari **browser/sesi yang membuat pesanan**. Jika sesi hilang, hubungi admin untuk mengunggah manual.

### A5. Memantau Status Pesanan

Buka `/status`, lalu masukkan **kode referensi** pesanan.

| Status | Arti |
|---|---|
| **Menunggu Konfirmasi** | Pesanan sudah masuk, menunggu pembayaran/konfirmasi admin |
| **Diproses** | Bukti bayar diterima, pesanan sedang disiapkan |
| **Dikirim** | Pesanan dalam pengiriman |
| **Selesai** | Pembayaran terverifikasi dan pesanan selesai |
| **Dibatalkan** | Pesanan dibatalkan (alasan ditampilkan) |

Halaman ini juga menampilkan rincian item, total, metode pembayaran, dan status pembayaran.

### A6. Melihat Kontak / Lokasi Outlet

Buka menu **Kontak** (`/kontak`) untuk melihat alamat, jam buka, telepon, dan informasi outlet. Data ini diisi oleh admin.

### A7. Pertanyaan Umum (Pelanggan)

| Pertanyaan | Jawaban |
|---|---|
| Apakah harus daftar akun? | Tidak. Checkout sebagai tamu. |
| Lupa kode referensi? | Hubungi admin dengan nama & nomor telepon yang dipakai saat checkout. |
| Bukti transfer salah file? | Hubungi admin; pelanggan tidak bisa mengganti sendiri. |
| Pesanan bisa dibatalkan sendiri? | Tidak. Pembatalan dilakukan admin (stok akan dikembalikan). |
| Pembayaran COD? | Pilih **COD** saat checkout, tidak perlu upload bukti. |

---

## Bagian B — Untuk Admin / Owner

Masuk melalui `/login` dengan email dan password yang diberikan.

> Akun dibuat oleh pengelola sistem: `php artisan admin:create`. Tidak ada pendaftaran mandiri.

### B1. Dashboard

Menampilkan ringkasan: jumlah pesanan, pesanan menunggu konfirmasi, pendapatan, produk stok menipis.

### B2. Mengelola Produk — menu **Produk**

| Aksi | Langkah |
|---|---|
| Tambah | Klik **Tambah Produk**, isi nama, kategori, harga, stok, deskripsi, unggah gambar → **Simpan** |
| Ubah | Klik **Edit** pada baris produk, ubah data → **Simpan** |
| Hapus | Klik **Hapus**, konfirmasi |
| Nonaktifkan | Edit produk → matikan **Aktif** (produk hilang dari katalog, data tetap ada) |

Catatan:
- **Slug** dibuat otomatis dari nama dan dijamin unik.
- Gambar disimpan di `storage/app/public/products`; ukuran disarankan < 2 MB, rasio 1:1.
- Menghapus produk yang masih dipakai pesanan lama dapat memengaruhi reaktivasi pesanan — lebih aman **nonaktifkan**.

### B3. Mengelola Kategori — menu **Kategori**

Tambah/ubah/hapus kategori. Nama kategori harus unik; slug otomatis. Kategori yang masih memiliki produk tidak boleh dihapus.

### B4. Mengelola Promo — menu **Promo**

| Field | Keterangan |
|---|---|
| Kode | Kode yang diketik pelanggan (disimpan huruf besar) |
| Tipe diskon | **Persen** atau **Nominal** |
| Nilai | Mis. `10` (10%) atau `5000` (Rp5.000) |
| Minimal belanja | Promo hanya berlaku bila subtotal ≥ nilai ini |
| Berlaku sampai | Tanggal terakhir promo aktif |
| Aktif | Promo hanya bisa dipakai bila aktif dan belum kedaluwarsa |

Diskon persen dibatasi maksimal 100%; diskon nominal tidak melebihi subtotal.

### B5. Mengelola Pesanan — menu **Pesanan**

1. Daftar pesanan menampilkan kode referensi, nama, total, status.
2. Klik **Detail** untuk melihat item, alamat, pembayaran, dan bukti transfer.
3. Ubah status melalui pilihan status, lalu **Simpan**.

**Aturan status (penting):**

- Mengubah status menjadi **Dibatalkan** → stok **dikembalikan otomatis** (satu kali saja).
- Mengaktifkan kembali pesanan yang dibatalkan → stok **dipotong lagi**. Jika stok tidak cukup, muncul pesan dan status tidak berubah.
- Menghapus pesanan → stok dikembalikan dan file bukti bayar dihapus.

### B6. Mengelola Pembayaran — menu **Pembayaran**

1. Daftar pembayaran menampilkan order, nominal, status, dan tanggal.
2. **Lihat bukti** membuka file bukti transfer (hanya untuk admin yang login).
3. **Verifikasi** (✅):
   - status pembayaran → **Terverifikasi**
   - status pesanan → **Selesai**
   - stok tidak berubah
4. **Tolak** (❌):
   - isi alasan penolakan
   - status pembayaran → **Ditolak**
   - pesanan → **Dibatalkan**
   - stok **dikembalikan**

**Klik ganda / dua admin bersamaan:** permintaan kedua tidak error 500, tetapi menampilkan pesan *"Pembayaran ini sudah diproses sebelumnya."* Data tetap konsisten.

Admin juga bisa menambah pembayaran manual (**Tambah Pembayaran**) untuk pesanan yang membayar tunai/COD, atau mengubah data pembayaran (nominal, status, catatan).

### B7. Mengatur Kontak / Outlet — menu **Kontak**

Isi nama outlet, alamat, telepon/WhatsApp, jam buka, dan catatan tambahan. Perubahan langsung tampil di halaman `/kontak`.

### B8. Akun & Hak Akses

| Role | Kemampuan |
|---|---|
| `ADMIN` | Seluruh panel admin |
| `OWNER` | Seluruh panel admin (setara) |

- Logout: tombol **Keluar** di kanan atas.
- Sesi berakhir otomatis setelah 120 menit tidak aktif.
- Registrasi publik ditutup; akun baru hanya lewat perintah `admin:create`.

---

## Bagian C — Alur Harian yang Disarankan

```
Pagi   : cek Dashboard, cek stok menipis, siapkan produk
Harian : pantau menu Pesanan → pesanan baru (MENUNGGU_KONFIRMASI)
         pantau menu Pembayaran → bukti baru (PENDING_REVIEW)
         verifikasi bukti yang valid, tolak yang tidak sesuai + isi alasan
         update status pengiriman (DIKIRIM) lalu SELESAI
Sore   : rekap pesanan selesai, cek pesanan yang masih menggantung
```

---

## Bagian D — Hal yang Sebaiknya Tidak Dilakukan

- Menghapus produk/kategori yang masih dipakai pesanan aktif.
- Mengubah status pesanan ke **Dibatalkan** hanya untuk "merapikan" daftar — stok akan bertambah.
- Membagikan tautan `admin/payments/{id}/receipt` — tautan ini hanya untuk admin yang login dan bersifat privat.
- Mengubah `.env` di server tanpa menjalankan `php artisan optimize:clear`.
