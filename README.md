# SIKERJA

**Sistem Informasi Kinerja dan Aktivitas Personel**

SIKERJA adalah aplikasi berbasis web untuk mengelola pelaksanaan Work From Home atau WFH, mulai dari penyusunan jadwal, penentuan Personel, presensi berbasis foto dan GPS, pemberian tugas, pelaporan pekerjaan, sampai verifikasi oleh Pimpinan atau Admin.

## Fitur Utama

- Autentikasi menggunakan ID Login dan password.
- Pembagian hak akses Admin, Pimpinan, dan Personel.
- Pengelolaan unit dan pengguna.
- Pembuatan serta aktivasi jadwal WFH.
- Penentuan Personel yang melaksanakan WFH.
- Check-in dan check-out menggunakan kamera serta GPS.
- Penyimpanan foto presensi pada storage privat.
- Rencana kerja pribadi Personel.
- Penugasan dari Pimpinan.
- Upload bukti pekerjaan PDF.
- Pengiriman dan verifikasi laporan.
- Permintaan revisi laporan.
- Monitoring dan rekapitulasi pelaksanaan WFH.
- Pusat notifikasi.
- Bukti foto dan lokasi presensi.
- Pengujian otomatis hak akses.
- Halaman error khusus SIKERJA.

## Teknologi

- Laravel 12
- PHP 8.2 atau lebih baru
- MariaDB atau MySQL
- Bootstrap
- JavaScript
- Vite
- PHPUnit

## Role Pengguna

### Admin

Admin bertugas mengelola:

- Data pengguna.
- Unit kerja.
- Jadwal WFH.
- Anggota jadwal.
- Monitoring presensi.
- Verifikasi laporan.
- Rekapitulasi pelaksanaan WFH.

### Pimpinan

Pimpinan dapat:

- Memberikan tugas kepada Personel.
- Memantau check-in dan check-out.
- Melihat foto serta GPS presensi.
- Memeriksa laporan Personel.
- Menyetujui atau meminta revisi laporan.

### Personel

Personel dapat:

- Melihat jadwal WFH.
- Melakukan check-in.
- Membuat rencana kerja pribadi.
- Menerima tugas Pimpinan.
- Memperbarui pelaksanaan pekerjaan.
- Mengunggah bukti pekerjaan.
- Mengirim laporan.
- Melakukan check-out.
- Melihat status verifikasi laporan.

## Instalasi Lokal

### 1. Unduh source code

```bash
git clone ALAMAT_REPOSITORY
cd sikerja
```

### 2. Instal dependency PHP

```bash
composer install
```

### 3. Instal dependency frontend

```bash
npm install
```

### 4. Buat file konfigurasi

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Atur database pada `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sikerja
DB_USERNAME=root
DB_PASSWORD=
```

### 6. Buat tabel dan data awal

```bash
php artisan migrate
php artisan db:seed
```

### 7. Jalankan frontend

```bash
npm run dev
```

### 8. Jalankan Laravel

Buka Terminal kedua:

```bash
php artisan serve
```

Aplikasi dapat dibuka melalui:

```text
http://127.0.0.1:8000
```

## Data Demo Presentasi

Data demo dibuat secara manual menggunakan:

```bash
php artisan db:seed --class=DemoSeeder
```

Akun demo:

| Role | ID Login | Password |
|---|---|---|
| Admin | `DEMOADMIN` | `DemoSikerja#2026` |
| Pimpinan | `DEMOPIMPINAN` | `DemoSikerja#2026` |
| Personel 1 | `DEMOPER001` | `DemoSikerja#2026` |
| Personel 2 | `DEMOPER002` | `DemoSikerja#2026` |
| Personel 3 | `DEMOPER003` | `DemoSikerja#2026` |
| Personel 4 | `DEMOPER004` | `DemoSikerja#2026` |
| Personel 5 | `DEMOPER005` | `DemoSikerja#2026` |

Skenario Personel demo:

| Akun | Skenario |
|---|---|
| `DEMOPER001` | Laporan menunggu verifikasi |
| `DEMOPER002` | Laporan perlu revisi |
| `DEMOPER003` | Laporan sudah disetujui |
| `DEMOPER004` | Pekerjaan sedang berlangsung |
| `DEMOPER005` | Terjadwal tetapi belum check-in |

> Akun demo hanya untuk pengembangan dan presentasi. Hapus atau nonaktifkan akun demo sebelum aplikasi digunakan pada lingkungan production.

## Menjalankan Pengujian

```bash
php artisan optimize:clear
php artisan test
```

Pengujian mencakup:

- Hak akses setiap role.
- Akun nonaktif.
- Password sementara.
- Akses bukti foto presensi.
- Perlindungan data Personel.
- Halaman error aplikasi.

## Keamanan File

Foto presensi dan bukti pekerjaan disimpan pada:

```text
storage/app/private
```

File tidak boleh diakses langsung melalui URL publik. File hanya dikirim melalui controller yang telah memeriksa autentikasi dan hak akses pengguna.

Konfigurasi local storage:

```php
'serve' => false,
```

## Dokumentasi

- [Panduan Pengguna](docs/PANDUAN_PENGGUNA.md)
- [Backup dan Restore Database](docs/BACKUP_RESTORE.md)
- [Panduan Presentasi](docs/PANDUAN_PRESENTASI.md)
- [Panduan Deployment](docs/DEPLOYMENT.md)

## Struktur Penting

```text
app/Http/Controllers     Controller aplikasi
app/Models               Model database
app/Http/Middleware      Middleware keamanan
database/migrations      Struktur database
database/seeders         Data awal dan data demo
resources/views          Tampilan Blade
routes/web.php           Route aplikasi
storage/app/private      File privat
tests/Feature            Pengujian fitur
```

## Perintah Pemeliharaan

Membersihkan cache:

```bash
php artisan optimize:clear
```

Membuat ulang cache production:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Melihat daftar route:

```bash
php artisan route:list
```

## Catatan Production

Konfigurasi wajib:

```env
APP_ENV=production
APP_DEBUG=false
```

Jangan menggunakan akun demo atau password bawaan pada aplikasi production.
