# Skrip Demo — Katalog Order UMKM (Dapur TH Sant)

Durasi total: **10 menit** demo + 5 menit tanya jawab.

---

## Persiapan 30 Menit Sebelum Demo

**Checklist teknis**

- [ ] `php artisan serve` (atau URL staging) sudah jalan, buka di tab terpisah
- [ ] `npm run build` sudah dijalankan (kalau belum, semua halaman 500)
- [ ] `php artisan storage:link` sudah jalan → gambar produk tampil
- [ ] Database berisi minimal 6 produk aktif dengan stok ≥ 10
- [ ] Ada 1 akun admin siap login (jangan membuat akun saat demo)
- [ ] Sesi keranjang bersih (buka jendela incognito)
- [ ] Data uji lama dihapus agar daftar pesanan tidak berantakan
- [ ] File bukti transfer contoh sudah ada di folder (`bukti-transfer-contoh.jpg`, ±500 KB)
- [ ] Zoom browser 110–125% agar teks terbaca dari belakang
- [ ] Notifikasi (WhatsApp/email/OS) dimatikan
- [ ] Backup: **rekaman video demo** sudah siap bila internet/server bermasalah

**Checklist non-teknis**

- [ ] Urutan bicara dilatih 2× dengan stopwatch
- [ ] Slide dibuka di tab lain, siap `Alt+Tab`
- [ ] Paham 3 angka kunci: 12 migrasi, 54 route, 2 role

**Data uji yang disiapkan**

| Item | Nilai |
|---|---|
| Produk | Dimsum Ayam — stok 20, harga Rp15.000 |
| Kode promo | `DEMO10` — potongan 10%, minimal Rp20.000 |
| Akun admin | `admin@umkm.com` / `password` (akun demo lokal) |

---

## Struktur Demo (10 menit)

| Menit | Bagian | Penekanan |
|---|---|---|
| 0:00–1:00 | Pembukaan & masalah | Kenapa UMKM butuh ini |
| 1:00–3:00 | Katalog → keranjang → checkout | Alur pelanggan tanpa akun |
| 3:00–4:00 | Halaman sukses & kode referensi | Lacak pesanan |
| 4:00–6:00 | Admin: verifikasi pembayaran | Stok & status berubah benar |
| 6:00–7:30 | Kasus batas: klik ganda & tolak | Ketahanan data |
| 7:30–8:30 | Pelacakan status pelanggan | Transparansi |
| 8:30–9:30 | Arsitektur & pengujian | Bukti kualitas |
| 9:30–10:00 | Penutup | Rencana lanjutan |

---

## Naskah per Bagian

### 0:00–1:00 — Pembukaan

> "Selamat pagi. Kami **Diki Nurdiansyah** (frontend) dan **Fauzan Aufa** (backend). Aplikasi yang kami buat adalah **Katalog Order UMKM untuk Dapur TH Sant**, sebuah toko dimsum rumahan.
>
> Masalahnya: pesanan masuk lewat chat WhatsApp, pembayaran dicek manual satu per satu, dan stok sering bentrok karena dua pembeli memesan barang terakhir bersamaan.
>
> Solusinya: satu website tempat pelanggan melihat katalog, memesan **tanpa perlu daftar akun**, mengunggah bukti transfer, dan memantau status. Admin memverifikasi pembayaran dari satu panel."

Tampilkan slide 1–3 (judul, masalah, solusi).

### 1:00–3:00 — Alur Pelanggan

**Aksi:**
1. Buka halaman utama → tunjukkan katalog.
2. Klik satu produk → detail.
3. **Tambah ke Keranjang** 2 pcs.
4. Buka keranjang → tunjukkan subtotal.
5. Klik **Checkout** → isi nama `Demo UKK`, telepon `08123456789`.
6. Pilih **Delivery**, alamat singkat.
7. Pilih **Transfer Bank → BCA**.
8. Masukkan promo `DEMO10` → **tunjukkan diskon muncul**.
9. Klik **Buat Pesanan**.

**Narasi:**

> "Perhatikan: tidak ada proses daftar atau login. Pelanggan langsung bisa memesan.
>
> Biaya dihitung otomatis: subtotal, kemasan dua ribu, ongkir sepuluh ribu untuk delivery — nol untuk ambil di tempat — lalu diskon dari kode promo.
>
> Di belakang layar, stok dipotong **di dalam transaksi database** dengan penguncian baris. Jadi kalau ada dua pembeli terakhir di saat yang sama, salah satunya akan mendapat pesan stok tidak cukup, bukan stok minus."

### 3:00–4:00 — Halaman Sukses

**Aksi:** tunjukkan halaman sukses, sorot kode referensi `ORD-XXXXXXXXXX`.

**Narasi:**

> "Pesanan mendapat kode referensi acak, bukan nomor urut. Ini disengaja: pelanggan tidak bisa menebak pesanan orang lain hanya dengan mengubah angka di URL.
>
> Kode ini yang dipakai untuk melacak pesanan dan menjadi bukti bagi admin."

### 4:00–6:00 — Admin Verifikasi Pembayaran

**Aksi:**
1. Tab baru → `/login` → login admin.
2. Dashboard → tunjukkan ringkasan.
3. Kembali ke tab pelanggan → unggah `bukti-transfer-contoh.jpg`.
4. Tab admin → menu **Pembayaran** → pesanan baru muncul `Menunggu Review`.
5. Klik **Lihat bukti** → gambar terbuka.
6. Klik **Verifikasi**.

**Narasi:**

