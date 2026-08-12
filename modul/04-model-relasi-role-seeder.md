# Modul 04: Model, Relasi, Role, dan Seeder


> **Untuk pemula:** jangan menyalin semua modul sekaligus. Kerjakan satu modul, jalankan, uji, lalu lanjut.  
> Semua contoh kode diberi komentar pada bagian penting agar alur Laravel mudah dipahami.


## Tujuan

Membuat model Eloquent, relasi utama, role awal, dan unit awal.

## Hasil Akhir

Tinker dapat membaca hubungan:

```text
User -> Role
User -> Unit
Schedule -> Members
Member -> Attendance
Member -> WorkReport
Report -> Items
Item -> Files
```

## Langkah 1: Buat Model

```bash
php artisan make:model Role
php artisan make:model Unit
php artisan make:model WfhSchedule
php artisan make:model WfhScheduleMember
php artisan make:model Attendance
php artisan make:model WorkReport
php artisan make:model WorkItem
php artisan make:model WorkItemFile
php artisan make:model AppNotification
php artisan make:model ActivityLog
```

## Langkah 2: Model Role

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
```

## Langkah 3: Model Unit

```php
class Unit extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
```

## Langkah 4: User

Tambahkan:

```php
public function role()
{
    return $this->belongsTo(Role::class);
}

public function unit()
{
    return $this->belongsTo(Unit::class);
}

public function scheduleMemberships()
{
    return $this->hasMany(
        WfhScheduleMember::class,
        'user_id'
    );
}

public function isAdmin(): bool
{
    return $this->role?->name === 'Admin';
}

public function isLeader(): bool
{
    return $this->role?->name === 'Pimpinan';
}

public function isPersonnel(): bool
{
    return $this->role?->name === 'Personel';
}
```

Cast:

```php
protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'must_change_password' => 'boolean',
    ];
}
```

## Langkah 5: WfhSchedule

```php
class WfhSchedule extends Model
{
    protected $fillable = [
        'wfh_date',
        'title',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'wfh_date' => 'date',
        ];
    }

    public function members()
    {
        return $this->hasMany(
            WfhScheduleMember::class,
            'schedule_id'
        );
    }
}
```

## Langkah 6: WfhScheduleMember

```php
class WfhScheduleMember extends Model
{
    protected $fillable = [
        'schedule_id',
        'user_id',
        'member_status',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'cancelled_at' => 'datetime',
        ];
    }

    public function schedule()
    {
        return $this->belongsTo(
            WfhSchedule::class,
            'schedule_id'
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->hasOne(
            Attendance::class,
            'schedule_member_id'
        );
    }

    public function workReport()
    {
        return $this->hasOne(
            WorkReport::class,
            'schedule_member_id'
        );
    }
}
```

## Langkah 7: WorkReport dan WorkItem

```php
public function items()
{
    return $this->hasMany(
        WorkItem::class,
        'report_id'
    );
}
```

Pada WorkItem:

```php
public function report()
{
    return $this->belongsTo(
        WorkReport::class,
        'report_id'
    );
}

public function files()
{
    return $this->hasMany(
        WorkItemFile::class,
        'item_id'
    );
}
```

Pada WorkItemFile:

```php
public function item()
{
    return $this->belongsTo(
        WorkItem::class,
        'item_id'
    );
}
```

## Langkah 8: Seeder Role

```bash
php artisan make:seeder RoleSeeder
```

```php
<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::updateOrCreate(
            ['name' => 'Admin'],
            ['description' => 'Administrator']
        );

        Role::updateOrCreate(
            ['name' => 'Pimpinan'],
            ['description' => 'Pimpinan']
        );

        Role::updateOrCreate(
            ['name' => 'Personel'],
            ['description' => 'Personel']
        );
    }
}
```

## Langkah 9: Seeder Unit

```bash
php artisan make:seeder UnitSeeder
```

Contoh:

```php
Unit::updateOrCreate(
    ['code' => 'DITKUMAD'],
    [
        'name' => 'Direktorat Hukum TNI AD',
        'is_active' => true,
    ]
);
```

## Langkah 10: DatabaseSeeder

```php
$this->call([
    RoleSeeder::class,
    UnitSeeder::class,
]);
```

Jalankan:

```bash
php artisan db:seed
```

## Langkah 11: Uji Tinker

```bash
php artisan tinker
```

```php
App\Models\Role::pluck('name');

App\Models\Unit::all();

App\Models\User::with([
    'role',
    'unit',
])->first();
```

## Penjelasan untuk Pemula

Model adalah representasi tabel database di Laravel.

Contoh:

```text
Tabel users
        ↓
Model User
```

Dengan Model kita dapat menulis:

```php
User::all();
```

tanpa menulis SQL panjang.

### Apa itu Relasi Eloquent?

Relasi membuat kita dapat berpindah dari satu data ke data lain.

Contoh:

```php
$user->unit
```

artinya:

> Ambil unit milik user ini.

Contoh lain:

```php
$report->items
```

artinya:

> Ambil seluruh pekerjaan pada laporan ini.

### Apa itu Seeder?

Seeder digunakan untuk mengisi data awal.

Contoh:

```text
Admin
Pimpinan
Personel
```

Role tersebut harus tersedia sejak awal. Karena itu kita membuat RoleSeeder.

### Kenapa `updateOrCreate()`?

Karena seeder mungkin dijalankan lebih dari satu kali.

`updateOrCreate()` membantu mencegah data ganda.

## Penjelasan Gamblang: Model dan Relasi Ini Untuk Apa?

### `Model`
Model adalah penghubung Laravel dengan tabel database.

Contoh:

```php
User::all()
```

berarti ambil seluruh data dari tabel `users`.

### `$fillable`
Daftar kolom yang boleh diisi secara massal melalui `create()` atau `update()`.

### `casts()`
Mengubah tipe data database menjadi tipe PHP yang lebih tepat. Contoh `is_active` menjadi boolean.

### `belongsTo`
Artinya model ini memiliki satu induk.

Contoh:

```php
$user->role
```

User memiliki satu Role.

### `hasMany`
Artinya satu data memiliki banyak data lain.

Contoh:

```php
$report->items
```

Satu report punya banyak item.

### `hasOne`
Artinya satu data punya tepat satu data terkait.

Contoh:

```php
$member->attendance
```

Satu member jadwal memiliki satu attendance.

### `RoleSeeder`
Dipakai untuk membuat data role awal secara otomatis.

### `updateOrCreate`
Mencegah role ganda jika seeder dijalankan berkali-kali.

## Checklist

- [ ] Model dibuat
- [ ] Relasi utama dibuat
- [ ] Seeder role dibuat
- [ ] Seeder unit dibuat
- [ ] `db:seed` berhasil
- [ ] Tinker berhasil


## Simpan Checkpoint Git

Setelah modul berhasil:

```bash
git status
git add .
git commit -m "Selesaikan modul ini"
```

Tujuan checkpoint adalah agar project mudah dikembalikan bila modul berikutnya mengalami error.


## Modul Berikutnya

Modul 05 membuat login dan logout manual menggunakan `login_id`.
