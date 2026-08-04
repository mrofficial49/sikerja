# Backup dan Restore Database SIKERJA

## Lokasi Backup

```text
storage/app/backups/database
```

Folder backup tidak boleh dimasukkan ke Git.

Pastikan `.gitignore` memuat:

```text
/storage/app/backups/
```

## Backup Database pada XAMPP macOS

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

Saat diminta password, masukkan password MySQL root. Tekan Enter apabila password kosong.

## Memeriksa Backup

```bash
test -s "$BACKUP_FILE" \
&& echo "Backup valid dan berisi data." \
|| echo "Backup kosong atau gagal."
```

```bash
grep -m 5 "CREATE TABLE" "$BACKUP_FILE"
```

## Kompres Backup

```bash
gzip -k "$BACKUP_FILE"
```

Verifikasi:

```bash
gzip -t "$BACKUP_FILE.gz" \
&& echo "Backup terkompres valid."
```

## Restore Database

> Backup database saat ini sebelum melakukan restore.

Aktifkan mode pemeliharaan:

```bash
php artisan down
```

Restore:

```bash
/Applications/XAMPP/xamppfiles/bin/mariadb \
    --host=127.0.0.1 \
    --port=3306 \
    --user=root \
    --password \
    sikerja \
    < storage/app/backups/database/NAMA_FILE_BACKUP.sql
```

Setelah selesai:

```bash
php artisan optimize:clear
php artisan up
```

## Restore File Gzip

```bash
gunzip -c \
storage/app/backups/database/NAMA_FILE.sql.gz \
| /Applications/XAMPP/xamppfiles/bin/mariadb \
    --host=127.0.0.1 \
    --port=3306 \
    --user=root \
    --password \
    sikerja
```

## Backup File Privat

Selain database, backup juga folder:

```text
storage/app/private
```

Contoh:

```bash
tar -czf \
storage/app/backups/private_files_$(date +"%Y%m%d_%H%M%S").tar.gz \
storage/app/private
```
