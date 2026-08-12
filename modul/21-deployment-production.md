# Modul 21: Deployment Production

## Tujuan

Menyiapkan SIKERJA agar dapat berjalan pada server production secara aman.

## Prasyarat

- Semua fitur selesai.
- `php artisan test` PASS.
- `npm run build` berhasil.
- Backup tersedia.

## Langkah 1: `.env` Production

```env
APP_NAME=SIKERJA
APP_ENV=production
APP_DEBUG=false
APP_URL=https://alamat-sikerja

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sikerja
DB_USERNAME=sikerja_user
DB_PASSWORD=password-kuat
```

Jangan memakai user `root` pada production.

## Langkah 2: Composer

```bash
composer install \
    --no-dev \
    --optimize-autoloader
```

## Langkah 3: Frontend

```bash
npm ci
npm run build
```

## Langkah 4: Migration

```bash
php artisan migrate --force
```

Jangan `migrate:fresh`.

## Langkah 5: Demo Seeder

Jangan jalankan:

```bash
php artisan db:seed \
    --class=DemoSeeder
```

pada production.

## Langkah 6: Document Root

Konfigurasi Apache/Nginx harus mengarah ke:

```text
/path/sikerja/public
```

bukan ke folder root project.

## Langkah 7: Permission

```bash
chmod -R 775 \
    storage \
    bootstrap/cache
```

Sesuaikan owner/group web server.

## Langkah 8: Cache Production

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Langkah 9: HTTPS

Production sebaiknya menggunakan HTTPS.

Hal ini penting untuk:

- keamanan login;
- kamera browser;
- geolocation;
- transfer data.

## Langkah 10: Storage Privat

Pastikan:

```php
'serve' => false,
```

dan jangan membuat file evidence tersedia melalui symlink public.

## Langkah 11: Final Test

Uji:

1. Login Admin.
2. Login Pimpinan.
3. Login Personel.
4. Jadwal.
5. Check-in.
6. Kamera.
7. GPS.
8. Tugas.
9. Progress.
10. Upload PDF.
11. Submit.
12. Check-out.
13. Verifikasi.
14. Monitoring.
15. Evidence.
16. PDF.
17. Notifikasi.
18. Logout.

## Troubleshooting

### Error 500

```bash
tail -n 150 \
storage/logs/laravel.log
```

### CSS tidak tampil

```bash
npm run build
rm -f public/hot
```

### Permission denied

Periksa folder:

```text
storage
bootstrap/cache
```

## Penjelasan untuk Pemula

Development dan production adalah dua lingkungan berbeda.

### Development

Digunakan saat membuat aplikasi.

Contoh:

```env
APP_DEBUG=true
```

### Production

Digunakan saat aplikasi dipakai sungguhan.

Harus:

```env
APP_DEBUG=false
```

agar detail error internal tidak terlihat pengguna.

### Document Root

Server harus mengarah ke folder:

```text
public
```

karena itulah pintu masuk aman Laravel.

Folder seperti:

```text
app
storage
.env
```

tidak boleh dapat diakses langsung dari browser.

## Penjelasan Gamblang: Deployment Ini Untuk Apa?

### Development
Lingkungan tempat programmer membuat aplikasi.

### Production
Lingkungan tempat aplikasi benar-benar digunakan.

### `APP_DEBUG=false`
Menyembunyikan detail error internal dari pengguna.

### `composer install --no-dev`
Memasang dependency yang dibutuhkan production tanpa package khusus development.

### `npm run build`
Membuat CSS/JS versi production.

### `php artisan migrate --force`
Menjalankan migration pada production tanpa prompt interaktif.

### Document root `/public`
Memastikan browser hanya dapat masuk melalui pintu Laravel yang aman.

### HTTPS
Mengenkripsi koneksi pengguna dan membantu fitur kamera/geolocation bekerja dalam secure context.

### Kenapa DB user bukan root?
Membatasi dampak jika kredensial aplikasi bocor.

## Checklist

- [ ] APP_ENV production
- [ ] APP_DEBUG false
- [ ] HTTPS
- [ ] DB user khusus
- [ ] Composer production
- [ ] Build
- [ ] Migration
- [ ] Permission
- [ ] Private storage
- [ ] No DemoSeeder
- [ ] Backup

## Modul Berikutnya

Modul 22 menutup project dengan Dokumentasi dan Presentasi.
