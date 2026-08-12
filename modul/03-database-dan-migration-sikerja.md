# Modul 03: Membuat Database dan Migration SIKERJA


> **Untuk pemula:** jangan menyalin semua modul sekaligus. Kerjakan satu modul, jalankan, uji, lalu lanjut.  
> Semua contoh kode diberi komentar pada bagian penting agar alur Laravel mudah dipahami.


## Tujuan

Membuat struktur database inti SIKERJA dari migration Laravel.

## Hasil Akhir

Perintah:

```bash
php artisan migrate
```

membuat tabel bisnis utama.

## Prasyarat

- Database `sikerja` tersedia.
- `.env` sudah benar.
- Modul 01 dan 02 selesai.

## Strategi Migration

Kita membuat tabel berurutan agar foreign key tidak error.

Urutan:

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

## Langkah 1: Roles

```bash
php artisan make:migration create_roles_table
```

Isi `up()`:

```php
Schema::create('roles', function (Blueprint $table) {
    $table->id();

    $table->string('name', 50)
        ->unique();

    $table->string('description')
        ->nullable();

    $table->timestamps();
});
```

## Langkah 2: Units

```bash
php artisan make:migration create_units_table
```

```php
Schema::create('units', function (Blueprint $table) {
    $table->id();

    $table->string('code', 50)
        ->unique();

    $table->string('name', 150);

    $table->text('description')
        ->nullable();

    $table->boolean('is_active')
        ->default(true);

    $table->timestamps();
});
```

## Langkah 3: Sesuaikan Users

Buka migration users bawaan Laravel dan tambahkan:

```php
$table->foreignId('role_id')
    ->nullable()
    ->constrained('roles');

$table->foreignId('unit_id')
    ->nullable()
    ->constrained('units');

$table->string('login_id', 50)
    ->unique();

$table->string('rank', 100)
    ->nullable();

$table->string('position', 150)
    ->nullable();

$table->boolean('is_active')
    ->default(true);

$table->boolean('must_change_password')
    ->default(false);
```

Kolom `email` boleh tetap digunakan atau dibuat nullable jika login utama memakai `login_id`.

## Langkah 4: Jadwal WFH

```bash
php artisan make:migration create_wfh_schedules_table
```

```php
Schema::create('wfh_schedules', function (Blueprint $table) {
    $table->id();

    $table->date('wfh_date');

    $table->string('title', 150)
        ->nullable();

    $table->enum('status', [
        'draft',
        'active',
        'completed',
        'cancelled',
    ])->default('draft');

    $table->timestamps();
});
```

## Langkah 5: Anggota Jadwal

```bash
php artisan make:migration create_wfh_schedule_members_table
```

```php
Schema::create('wfh_schedule_members', function (Blueprint $table) {
    $table->id();

    $table->foreignId('schedule_id')
        ->constrained('wfh_schedules')
        ->cascadeOnDelete();

    $table->foreignId('user_id')
        ->constrained('users');

    $table->enum('member_status', [
        'scheduled',
        'cancelled',
        'schedule_change',
        'present',
        'absent',
    ])->default('scheduled');

    $table->timestamp('cancelled_at')
        ->nullable();

    $table->timestamps();

    $table->unique([
        'schedule_id',
        'user_id',
    ]);
});
```

## Langkah 6: Attendance

```bash
php artisan make:migration create_attendances_table
```

```php
Schema::create('attendances', function (Blueprint $table) {
    $table->id();

    $table->foreignId('schedule_member_id')
        ->unique()
        ->constrained('wfh_schedule_members')
        ->cascadeOnDelete();

    $table->timestamp('checkin_at')
        ->nullable();

    $table->decimal(
        'checkin_latitude',
        10,
        7
    )->nullable();

    $table->decimal(
        'checkin_longitude',
        10,
        7
    )->nullable();

    $table->string('checkin_photo_path')
        ->nullable();

    $table->text('checkin_late_reason')
        ->nullable();

    $table->timestamp('checkout_at')
        ->nullable();

    $table->decimal(
        'checkout_latitude',
        10,
        7
    )->nullable();

    $table->decimal(
        'checkout_longitude',
        10,
        7
    )->nullable();

    $table->string('checkout_photo_path')
        ->nullable();

    $table->enum('attendance_status', [
        'present',
        'absent',
        'incomplete',
    ])->default('incomplete');

    $table->timestamps();
});
```

## Langkah 7: Work Reports

```bash
php artisan make:migration create_work_reports_table
```

```php
Schema::create('work_reports', function (Blueprint $table) {
    $table->id();

    $table->foreignId('schedule_member_id')
        ->unique()
        ->constrained('wfh_schedule_members')
        ->cascadeOnDelete();

    $table->enum('status', [
        'draft',
        'waiting_verification',
        'approved',
        'needs_revision',
        'incomplete',
        'completed_offline',
    ])->default('draft');

    $table->text('verification_note')
        ->nullable();

    $table->timestamp('submitted_at')
        ->nullable();

    $table->timestamp('verified_at')
        ->nullable();

    $table->foreignId('verified_by')
        ->nullable()
        ->constrained('users');

    $table->timestamps();
});
```

## Langkah 8: Work Items

```bash
php artisan make:migration create_work_items_table
```

