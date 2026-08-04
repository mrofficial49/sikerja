# SIKERJA

**Sistem Informasi Kinerja dan Aktivitas Personel**

SIKERJA adalah aplikasi berbasis web untuk mendukung pelaksanaan dan pengawasan Work From Home (WFH) secara terintegrasi. Sistem mencakup pengelolaan jadwal, presensi berbasis foto dan GPS, penugasan, pelaporan hasil kerja, verifikasi berjenjang, monitoring, notifikasi, serta ekspor rekap kinerja ke PDF.

---

## Fitur Utama

### Autentikasi dan keamanan
- Login menggunakan NRP, NIP, atau ID Login.
- Role Admin, Pimpinan, dan Personel.
- Validasi akun aktif.
- Kewajiban mengganti password sementara.
- Pembatasan akses berdasarkan role.
- Halaman error khusus.
- Pengujian otomatis hak akses.

### Pengelolaan WFH
- Pembuatan dan aktivasi jadwal WFH.
- Penentuan Personel peserta WFH.
- Pengelolaan status anggota jadwal.
- Monitoring kehadiran dan pelaksanaan kerja.

### Presensi foto dan GPS
- Check-in menggunakan kamera dan lokasi.
- Check-out menggunakan kamera dan lokasi.
- Penyimpanan waktu, latitude, dan longitude.
- Foto presensi disimpan pada storage privat.
- Admin dan Pimpinan dapat melihat bukti presensi.
- Personel hanya dapat melihat bukti presensinya sendiri.
- Tautan lokasi ke Google Maps.

### Pekerjaan dan penugasan
- Rencana kerja pribadi.
- Penugasan oleh Pimpinan.
- Status pekerjaan: belum dimulai, berlangsung, terkendala, selesai, dan dibatalkan.
- Pencatatan progres, kendala, dan tindak lanjut.
- Upload bukti pekerjaan PDF.

### Laporan dan verifikasi
- Pengiriman laporan kerja.
- Verifikasi oleh Admin atau Pimpinan.
- Persetujuan laporan.
- Permintaan revisi.
- Pengiriman ulang hasil revisi.
- Catatan verifikasi.

### Monitoring dan rekap
- Ringkasan jumlah Personel.
- Ringkasan check-in dan check-out.
- Ringkasan status laporan.
- Filter berdasarkan jadwal dan unit.
- Pencarian Personel.
- Akses bukti foto dan GPS.
- Akses detail laporan.
- Ekspor rekap kinerja ke PDF.

### Notifikasi
- Jadwal WFH.
- Perubahan jadwal.
- Tugas baru.
- Laporan menunggu verifikasi.
- Permintaan revisi.
- Persetujuan laporan.

### Antarmuka
- Tema Modern Military Executive.
- Sidebar desktop.
- Sidebar mobile offcanvas.
- Topbar profil dan notifikasi.
- Halaman login profesional.
- Identitas visual hijau, putih, dan emas.
- Logo SIKERJA pada sidebar, login, dan PDF.
- Responsif untuk desktop, tablet, dan ponsel.

---

## Teknologi

- Laravel 12
- PHP 8.2 atau lebih baru
- MariaDB atau MySQL
- Bootstrap
- JavaScript
- Vite
- PHPUnit
- Laravel DOMPDF

Dependency PDF:

```text
barryvdh/laravel-dompdf
```

---

## Hak Akses Pengguna

### Admin
Admin dapat mengelola unit, pengguna, jadwal WFH, anggota jadwal, monitoring, verifikasi laporan, bukti presensi, dan ekspor rekap PDF.

### Pimpinan
Pimpinan dapat memberikan tugas, memantau Personel, melihat bukti presensi, memverifikasi laporan, serta mengekspor rekap PDF.

### Personel
Personel dapat melakukan presensi, membuat rencana kerja, menerima tugas, memperbarui progres, mengunggah bukti, mengirim laporan, memperbaiki revisi, melakukan check-out, dan melihat notifikasi.

---

