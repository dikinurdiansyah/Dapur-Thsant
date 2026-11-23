# Panduan Deployment — Katalog Order UMKM (Dapur TH Sant)

Panduan ini dipakai untuk men-deploy aplikasi ke server produksi atau staging (VPS Linux, PHP-FPM + Nginx + MySQL).

---

## 1. Ringkasan Arsitektur Server

```
Internet ──▶ Nginx (:443, HTTPS) ──▶ PHP-FPM 8.2 ──▶ Laravel 12
                                                      ├─ MySQL 8  (data)
                                                      ├─ storage/app/public   (gambar produk, via symlink)
                                                      ├─ storage/app/private  (bukti transfer, privat)
                                                      └─ storage/framework    (session, cache, view)
```

Tidak ada worker/queue dan tidak ada cron wajib — aplikasi tidak memakai job terjadwal.

---

## 2. Kebutuhan Server

| Komponen | Versi minimum |
|---|---|
| OS | Ubuntu 22.04/24.04 (atau setara) |
| PHP | 8.2 (ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd`, `curl`, `xml`, `zip`) |
| Composer | 2.x |
| Node.js | 20.x (hanya untuk build aset) |
| MySQL | 8.0 |
| Nginx | 1.20+ |
| Sertifikat | Let's Encrypt (Certbot) — **wajib**, karena cookie session di-set secure |

---

## 3. Langkah Deploy (dari nol)

### 3.1 Ambil kode

```bash
sudo mkdir -p /var/www/katalog && sudo chown -R $USER:www-data /var/www/katalog
git clone -b main https://github.com/dikinurdiansyah/katalog-order-umkm-saturn.git /var/www/katalog
cd /var/www/katalog
```

> Deploy **hanya dari branch `main`**. Branch `utama` berisi lini kode berbeda dan tidak dipakai untuk produksi.

### 3.2 Dependensi

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build            # WAJIB — public/build di-ignore git
```

### 3.3 Environment

```bash
cp .env.production.example .env
nano .env
```

Isi minimal:

```env
APP_NAME="Dapur TH Sant"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=user_database
DB_PASSWORD=password_kuat

SESSION_DRIVER=file
SESSION_SECURE_COOKIE=true

FILESYSTEM_DISK=local
CACHE_STORE=database
QUEUE_CONNECTION=database
MAIL_MAILER=log
```

Lalu:

```bash
php artisan key:generate
```

> `APP_KEY` berbeda per environment. Jangan pernah memakai key lokal di produksi.

### 3.4 Database

```sql
CREATE DATABASE nama_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'user_database'@'localhost' IDENTIFIED BY 'password_kuat';
GRANT ALL PRIVILEGES ON nama_database.* TO 'user_database'@'localhost';
FLUSH PRIVILEGES;
```

```bash
php artisan migrate --force     # TANPA db:seed di produksi
```

### 3.5 Storage & permission

```bash
php artisan storage:link
sudo chown -R www-data:www-data storage bootstrap/cache
sudo find storage bootstrap/cache -type d -exec chmod 775 {} \;
sudo find storage bootstrap/cache -type f -exec chmod 664 {} \;
```

`storage/app/private` harus writable — di sinilah bukti transfer pelanggan disimpan.

### 3.6 Akun admin pertama

```bash
php artisan admin:create --name="Admin Dapur" --email=admin@domain.com --password='PasswordKuat123' --role=OWNER
```

Registrasi publik ditutup, jadi tanpa langkah ini tidak ada yang bisa login.

### 3.7 Optimasi

```bash
php artisan optimize        # config:cache + route:cache + view:cache
```

Setiap kali `.env` berubah, jalankan `php artisan optimize:clear` lalu `php artisan optimize` lagi.

---

## 4. Konfigurasi Nginx

```nginx
server {
    listen 80;
    server_name domain-anda.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name domain-anda.com;
    root /var/www/katalog/public;      # HARUS berakhir di /public

    index index.php;
    charset utf-8;

    ssl_certificate     /etc/letsencrypt/live/domain-anda.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/domain-anda.com/privkey.pem;

    client_max_body_size 10m;          # upload bukti bayar (maks 2 MB)

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }

    location ~ /\.(?!well-known).* { deny all; }
}
```

---

## 5. Konfigurasi PHP (`/etc/php/8.2/fpm/php.ini`)

```ini
upload_max_filesize = 8M
post_max_size = 10M
memory_limit = 256M
max_execution_time = 60
```

Setelah diubah: `sudo systemctl restart php8.2-fpm`.

---

## 6. Checklist Go-Live

- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] `APP_URL` = domain HTTPS final
- [ ] `APP_KEY` sudah di-generate
- [ ] `php artisan migrate --force` sukses (12 migrasi)
- [ ] `public/build/manifest.json` ada
- [ ] `public/storage` symlink ada dan gambar produk tampil
- [ ] `storage/` + `bootstrap/cache/` writable oleh `www-data`
- [ ] Akun admin dibuat, login berhasil
- [ ] `SESSION_SECURE_COOKIE=true` **dan** situs sudah HTTPS
- [ ] `GET /health` mengembalikan `"status":"ok"` dan `"database_status":"CONNECTED"`
- [ ] Checkout uji: pesanan muncul di admin, stok berkurang
- [ ] Upload bukti transfer uji berhasil (< 2 MB)
- [ ] Verifikasi pembayaran: order → `SELESAI`, stok tetap
- [ ] Tolak pembayaran: order → `DIBATALKAN`, stok kembali
- [ ] `php artisan optimize` dijalankan terakhir
- [ ] Backup database terjadwal

