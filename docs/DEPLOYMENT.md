# Deployment SIKERJA

## Persyaratan Server

- PHP 8.2 atau lebih baru.
- Composer.
- MariaDB atau MySQL.
- Node.js untuk proses build.
- Web server Apache atau Nginx.
- Ekstensi PHP yang dibutuhkan Laravel.

## Konfigurasi Production

Pada `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://alamat-aplikasi

FILESYSTEM_DISK=local
```

Database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sikerja
DB_USERNAME=nama_user_database
DB_PASSWORD=password_database
```

Jangan memakai akun root database pada server production.

## Instalasi

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan key:generate
php artisan migrate --force
```

Jangan menjalankan `DemoSeeder` pada production.

## Hak Akses Folder

Web server harus dapat menulis ke:

```text
storage
bootstrap/cache
```

Contoh Linux:

```bash
chmod -R 775 storage bootstrap/cache
```

Sesuaikan pemilik folder dengan pengguna web server.

## Document Root

Document root web server harus diarahkan ke:

```text
/path/proyek/sikerja/public
```

Jangan arahkan document root ke folder utama proyek.

## Optimasi

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Keamanan

Pastikan:

- `APP_DEBUG=false`.
- File `.env` tidak dapat diakses publik.
- Storage privat tidak disajikan langsung.
- `'serve' => false` pada disk local.
- Password akun awal sudah diganti.
- Akun demo dihapus atau dinonaktifkan.
- HTTPS aktif.
- Backup database dilakukan berkala.
- Folder backup tidak berada pada direktori publik.

## Pemeriksaan Setelah Deployment

```bash
php artisan about
php artisan migrate:status
php artisan route:list
php artisan test
```

Uji:

1. Login setiap role.
2. Check-in dan check-out.
3. Upload bukti pekerjaan.
4. Foto presensi.
5. GPS dan Google Maps.
6. Verifikasi laporan.
7. Notifikasi.
8. Logout.
9. Halaman 403 dan 404.

## Mode Pemeliharaan

Aktifkan:

```bash
php artisan down
```

Nonaktifkan:

```bash
php artisan up
```