## Alur Bisnis

```text
Admin membuat jadwal WFH
        ↓
Admin menambahkan Personel
        ↓
Admin mengaktifkan jadwal
        ↓
Personel check-in dengan foto dan GPS
        ↓
Personel membuat rencana kerja atau menerima tugas
        ↓
Personel memperbarui progres dan bukti pekerjaan
        ↓
Personel mengirim laporan
        ↓
Personel check-out dengan foto dan GPS
        ↓
Admin/Pimpinan memeriksa laporan
        ↓
Laporan disetujui atau dikembalikan untuk revisi
        ↓
Admin/Pimpinan mengekspor rekap PDF
```

---

## Persyaratan Sistem

- PHP 8.2 atau lebih baru
- Composer
- Node.js dan NPM
- MariaDB atau MySQL
- Git
- Browser modern
- Kamera dan layanan lokasi untuk presensi

Periksa versi:

```bash
php -v
composer --version
node -v
npm -v
mysql --version
```

---

## Instalasi Lokal

### 1. Clone repository

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

### 4. Buat konfigurasi aplikasi

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

### 7. Buat data demo presentasi

```bash
php artisan db:seed --class=DemoSeeder
```

---

## Menjalankan Aplikasi

Terminal pertama:

```bash
npm run dev
```

Terminal kedua:

```bash
php artisan serve
```

Buka:

```text
http://127.0.0.1:8000
```

Untuk build aset:

```bash
npm run build
php artisan optimize:clear
```

---

## Data Demo

Semua akun demo menggunakan password:

```text
DemoSikerja#2026
```

| Role | ID Login | Password |
|---|---|---|
| Admin | `DEMOADMIN` | `DemoSikerja#2026` |
| Pimpinan | `DEMOPIMPINAN` | `DemoSikerja#2026` |
| Personel 1 | `DEMOPER001` | `DemoSikerja#2026` |
| Personel 2 | `DEMOPER002` | `DemoSikerja#2026` |
| Personel 3 | `DEMOPER003` | `DemoSikerja#2026` |
| Personel 4 | `DEMOPER004` | `DemoSikerja#2026` |
| Personel 5 | `DEMOPER005` | `DemoSikerja#2026` |

Skenario demo:

| Akun | Skenario |
|---|---|
| `DEMOPER001` | Laporan menunggu verifikasi |
| `DEMOPER002` | Laporan perlu revisi |
| `DEMOPER003` | Laporan disetujui |
| `DEMOPER004` | Pekerjaan sedang berlangsung |
| `DEMOPER005` | Terjadwal tetapi belum check-in |

> Akun demo hanya untuk pengembangan dan presentasi. Nonaktifkan atau hapus sebelum digunakan pada production.

---

## Ekspor Rekap PDF

Fitur tersedia pada menu **Monitoring & Rekap** untuk Admin dan Pimpinan.

Cara menggunakan:

1. Pilih jadwal WFH.
2. Pilih unit kerja bila diperlukan.
3. Isi pencarian Personel bila diperlukan.
4. Tekan **Tampilkan**.
5. Tekan **Ekspor Rekap PDF**.

Format nama file:

```text
rekap-kinerja-wfh-YYYY-MM-DD.pdf
```

Isi PDF:
- Logo SIKERJA.
- Informasi jadwal.
- Ringkasan presensi dan pekerjaan.
- Identitas Personel.
- Unit dan jabatan.
- Waktu check-in dan check-out.
- Judul pekerjaan, target, progres, dan kendala.
- Status dan catatan verifikasi.
- Kolom pengesahan.
- Nomor halaman.

Periksa dependency:

```bash
composer show barryvdh/laravel-dompdf
```

Logo PDF berada pada:

```text
public/images/logo-sikerja.png
```

---

## Keamanan File

Foto presensi dan bukti pekerjaan disimpan pada:

```text
storage/app/private
```

Konfigurasi disk lokal:

```php
'serve' => false,
```

