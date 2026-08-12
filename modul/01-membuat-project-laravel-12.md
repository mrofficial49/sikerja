# Modul 01: Membuat Project Laravel 12


> **Untuk pemula:** jangan menyalin semua modul sekaligus. Kerjakan satu modul, jalankan, uji, lalu lanjut.  
> Semua contoh kode diberi komentar pada bagian penting agar alur Laravel mudah dipahami.


## Tujuan

Membuat project Laravel 12 baru, memasang dependency, mengatur `.env`, membuat database `sikerja`, menjalankan Laravel, dan menyiapkan Git.

## Hasil Akhir

Saat membuka:

```text
http://127.0.0.1:8000
```

halaman Laravel dapat tampil.

## Prasyarat

Periksa:

```bash
php -v
composer -V
node -v
npm -v
git --version
```

## Langkah 1: Buat Project

```bash
composer create-project laravel/laravel sikerja "^12.0"
```

Masuk:

```bash
cd sikerja
```

## Langkah 2: Periksa Struktur

```bash
ls
```

Folder penting:

```text
app
bootstrap
config
database
public
resources
routes
storage
tests
```

## Langkah 3: Instal Dependency Frontend

```bash
npm install
```

## Langkah 4: Buat Database

Buat database melalui phpMyAdmin/MySQL Workbench:

```sql
CREATE DATABASE sikerja
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

## Langkah 5: Atur `.env`

Buka `.env`:

```env
APP_NAME=SIKERJA
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sikerja
DB_USERNAME=root
DB_PASSWORD=
```

## Langkah 6: Generate Application Key

```bash
php artisan key:generate
```

## Langkah 7: Bersihkan Cache

```bash
php artisan optimize:clear
```

## Langkah 8: Periksa Laravel

```bash
php artisan about
```

## Langkah 9: Jalankan Backend

```bash
php artisan serve
```

## Langkah 10: Jalankan Vite

Buka terminal baru:

```bash
npm run dev
```

## Langkah 11: Inisialisasi Git

Jika belum:

```bash
git init
git add .
git commit -m "Inisialisasi project Laravel 12 SIKERJA"
```

## Penjelasan Penting

### `composer create-project`

Membuat source Laravel dan memasang package PHP.

### `.env`

Menyimpan konfigurasi lokal. File ini tidak boleh dipublikasikan.

### `APP_KEY`

Digunakan Laravel untuk enkripsi.

### `npm run dev`

Menjalankan Vite untuk CSS/JavaScript saat development.

## Pengujian Manual

1. Buka `http://127.0.0.1:8000`.
2. Pastikan halaman tidak error.
3. Jalankan:

```bash
php artisan route:list
```

4. Periksa database:

```bash
php artisan migrate:status
```

## Kesalahan Umum

### Unknown database `sikerja`

Database belum dibuat.

### Access denied

Periksa username/password MySQL.

### `Vite manifest not found`

Jalankan:

```bash
npm run dev
```

atau:

```bash
npm run build
```

## Penjelasan untuk Pemula

Pada modul ini kita belum membuat fitur SIKERJA. Kita hanya menyiapkan "rumah" tempat aplikasi akan dibangun.

### Apa itu Laravel?

Laravel adalah framework PHP.

Framework dapat dibayangkan sebagai kerangka rumah. Kita tidak perlu membangun semuanya dari nol karena Laravel sudah menyediakan:

- routing;
- koneksi database;
- keamanan;
- session;
- validasi;
- model;
- controller;
- Blade.

### Apa itu Composer?

Composer adalah pengelola package PHP.

Contoh:

```bash
composer install
```

artinya:

> "Baca daftar kebutuhan project, lalu pasang semua package PHP yang dibutuhkan."

### Apa itu npm?

npm mengelola package frontend seperti Bootstrap dan JavaScript.

### Apa itu `.env`?

`.env` adalah file konfigurasi khusus komputer kita.

Contoh:

```env
DB_DATABASE=sikerja
```

berarti Laravel harus terhubung ke database bernama `sikerja`.

### Kenapa `APP_KEY` penting?

Laravel menggunakan `APP_KEY` untuk membantu proses enkripsi.

Karena itu setelah membuat project baru jalankan:

```bash
php artisan key:generate
```

### Apa itu Artisan?

Artisan adalah command-line helper milik Laravel.

Contoh:

```bash
php artisan serve
```

artinya menjalankan server development Laravel.

## Penjelasan Gamblang: Setiap Perintah Ini Untuk Apa?

### `composer create-project laravel/laravel sikerja`
Membuat folder project Laravel baru bernama `sikerja`.

### `cd sikerja`
Masuk ke folder project supaya perintah berikutnya dijalankan di tempat yang benar.

### `npm install`
Mengunduh dependency frontend yang tercantum di `package.json`.

### `.env`
Dipakai untuk konfigurasi yang berbeda pada setiap komputer, misalnya nama database dan URL aplikasi.

### `php artisan key:generate`
Membuat kunci keamanan Laravel. Tanpa ini beberapa fitur enkripsi/session tidak bekerja dengan benar.

### `php artisan optimize:clear`
Menghapus cache konfigurasi lama supaya perubahan terbaru terbaca.

### `php artisan serve`
Menjalankan server lokal Laravel.

### `npm run dev`
Menjalankan Vite agar CSS/JavaScript dapat dimuat saat development.

### `git init`
Membuat folder project menjadi repository Git.

### `git commit`
Menyimpan checkpoint perubahan.

## Checklist

- [ ] Project dibuat
- [ ] Database `sikerja` dibuat
- [ ] `.env` benar
- [ ] APP_KEY tersedia
- [ ] Laravel berjalan
- [ ] Vite berjalan
- [ ] Git checkpoint tersedia


## Simpan Checkpoint Git

Setelah modul berhasil:

```bash
git status
git add .
git commit -m "Selesaikan modul ini"
```

Tujuan checkpoint adalah agar project mudah dikembalikan bila modul berikutnya mengalami error.


## Modul Berikutnya

Modul 02 membuat Route, Controller, Blade, Bootstrap, dan layout awal.
