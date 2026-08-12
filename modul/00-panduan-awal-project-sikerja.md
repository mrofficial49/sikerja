# Modul 00: Panduan Awal Project SIKERJA


> **Untuk pemula:** jangan menyalin semua modul sekaligus. Kerjakan satu modul, jalankan, uji, lalu lanjut.  
> Semua contoh kode diberi komentar pada bagian penting agar alur Laravel mudah dipahami.


## Tujuan

Modul ini menjadi peta besar sebelum mulai menulis kode. Setelah selesai membaca, Anda harus memahami:

- aplikasi apa yang akan dibuat;
- siapa yang menggunakan aplikasi;
- teknologi yang digunakan;
- database utama;
- alur bisnis;
- urutan modul;
- aturan belajar dan backup.

## Judul Project

```text
SIKERJA
Sistem Informasi Kinerja dan Aktivitas Personel
```

SIKERJA adalah aplikasi berbasis Laravel untuk membantu pelaksanaan dan monitoring aktivitas kerja Personel, khususnya skenario WFH.

## Masalah yang Diselesaikan

Tanpa sistem, kegiatan WFH dapat menimbulkan masalah seperti:

- jadwal Personel tersebar;
- check-in sulit dibuktikan;
- lokasi Personel tidak terdokumentasi;
- pekerjaan sulit dipantau;
- laporan tersebar melalui chat;
- Pimpinan sulit melihat siapa yang sudah bekerja;
- bukti pekerjaan tidak terarsip rapi;
- rekap harus dibuat manual.

SIKERJA menyatukan proses tersebut dalam satu aplikasi.

## Role Pengguna

### 1. Admin

Admin mengelola:

- unit kerja;
- pengguna;
- jadwal WFH;
- anggota jadwal;
- monitoring;
- verifikasi laporan;
- rekap PDF.

### 2. Pimpinan

Pimpinan dapat:

- memberikan tugas;
- memantau Personel;
- melihat bukti foto dan GPS;
- melihat laporan;
- menyetujui laporan;
- meminta revisi;
- mengunduh rekap PDF.

### 3. Personel

Personel dapat:

- melihat jadwal WFH;
- check-in dengan foto dan GPS;
- membuat rencana kerja;
- menerima tugas Pimpinan;
- mengisi progress pekerjaan;
- menulis kendala;
- upload bukti PDF;
- mengirim laporan;
- check-out dengan foto dan GPS;
- melihat notifikasi.

## Teknologi

- Laravel 12
- PHP minimal 8.2
- MariaDB atau MySQL
- Blade Template
- Bootstrap 5
- JavaScript
- Vite
- Eloquent ORM
- Laravel Middleware
- PHPUnit
- Laravel DOMPDF
- Git

## Tools

Periksa:

```bash
php -v
composer -V
node -v
npm -v
git --version
```

Gunakan editor seperti Visual Studio Code.

## Nama Folder Project

```text
sikerja
```

Contoh lokasi:

```text
/Users/user/sikerja
```

## Database

Nama database:

```text
sikerja
```

Contoh `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sikerja
DB_USERNAME=root
DB_PASSWORD=
```

## Tabel Utama

```text
roles
units
users
wfh_schedules
wfh_schedule_members
attendances
work_reports
work_items
work_item_files
app_notifications
activity_logs
```

Tabel Laravel pendukung dapat mencakup:

```text
sessions
cache
cache_locks
jobs
job_batches
failed_jobs
migrations
password_reset_tokens
```

## ERD Sederhana

```text
ROLES 1 ---------------- N USERS
UNITS 1 ---------------- N USERS

WFH_SCHEDULES 1 -------- N WFH_SCHEDULE_MEMBERS
USERS         1 -------- N WFH_SCHEDULE_MEMBERS

WFH_SCHEDULE_MEMBERS 1 -- 1 ATTENDANCES
WFH_SCHEDULE_MEMBERS 1 -- 1 WORK_REPORTS

WORK_REPORTS 1 ---------- N WORK_ITEMS
WORK_ITEMS   1 ---------- N WORK_ITEM_FILES
```

## Status Penting

### Jadwal WFH

```text
draft
active
completed
cancelled
```

### Anggota Jadwal

```text
scheduled
cancelled
schedule_change
present
absent
```

### Kehadiran

```text
present
absent
incomplete
```

### Sumber Pekerjaan

```text
personal_plan
leader_task
```

### Status Pekerjaan

```text
not_started
in_progress
blocked
completed
cancelled
```

### Status Laporan

```text
draft
waiting_verification
approved
needs_revision
incomplete
completed_offline
```

## Alur Bisnis

```text
ADMIN
  ↓
Membuat jadwal WFH
  ↓
Menambahkan Personel
  ↓
Mengaktifkan jadwal
  ↓
PERSONEL
  ↓
Check-in + Foto + GPS
  ↓
Rencana kerja sendiri / Tugas Pimpinan
  ↓
Update progress dan kendala
  ↓
Upload bukti pekerjaan
  ↓
Kirim laporan
  ↓
Check-out + Foto + GPS
  ↓
PIMPINAN / ADMIN
  ↓
Verifikasi
  ↓
Disetujui / Revisi
  ↓
Monitoring
  ↓
Ekspor PDF
```

## Daftar Modul Praktikum