File privat hanya dikirim melalui controller yang telah memeriksa autentikasi dan hak akses.

---

## Pengujian

```bash
php artisan optimize:clear
php artisan test
```

Periksa route PDF:

```bash
php artisan route:list --name=monitoring.pdf
```

Route yang harus tersedia:

```text
admin.monitoring.pdf
leader.monitoring.pdf
```

---

## Backup Database

Lokasi backup:

```text
storage/app/backups/database
```

Pastikan `.gitignore` memuat:

```text
/storage/app/backups/
```

Contoh backup pada XAMPP macOS:

```bash
mkdir -p storage/app/backups/database

BACKUP_TIME=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="storage/app/backups/database/sikerja_${BACKUP_TIME}.sql"

/Applications/XAMPP/xamppfiles/bin/mariadb-dump \
    --host=127.0.0.1 \
    --port=3306 \
    --user=root \
    --password \
    --single-transaction \
    --quick \
    --triggers \
    --skip-routines \
    --skip-events \
    --default-character-set=utf8mb4 \
    --result-file="$BACKUP_FILE" \
    sikerja
```

Kompres backup:

```bash
gzip -k "$BACKUP_FILE"
```

Backup file privat:

```bash
tar -czf \
storage/app/backups/private_files_$(date +"%Y%m%d_%H%M%S").tar.gz \
storage/app/private
```

---

## Deployment Production

Konfigurasi `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://alamat-aplikasi
FILESYSTEM_DISK=local
```

Instalasi:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
```

Optimasi:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Hak akses folder:

```bash
chmod -R 775 storage bootstrap/cache
```

Document root harus diarahkan ke:

```text
/path/proyek/sikerja/public
```

Pastikan:
- `APP_DEBUG=false`.
- HTTPS aktif.
- `.env` tidak dapat diakses publik.
- Storage privat tidak disajikan langsung.
- Akun demo dinonaktifkan.
- Logo SIKERJA tersedia.
- DOMPDF terpasang.
- Backup database tersedia.

---

## Struktur Folder Penting

```text
app/Http/Controllers
app/Http/Middleware
app/Models
database/migrations
database/seeders
resources/css
resources/js
resources/views
routes/web.php
storage/app/private
storage/app/backups
tests/Feature
public/images/logo-sikerja.png
```

---

## Dokumentasi Tambahan

- [Panduan Pengguna](docs/PANDUAN_PENGGUNA.md)
- [Backup dan Restore](docs/BACKUP_RESTORE.md)
- [Panduan Presentasi](docs/PANDUAN_PRESENTASI.md)
- [Panduan Deployment](docs/DEPLOYMENT.md)
- [Catatan Perubahan](docs/CHANGELOG.md)

---

## Perintah Pemeliharaan

```bash
php artisan optimize:clear
php artisan route:list
php artisan migrate:status
php artisan about
npm run build
php artisan test
```

---

## Catatan Versi

### Versi 1.1.0
- Redesign antarmuka.
- Sidebar desktop dan mobile.
- Identitas logo diperkuat.
- Pemuatan CSS diperbaiki.
- Bukti foto dan GPS presensi.
- Ekspor rekap kinerja ke PDF.
- Laravel DOMPDF ditambahkan.
- Dokumentasi diperbarui.

### Versi 1.0.0
- Autentikasi dan role.
- Pengelolaan unit dan pengguna.
- Pengelolaan jadwal WFH.
- Presensi foto dan GPS.
- Rencana kerja pribadi.
- Penugasan Pimpinan.
- Pelaporan dan verifikasi.
- Monitoring dan rekap.
- Pusat notifikasi.
- Data demo.
- Backup database.
- Pengujian otomatis.

---

## Lisensi dan Penggunaan

SIKERJA dikembangkan sebagai aplikasi internal dan bahan presentasi proyek akhir. Penggunaan, distribusi, dan pengembangan lanjutan menyesuaikan kebijakan organisasi.

---

## SIKERJA

**Profesional · Responsif · Integratif · Modern · Adaptif**