```php
Schema::create('work_items', function (Blueprint $table) {
    $table->id();

    $table->foreignId('report_id')
        ->constrained('work_reports')
        ->cascadeOnDelete();

    $table->foreignId('created_by')
        ->constrained('users');

    $table->enum('source_type', [
        'personal_plan',
        'leader_task',
    ]);

    $table->string('title');

    $table->text('description')
        ->nullable();

    $table->text('target_result')
        ->nullable();

    $table->enum('status', [
        'not_started',
        'in_progress',
        'blocked',
        'completed',
        'cancelled',
    ])->default('not_started');

    $table->unsignedTinyInteger('progress')
        ->default(0);

    $table->text('obstacle')
        ->nullable();

    $table->text('follow_up_plan')
        ->nullable();

    $table->boolean('continue_offline')
        ->default(false);

    $table->foreignId('cancelled_by')
        ->nullable()
        ->constrained('users');

    $table->timestamp('cancelled_at')
        ->nullable();

    $table->timestamp('assigned_at')
        ->nullable();

    $table->timestamps();
});
```

## Langkah 9: Work Item Files

```bash
php artisan make:migration create_work_item_files_table
```

PENTING: project menggunakan `item_id`.

```php
Schema::create('work_item_files', function (Blueprint $table) {
    $table->id();

    $table->foreignId('item_id')
        ->constrained('work_items')
        ->cascadeOnDelete();

    $table->string('file_path');

    $table->string('original_name')
        ->nullable();

    $table->string('mime_type')
        ->nullable();

    $table->unsignedBigInteger('file_size')
        ->nullable();

    $table->timestamps();
});
```

## Langkah 10: Notifications

```bash
php artisan make:migration create_app_notifications_table
```

```php
Schema::create('app_notifications', function (Blueprint $table) {
    $table->id();

    $table->foreignId('user_id')
        ->constrained('users')
        ->cascadeOnDelete();

    $table->string('type', 100);
    $table->string('title', 200);
    $table->text('message');
    $table->text('url')->nullable();

    $table->timestamp('read_at')
        ->nullable();

    $table->timestamps();
});
```

## Langkah 11: Activity Logs

```bash
php artisan make:migration create_activity_logs_table
```

```php
Schema::create('activity_logs', function (Blueprint $table) {
    $table->id();

    $table->foreignId('user_id')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();

    $table->string('action', 150);
    $table->text('description')->nullable();
    $table->string('ip_address', 45)->nullable();

    $table->timestamps();
});
```

## Langkah 12: Jalankan Migration

```bash
php artisan migrate
```

## Langkah 13: Periksa Tabel

```bash
php artisan migrate:status
php artisan db:table users
php artisan db:table work_items
php artisan db:table work_item_files
```

## Kesalahan Umum

### Foreign key constraint

Urutan migration salah.

### Unknown column `work_item_id`

Kolom benar:

```text
item_id
```

### `migrate:fresh` menghapus data

Gunakan hanya pada project latihan yang memang boleh dihapus.

## Penjelasan untuk Pemula

Migration adalah cara Laravel membuat struktur database menggunakan PHP.

Daripada membuat tabel satu per satu melalui phpMyAdmin, kita menuliskan struktur tabel dalam file migration.

Contoh:

```php
$table->string('name', 150);
```

artinya:

> Buat kolom `name` bertipe teks dengan panjang maksimal 150 karakter.

### Primary Key

```php
$table->id();
```

membuat kolom `id` sebagai identitas unik.

### Foreign Key

Contoh:

```php
$table->foreignId('user_id')
    ->constrained('users');
```

artinya data pada `user_id` harus menunjuk ke user yang benar-benar ada.

### Kenapa Relasi Penting?

Tanpa relasi, database akan mudah kacau.

Contoh:

```text
Laporan milik siapa?
Presensi milik siapa?
Tugas milik siapa?
```

Foreign key membantu menjaga hubungan tersebut.

### Enum

Enum membatasi isi kolom hanya pada pilihan tertentu.

Contoh:

```text
draft
active
completed
cancelled
```

Dengan begitu developer tidak dapat sembarang menyimpan status seperti:

```text
selesai_banget
```

yang tidak dikenal sistem.

## Penjelasan Gamblang: Setiap Komponen Database Ini Untuk Apa?

### Migration
Migration dipakai untuk membuat struktur database melalui kode PHP. Keuntungannya: struktur database dapat dilacak Git dan dibuat ulang pada komputer lain.

### `$table->id()`
Membuat kolom `id` sebagai identitas unik setiap baris data.

### `$table->string(...)`
Membuat kolom teks pendek.

### `$table->text(...)`
Membuat kolom teks panjang.

### `$table->boolean(...)`
Membuat nilai benar/salah, misalnya `is_active`.

### `$table->foreignId(...)`
Membuat kolom yang menghubungkan tabel dengan tabel lain.

### `->constrained('users')`
Memastikan nilai foreign key benar-benar menunjuk ke data yang ada pada tabel `users`.

### `->cascadeOnDelete()`
Jika data induk dihapus, data turunan ikut dihapus. Digunakan hanya pada relasi yang memang aman seperti member jadwal terhadap jadwal.

### `enum`
Membatasi nilai status supaya tidak sembarang teks.

### `unique`
Mencegah data ganda. Contoh satu Personel tidak boleh masuk dua kali pada jadwal yang sama.

### `timestamps()`
Membuat `created_at` dan `updated_at`.

### `php artisan migrate`
Menjalankan seluruh migration yang belum pernah dijalankan.

## Checklist

- [ ] Seluruh migration dibuat
- [ ] Foreign key benar
- [ ] Enum benar
- [ ] `item_id` benar
- [ ] `php artisan migrate` berhasil


## Simpan Checkpoint Git

Setelah modul berhasil:

```bash
git status
git add .
git commit -m "Selesaikan modul ini"
```

Tujuan checkpoint adalah agar project mudah dikembalikan bila modul berikutnya mengalami error.


## Modul Berikutnya

Modul 04 membuat model, relasi, role, unit, dan seeder awal.
