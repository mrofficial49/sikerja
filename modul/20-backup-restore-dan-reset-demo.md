# Modul 20: Backup, Restore, dan Reset Demo

## Tujuan

Membuat prosedur aman untuk:

- backup database;
- backup foto/bukti privat;
- mengosongkan data aktivitas untuk presentasi;
- restore ketika diperlukan.

## Prinsip

Backup dilakukan sebelum reset.

Data master yang dipertahankan:

```text
roles
units
users
```

Data aktivitas yang dapat dikosongkan:

```text
work_item_files
work_items
work_reports
attendances
wfh_schedule_members
wfh_schedules
app_notifications
activity_logs
```

## Langkah 1: Backup Database

Untuk XAMPP macOS:

```bash
mkdir -p storage/app/backups/database

BACKUP_TIME=$(date +"%Y%m%d_%H%M%S")

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
    --result-file="storage/app/backups/database/sikerja_${BACKUP_TIME}.sql" \
    sikerja
```

## Langkah 2: Backup Private Files

```bash
tar -czf \
storage/app/backups/private_$(date +"%Y%m%d_%H%M%S").tar.gz \
storage/app/private
```

## Langkah 3: Maintenance

```bash
php artisan down
```

## Langkah 4: Reset Aktivitas

```bash
php artisan tinker --execute="
\$tables = [
    'work_item_files',
    'work_items',
    'work_reports',
    'attendances',
    'wfh_schedule_members',
    'wfh_schedules',
    'app_notifications',
    'activity_logs',
];

\Illuminate\Support\Facades\Schema
    ::disableForeignKeyConstraints();

try {
    foreach (\$tables as \$table) {
        if (
            \Illuminate\Support\Facades\Schema
                ::hasTable(\$table)
        ) {
            \Illuminate\Support\Facades\DB
                ::table(\$table)
                ->truncate();

            echo 'Dikosongkan: '
                . \$table
                . PHP_EOL;
        }
    }
} finally {
    \Illuminate\Support\Facades\Schema
        ::enableForeignKeyConstraints();
}
"
```

## Langkah 5: Hapus File Privat

Setelah backup:

```bash
find storage/app/private \
    -mindepth 1 \
    -exec rm -rf {} +

mkdir -p storage/app/private
```

## Langkah 6: Session

Jika menggunakan tabel `sessions`:

```bash
php artisan tinker --execute="
if (
    \Illuminate\Support\Facades\Schema
        ::hasTable('sessions')
) {
    \Illuminate\Support\Facades\DB
        ::table('sessions')
        ->truncate();
}
"
```

## Langkah 7: Notifikasi

Periksa:

```bash
php artisan tinker --execute="
foreach (
    ['app_notifications','notifications']
    as \$table
) {
    if (
        \Illuminate\Support\Facades\Schema
            ::hasTable(\$table)
    ) {
        echo \$table . ': '
            . \Illuminate\Support\Facades\DB
                ::table(\$table)
                ->count()
            . PHP_EOL;
    }
}
"
```

## Langkah 8: Cache

```bash
php artisan optimize:clear
```

## Langkah 9: Aktifkan Lagi

```bash
php artisan up
```

## Langkah 10: Verifikasi

Tabel aktivitas harus `0`.

`users` dan `units` tetap ada.

## Restore

Masuk maintenance:

```bash
php artisan down
```

Restore SQL melalui MariaDB/MySQL, lalu:

```bash
php artisan optimize:clear
php artisan up
```

## Kesalahan Umum

### Semua user hilang

Anda menjalankan reset database penuh, bukan reset aktivitas.

### Notifikasi masih terlihat

Tabel aktual mungkin `notifications`, bukan `app_notifications`.

### Foto lama masih terlihat

File privat belum dihapus.

## Penjelasan untuk Pemula

Backup adalah salinan cadangan.

SIKERJA memiliki dua jenis data penting:

### 1. Database

Menyimpan:

```text
User
Jadwal
Presensi
Laporan
Notifikasi
```

### 2. File

Menyimpan:

```text
Foto presensi
Bukti pekerjaan
```

Karena itu backup database saja belum cukup.

### Reset Demo

Reset demo bukan menghapus aplikasi.

Yang dihapus hanya aktivitas, sedangkan:

```text
roles
units
users
```

tetap ada agar aplikasi siap digunakan lagi dari kondisi bersih.

## Penjelasan Gamblang: Backup dan Reset Ini Untuk Apa?

### Backup database
Menyimpan salinan seluruh data tabel.

### Backup private files
Menyimpan foto dan bukti pekerjaan yang tidak berada di database.

### Kenapa dua backup?
Database hanya menyimpan path file, bukan selalu isi file.

### `php artisan down`
Menutup aplikasi sementara saat maintenance.

### `truncate()`
Mengosongkan tabel sekaligus mereset auto increment.

### `disableForeignKeyConstraints()`
Mematikan pemeriksaan foreign key sementara agar tabel dapat dikosongkan sesuai urutan.

### Kenapa `roles`, `units`, `users` tidak dihapus?
Karena itu master data yang masih diperlukan setelah reset demo.

### `optimize:clear`
Membersihkan cache agar aplikasi kembali membaca keadaan terbaru.

## Checklist

- [ ] Database backup
- [ ] Private backup
- [ ] Maintenance
- [ ] Aktivitas kosong
- [ ] Users tetap
- [ ] Units tetap
- [ ] Notifikasi kosong
- [ ] Private kosong
- [ ] Cache clear

## Modul Berikutnya

Modul 21 membahas Deployment Production.