> "Bukti transfer disimpan di penyimpanan **privat**, bukan folder publik. Jadi file ini hanya bisa dibuka oleh admin yang sudah login — bukan siapa pun yang menebak URL-nya.
>
> Setelah diverifikasi, status pesanan otomatis menjadi **Selesai**, dan nominal pembayaran diambil dari total pesanan di server, bukan dari isian form pelanggan — supaya tidak bisa diubah jadi lebih kecil."

### 6:00–7:30 — Kasus Batas (Ini Pembeda)

**Aksi:**
1. Klik **Verifikasi** sekali lagi pada pembayaran yang sama.

**Narasi:**

> "Kalau admin terlanjur klik dua kali, atau dua admin memproses bersamaan, aplikasi **tidak error 500**. Muncul pesan: *'Pembayaran ini sudah diproses sebelumnya.'*
>
> Sekarang saya tolak pembayaran pada pesanan lain."

2. Buka pembayaran pesanan kedua → **Tolak** → isi alasan.
3. Buka menu **Produk** → tunjukkan stok kembali naik.

**Narasi:**

> "Menolak pembayaran otomatis membatalkan pesanan **dan** mengembalikan stok — tepat satu kali. Kalau admin membatalkan pesanan lalu menolak pembayarannya, stok tidak bertambah dua kali, karena logikanya idempoten dan dijaga transaksi.
>
> Sebaliknya, jika pesanan batal diaktifkan kembali, stok dipotong lagi; kalau stoknya tidak cukup, aplikasi menolak dengan pesan yang jelas."

### 7:30–8:30 — Pelacakan Status

**Aksi:** buka `/status` → masukkan kode referensi.

**Narasi:**

> "Pelanggan bisa memantau status kapan saja tanpa akun: menunggu konfirmasi, diproses, dikirim, selesai, atau dibatalkan beserta alasannya. Halaman ini juga menampilkan rincian item dan status pembayaran."

### 8:30–9:30 — Arsitektur & Pengujian

Tampilkan slide arsitektur dan tabel pengujian.

> "Stack: Laravel 12, MySQL, Blade. Total 12 migrasi dan 54 route. Ada dua role, admin dan owner.
>
> Pengujian yang saya jalankan: lint seluruh file PHP bersih, dua feature test lulus, sepuluh skenario stok, checkout bersamaan, klik ganda verifikasi lewat web dan API, penolakan pembayaran, serta endpoint API dengan hasil 401 tanpa token, 422 validasi, 200 sukses, dan 409 untuk klik ganda."

### 9:30–10:00 — Penutup

> "Yang sudah ada: alur pesan sampai verifikasi berjalan utuh dengan data yang konsisten.
>
> Rencana lanjutan: notifikasi WhatsApp otomatis saat status berubah, laporan penjualan, dan integrasi payment gateway. Terima kasih — saya siap menjawab pertanyaan."

---

## Antisipasi Pertanyaan Penguji

| Pertanyaan | Jawaban ringkas |
|---|---|
| Kenapa pelanggan tidak perlu akun? | Target UMKM; menurunkan friksi. Data kontak dikumpulkan saat checkout, pesanan dilacak lewat kode referensi. |
| Bagaimana mencegah stok minus? | Transaksi + `lockForUpdate()` saat membaca produk, validasi stok sebelum `decrement`, rollback bila satu item gagal. |
| Bagaimana jika dua admin verifikasi bersamaan? | Guard status di controller + `RuntimeException` di model; request kedua dapat pesan ramah (web) atau 409 (API). |
| Kenapa bukti bayar tidak di folder public? | Berisi data pribadi. Disimpan privat, disajikan lewat route ber-otentikasi. |
| Bagaimana mencegah pelanggan mengunggah bukti untuk pesanan orang lain? | Order diambil dari sesi, bukan dari `order_id` kiriman form; nominal ditentukan server. |
| Kenapa nominal tidak dari form? | Mencegah manipulasi pembayaran; server memakai `order.total_amount`. |
| Bagaimana keamanan panel admin? | Login session + middleware role; registrasi publik ditutup; rate limit login 5/menit. |
| Kenapa memakai kode acak, bukan ID? | Mencegah enumerasi pesanan. |
| Apa yang terjadi jika produk dihapus? | Reaktivasi pesanan yang memuat produk terhapus ditolak dengan pesan; produk sebaiknya dinonaktifkan, bukan dihapus. |
| Apa kekurangan aplikasi ini? | Belum ada notifikasi otomatis, verifikasi pembayaran masih manual, test otomatis masih minim, belum ada payment gateway. |

---

## Rencana Cadangan (Jika Demo Gagal)

| Masalah | Tindakan cepat |
|---|---|
| Server lokal mati | Pindah ke URL staging, atau putar rekaman video demo |
| Halaman 500 | `tail storage/logs/laravel.log`, cek `public/build` ada, `php artisan optimize:clear` |
| Database mati | Nyalakan MySQL, `php artisan migrate:status`, ulangi dari langkah terakhir |
| Gambar tidak tampil | `php artisan storage:link` |
| Login gagal | Cek `SESSION_SECURE_COOKIE=false` untuk HTTP lokal |
| Waktu habis | Lewati bagian 7:30–8:30, langsung penutup + tunjukkan dokumen |

**Aturan saat demo:** jangan mengetik perintah terminal di depan penguji kecuali diminta; jangan membuka file `.env`; jika ada error, tetap tenang, sebutkan penyebabnya singkat, lanjutkan bagian berikutnya.
