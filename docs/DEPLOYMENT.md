# Panduan Deployment SIKERJA

## 1. Pendahuluan

Dokumen ini menjelaskan proses deployment SIKERJA ke server production.

SIKERJA menggunakan:

- Laravel 12.
- PHP 8.2 atau lebih baru.
- MariaDB/MySQL.
- Bootstrap.
- Vite.
- Laravel DOMPDF.
- Storage privat untuk foto dan bukti pekerjaan.

---

## 2. Persyaratan Server

Server harus menyediakan:

- PHP 8.2 atau lebih baru.
- Composer.
- MariaDB atau MySQL.
- Apache atau Nginx.
- Node.js dan NPM untuk proses build.
- HTTPS.
- Ekstensi PHP yang dibutuhkan Laravel.

Ekstensi yang umum diperlukan:

- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- PDO MySQL
- Tokenizer
- XML
- GD atau Imagick sesuai kebutuhan server

Periksa:

```bash
php -v
php -m
composer --version
node -v
npm -v
```

---

## 3. Persiapan Repository

Clone repository:

```bash
git clone ALAMAT_REPOSITORY
cd sikerja
```

Atau upload source code ke server menggunakan metode yang disetujui pengelola.

Pastikan file berikut tidak ikut dipublikasikan:

- `.env`
- Backup database.
- File privat.
- Password atau credential.
- Folder development yang tidak diperlukan.

---

## 4. Konfigurasi Environment

Salin file environment:

```bash
cp .env.example .env
```

Atur `.env`:

```env
APP_NAME=SIKERJA
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://alamat-aplikasi

APP_LOCALE=id
APP_FALLBACK_LOCALE=id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sikerja
DB_USERNAME=sikerja_user
DB_PASSWORD=password_database_yang_kuat

FILESYSTEM_DISK=local

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

Buat application key:

```bash
php artisan key:generate
```

> Jangan menjalankan `php artisan key:generate` apabila server sedang menggunakan data terenkripsi dari key lama, kecuali telah dipastikan aman.

---

## 5. Database Production

Buat database dan pengguna khusus:

```sql
CREATE DATABASE sikerja
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

CREATE USER 'sikerja_user'@'localhost'
IDENTIFIED BY 'password_database_yang_kuat';

GRANT ALL PRIVILEGES
ON sikerja.*
TO 'sikerja_user'@'localhost';

FLUSH PRIVILEGES;
```

Jangan menggunakan akun root database untuk aplikasi production.

---

## 6. Instal Dependency

Instal dependency PHP:

```bash
composer install \
    --no-dev \
    --optimize-autoloader
```

Instal dependency frontend:

```bash
npm ci
```

Build aset:

```bash
npm run build
```

Periksa DOMPDF:

```bash
composer show barryvdh/laravel-dompdf
```

---

## 7. Migration

Jalankan:

```bash
php artisan migrate --force
```

Jangan menjalankan `DemoSeeder` pada production.

Seeder data dasar hanya dijalankan apabila memang dibutuhkan:

```bash
php artisan db:seed --force
```

Pastikan isi `DatabaseSeeder` aman untuk production sebelum dijalankan.

---

## 8. Hak Akses Folder

Web server harus dapat menulis ke:

```text
storage
bootstrap/cache
```

Contoh Linux:

```bash
chmod -R 775 storage bootstrap/cache
```

Sesuaikan pemilik folder:

```bash
chown -R www-data:www-data storage bootstrap/cache
```

Nama pengguna web server dapat berbeda, misalnya:

- `www-data`
- `apache`
- `nginx`

---

## 9. Document Root

Document root harus diarahkan ke:

```text
/path/sikerja/public
```

Jangan arahkan document root ke folder utama project.

Struktur yang benar:

```text
/path/sikerja/
├── app
├── bootstrap
├── config
├── database
├── public        ← document root
├── resources
├── routes
├── storage
└── vendor
```

---

## 10. Konfigurasi Apache

Contoh VirtualHost:

```apache
<VirtualHost *:80>
    ServerName sikerja.example.id
    DocumentRoot /var/www/sikerja/public

    <Directory /var/www/sikerja/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/sikerja-error.log
    CustomLog ${APACHE_LOG_DIR}/sikerja-access.log combined