---

## 7. Verifikasi Setelah Deploy

```bash
# Status aplikasi + DB
curl -s https://domain-anda.com/health

# Pastikan halaman publik 200
curl -s -o /dev/null -w '%{http_code}\n' https://domain-anda.com/
curl -s -o /dev/null -w '%{http_code}\n' https://domain-anda.com/login

# Pastikan file privat tidak bisa diakses langsung (harus 403/404)
curl -s -o /dev/null -w '%{http_code}\n' https://domain-anda.com/storage/app/private/

# Log
tail -f storage/logs/laravel.log
```

Cek manual di browser: katalog → keranjang → checkout → halaman sukses → upload bukti → login admin → verifikasi.

---

## 8. Update / Rilis Berikutnya

```bash
cd /var/www/katalog
php artisan down --render="errors::503"   # opsional, mode maintenance
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan optimize:clear && php artisan optimize
php artisan up
```

---

## 9. Rollback

```bash
git log --oneline -5
git checkout <commit-sebelumnya>
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan optimize:clear && php artisan optimize
```

Jika perubahan melibatkan migrasi yang merusak data, pulihkan dari backup:

```bash
mysql -u user_database -p nama_database < backup_sebelum_deploy.sql
```

> Migrasi di proyek ini bersifat menambah (tidak ada `drop` data), sehingga rollback kode biasanya aman tanpa restore database.

---

## 10. Backup

```bash
# Database harian (crontab)
0 2 * * * mysqldump -u user -p'pass' nama_database | gzip > /var/backups/db_$(date +\%F).sql.gz

# File upload (gambar produk + bukti transfer)
0 3 * * * tar czf /var/backups/storage_$(date +\%F).tar.gz /var/www/katalog/storage/app
```

---

## 11. Pemecahan Masalah Deploy

| Gejala | Penyebab | Solusi |
|---|---|---|
| Semua halaman 500, `ViteManifestNotFoundException` | `npm run build` belum dijalankan | jalankan `npm ci && npm run build` |
| 403 Forbidden di semua halaman | Document root bukan `/public` | perbaiki `root` Nginx |
| 500 tanpa detail | `APP_DEBUG=false` menyembunyikan error | baca `storage/logs/laravel.log` |
| `could not find driver` | ekstensi `pdo_mysql` belum aktif | `apt install php8.2-mysql`, restart FPM |
| Gambar produk 404 | `storage:link` belum jalan / permission | `php artisan storage:link`, `chown www-data` |
| Login sukses tapi balik ke login | cookie secure di HTTP, atau `APP_URL` salah | pakai HTTPS, samakan `APP_URL` |
| `SQLSTATE[HY000] [2002]` | MySQL mati / `DB_HOST` salah | cek service & kredensial |
| Upload 413 | `client_max_body_size` kecil | set `10m` |
| Perubahan `.env` tidak berlaku | config ter-cache | `php artisan optimize:clear` |
| `419 Page Expired` saat submit | session/cookie tidak tersimpan | cek permission `storage/framework/sessions`, `SESSION_DOMAIN` |

---

## 12. Catatan Keamanan

- `.env` **tidak boleh** masuk git (sudah ada di `.gitignore`).
- `APP_DEBUG` wajib `false` di produksi — jika `true`, stack trace dan query bisa terbaca publik.
- Bukti transfer bersifat privat; jangan menambahkan route publik untuk membukanya.
- Rotasi password DB dan akun admin secara berkala.
- Token API (`personal_access_tokens`) harus dihapus bila tidak dipakai.