```text
Modul 00  Panduan Awal Project
Modul 01  Membuat Project Laravel 12
Modul 02  Route, Controller, Blade, Bootstrap
Modul 03  Membuat Database dan Migration
Modul 04  Model, Relasi, Role, dan Seeder
Modul 05  Membuat Login dan Logout
Modul 06  Middleware dan Hak Akses Role
Modul 07  CRUD Unit Kerja
Modul 08  CRUD Pengguna dan Reset Password
Modul 09  Jadwal WFH dan Anggota Jadwal
Modul 10  Check-in Foto dan GPS
Modul 10A Check-out Foto dan GPS
Modul 11  Rencana Kerja Pribadi
Modul 12  Penugasan oleh Pimpinan
Modul 13  Progress, Kendala, dan Bukti PDF
Modul 14  Laporan Kerja Personel
Modul 14A Verifikasi dan Revisi Laporan
Modul 15  Monitoring dan Rekap
Modul 15A Bukti Foto dan GPS Privat
Modul 15B Ekspor Rekap ke PDF
Modul 16  Pusat Notifikasi
Modul 17  Dashboard Admin, Pimpinan, Personel
Modul 18  Redesign UI Modern Military Executive
Modul 19  Testing dan Quality Assurance
Modul 20  Backup, Restore, dan Reset Demo
Modul 21  Deployment Production
Modul 22  Dokumentasi dan Presentasi
```

## Aturan Belajar

1. Baca tujuan modul sebelum coding.
2. Jalankan perintah terminal satu per satu.
3. Jangan lanjut bila masih error.
4. Uji manual setelah setiap fitur.
5. Gunakan Git sebagai checkpoint.
6. Backup database sebelum perubahan besar.
7. Jangan menjalankan `migrate:fresh` pada database penting.
8. Jangan menyimpan file sensitif di folder publik.
9. Pengguna yang sudah punya histori cukup dinonaktifkan.
10. Catat error lengkap sebelum meminta bantuan.

## Penjelasan untuk Pemula

Sebelum mulai coding, pahami dulu bahwa website modern biasanya memiliki beberapa bagian.

### 1. Frontend

Frontend adalah bagian yang dilihat pengguna di browser.

Contohnya:

- halaman login;
- tombol;
- tabel;
- form;
- warna;
- menu.

Pada SIKERJA, frontend dibuat menggunakan:

```text
HTML
CSS
Bootstrap
JavaScript
Blade
```

### 2. Backend

Backend adalah bagian yang bekerja di belakang layar.

Contohnya:

- memeriksa login;
- menyimpan data ke database;
- memeriksa role;
- membuat laporan;
- mengambil data monitoring.

Pada SIKERJA, backend menggunakan:

```text
Laravel 12
PHP
```

### 3. Database

Database adalah tempat menyimpan data.

Contohnya:

```text
Pengguna
Unit
Jadwal
Presensi
Pekerjaan
Laporan
Notifikasi
```

Pada project ini database menggunakan MySQL/MariaDB.

### 4. Cara Website SIKERJA Bekerja

Secara sederhana:

```text
Pengguna klik tombol
        ↓
Browser mengirim request
        ↓
Laravel menerima request
        ↓
Controller menjalankan logika
        ↓
Model membaca/menyimpan database
        ↓
Laravel mengirim hasil
        ↓
Browser menampilkan halaman
```

Jadi saat belajar, jangan hanya menghafal kode. Coba pahami **data datang dari mana, diproses di mana, lalu ditampilkan di mana**.

## Penjelasan Gamblang: Bagian Ini Untuk Apa?

### `Laravel 12`
Digunakan sebagai kerangka utama aplikasi. Laravel mengatur route, controller, model, database, session, keamanan, dan struktur project supaya kode tidak berantakan.

### `Blade`
Digunakan untuk membuat halaman HTML yang bisa menerima data dari Laravel.

Contoh:

```blade
{{ $user->name }}
```

digunakan untuk menampilkan nama pengguna dari backend ke browser.

### `Bootstrap`
Digunakan untuk mempercepat pembuatan tampilan seperti tombol, form, navbar, card, tabel, dan responsive layout.

### `JavaScript`
Digunakan saat halaman perlu berinteraksi langsung dengan browser, misalnya kamera dan GPS.

### `MySQL/MariaDB`
Digunakan untuk menyimpan seluruh data aplikasi secara permanen.

### `Git`
Digunakan untuk menyimpan riwayat perubahan source code. Kalau terjadi kesalahan, kita bisa melihat atau kembali ke versi sebelumnya.

### `Role`
Digunakan untuk membedakan hak akses. Admin, Pimpinan, dan Personel tidak boleh memiliki kewenangan yang sama.

### `Tabel`
Setiap tabel punya fungsi berbeda. Misalnya `users` menyimpan pengguna, `attendances` menyimpan presensi, `work_items` menyimpan pekerjaan.

### `ERD`
Digunakan untuk memahami hubungan antar tabel sebelum coding database.

## Checklist Sebelum Modul 01

- [ ] PHP tersedia
- [ ] Composer tersedia
- [ ] Node.js tersedia
- [ ] npm tersedia
- [ ] MySQL/MariaDB berjalan
- [ ] Visual Studio Code tersedia
- [ ] Browser tersedia
- [ ] Git tersedia
- [ ] Database dapat dibuat
- [ ] Memahami tiga role
- [ ] Memahami alur bisnis

## Modul Berikutnya

Modul 01 akan membuat project Laravel 12 dari nol.