</VirtualHost>
```

Aktifkan rewrite:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Aktifkan HTTPS menggunakan mekanisme yang disetujui organisasi.

---

## 11. Konfigurasi Nginx

Contoh:

```nginx
server {
    listen 80;
    server_name sikerja.example.id;

    root /var/www/sikerja/public;
    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Sesuaikan versi PHP-FPM.

---

## 12. Storage Privat

Foto presensi dan bukti pekerjaan disimpan pada:

```text
storage/app/private
```

Disk local harus tetap menggunakan:

```php
'serve' => false,
```

Jangan membuat symbolic link dari storage privat ke folder publik.

File privat hanya dikirim melalui controller yang melakukan pemeriksaan:

- Login.
- Akun aktif.
- Password sudah diganti.
- Role.
- Kepemilikan data.

---

## 13. Logo SIKERJA

Pastikan logo tersedia:

```text
public/images/logo-sikerja.png
```

Logo digunakan pada:

- Sidebar.
- Menu mobile.
- Halaman login.
- Dokumen PDF.

Periksa:

```bash
ls -lah public/images/logo-sikerja.png
```

---

## 14. DOMPDF

Package:

```text
barryvdh/laravel-dompdf
```

Periksa:

```bash
composer show barryvdh/laravel-dompdf
```

Periksa facade:

```bash
php artisan tinker --execute="
dump(
    class_exists(
        Barryvdh\DomPDF\Facade\Pdf::class
    )
);
"
```

Hasil:

```text
true
```

Periksa route:

```bash
php artisan route:list --name=monitoring.pdf
```

Route:

```text
admin.monitoring.pdf
leader.monitoring.pdf
```

---

## 15. Optimasi Laravel

Bersihkan cache:

```bash
php artisan optimize:clear
```

Buat cache production:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Periksa:

```bash
php artisan about
php artisan migrate:status
```

---

## 16. Queue Worker

Apabila fitur queue digunakan:

```bash
php artisan queue:work \
    --sleep=3 \
    --tries=3 \
    --timeout=120
```

Gunakan Supervisor atau systemd agar worker berjalan otomatis.

Contoh Supervisor:

```ini
[program:sikerja-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/sikerja/artisan queue:work --sleep=3 --tries=3 --timeout=120
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/sikerja/storage/logs/worker.log
stopwaitsecs=3600
```

---

## 17. Scheduler

Apabila aplikasi menggunakan scheduler, tambahkan cron:

```cron
* * * * * cd /var/www/sikerja && php artisan schedule:run >> /dev/null 2>&1
```

---

## 18. Mode Pemeliharaan

Aktifkan:

```bash
php artisan down
```

Nonaktifkan:

```bash
php artisan up
```

Gunakan mode pemeliharaan saat:

- Migration besar.
- Perbaikan database.
- Restore.
- Perubahan konfigurasi penting.

---

## 19. Backup Production

Backup wajib mencakup:

- Database.
- `storage/app/private`.
- File `.env` secara aman.
- Logo dan file konfigurasi penting.

Jangan menyimpan backup di folder `public`.

Contoh database:

```bash
mkdir -p storage/app/backups/database

mysqldump \
    --host=127.0.0.1 \
    --user=sikerja_user \
    --password \
    --single-transaction \
    --quick \
    --triggers \
    --skip-events \
    --skip-routines \
    --default-character-set=utf8mb4 \
    sikerja \
    > storage/app/backups/database/sikerja_$(date +"%Y%m%d_%H%M%S").sql
```

Backup file privat:

```bash
tar -czf \
storage/app/backups/private_$(date +"%Y%m%d_%H%M%S").tar.gz \
storage/app/private
```

---

## 20. Restore Database

Aktifkan maintenance:

```bash
php artisan down
```

Restore:

```bash
mysql \
    --host=127.0.0.1 \
    --user=sikerja_user \
    --password \
    sikerja \
    < NAMA_FILE_BACKUP.sql
```

Setelah restore:

```bash
php artisan optimize:clear
php artisan up
```

---

## 21. Pengujian Setelah Deployment

Uji:

1. Halaman login.
2. Login Admin.
3. Login Pimpinan.
4. Login Personel.
5. Akun nonaktif.
6. Penggantian password.
7. Pembuatan jadwal.
8. Check-in.
9. Kamera.
10. GPS.
11. Rencana kerja.
12. Tugas Pimpinan.
13. Upload bukti.
14. Pengiriman laporan.
15. Revisi.
16. Persetujuan.
17. Check-out.
18. Foto dan GPS.
19. Monitoring.
20. Ekspor PDF.
21. Notifikasi.
22. Logout.
23. Halaman 403.
24. Halaman 404.

Jalankan test:

```bash
php artisan test
```

---

## 22. Keamanan Production

Pastikan:

- `APP_ENV=production`.
- `APP_DEBUG=false`.
- HTTPS aktif.
- `.env` tidak dapat diakses.
- Database tidak menggunakan root.
- Password kuat.
- Akun demo dinonaktifkan.
- Storage privat tidak terpublikasi.
- Backup tidak berada di folder publik.
- Hak akses folder terbatas.
- Log diperiksa berkala.
- Server diperbarui secara berkala.
- Hanya port yang diperlukan yang dibuka.

---

## 23. Update Aplikasi

Urutan update:

```bash
php artisan down

git pull

composer install \
    --no-dev \
    --optimize-autoloader

npm ci
npm run build

php artisan migrate --force

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan up
```

Lakukan backup sebelum update.

---

## 24. Troubleshooting

### Tampilan tanpa CSS

```bash
rm -f public/hot
npm run build
php artisan optimize:clear
```

Pastikan layout memuat:

```blade
@vite([
    'resources/css/app.css',
    'resources/js/app.js',
])
```

### Permission denied

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### PDF gagal

```bash
composer show barryvdh/laravel-dompdf
php artisan optimize:clear
php artisan route:list --name=monitoring.pdf
```

### Error 500

```bash
tail -n 150 storage/logs/laravel.log
```

### Route tidak ditemukan

```bash
php artisan route:clear
php artisan route:list
```

---

## 25. Checklist Deployment

- [ ] Source code terbaru.
- [ ] `.env` production.
- [ ] `APP_DEBUG=false`.
- [ ] Database dibuat.
- [ ] User database khusus.
- [ ] Composer install berhasil.
- [ ] NPM build berhasil.
- [ ] Migration berhasil.
- [ ] Permission storage benar.
- [ ] Document root ke `public`.
- [ ] HTTPS aktif.
- [ ] Logo tersedia.
- [ ] DOMPDF tersedia.
- [ ] Route PDF tersedia.
- [ ] Akun demo dinonaktifkan.
- [ ] Backup tersedia.
- [ ] Seluruh role berhasil diuji.
- [ ] Ekspor PDF berhasil.
- [ ] Test otomatis lulus.
