# Modul 16: Pusat Notifikasi

## Tujuan

Membuat sistem notifikasi internal agar pengguna mengetahui kejadian penting tanpa harus memeriksa setiap menu.

Contoh notifikasi:

- tugas baru dari Pimpinan;
- laporan perlu revisi;
- laporan disetujui;
- jadwal WFH baru.

## Hasil Akhir

Pengguna memiliki:

- badge jumlah notifikasi belum dibaca;
- halaman daftar notifikasi;
- tombol buka notifikasi;
- status `read_at`.

## Struktur File

```text
app/Models/AppNotification.php
app/Http/Controllers/NotificationController.php
resources/views/notifications/index.blade.php
routes/web.php
resources/views/layouts/app.blade.php
```

## Langkah 1: Model AppNotification

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'url',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function scopeUnread($query)
    {
        return $query->whereNull(
            'read_at'
        );
    }
}
```

## Langkah 2: Relasi pada User

```php
public function appNotifications()
{
    return $this->hasMany(
        AppNotification::class,
        'user_id'
    );
}
```

## Langkah 3: Buat Controller

```bash
php artisan make:controller NotificationController
```

## Langkah 4: Index

```php
public function index(
    Request $request
): View {
    $notifications = $request
        ->user()
        ->appNotifications()
        ->latest()
        ->paginate(30);

    return view(
        'notifications.index',
        compact('notifications')
    );
}
```

## Langkah 5: Buka Notifikasi

```php
public function open(
    Request $request,
    AppNotification $notification
): RedirectResponse {
    abort_unless(
        $notification->user_id
            === $request->user()->id,
        403
    );

    if (is_null($notification->read_at)) {
        $notification->update([
            'read_at' => now(),
        ]);
    }

    if (filled($notification->url)) {
        return redirect(
            $notification->url
        );
    }

    return redirect()
        ->route(
            'notifications.index'
        );
}
```

## Langkah 6: Route

```php
Route::middleware([
    'auth',
    'active',
    EnsurePasswordIsChanged::class,
])->group(function () {
    Route::get('/notifikasi', [
        NotificationController::class,
        'index',
    ])->name('notifications.index');

    Route::get(
        '/notifikasi/{notification}/buka',
        [
            NotificationController::class,
            'open',
        ]
    )->name('notifications.open');
});
```

## Langkah 7: Buat Notifikasi Tugas

Setelah Pimpinan membuat WorkItem `leader_task`:

```php
AppNotification::create([
    'user_id' => $member->user_id,
    'type' => 'leader_task',
    'title' => 'Tugas Baru',
    'message'
        => 'Anda menerima tugas baru dari Pimpinan.',
    'url' => route(
        'personnel.work-items.index'
    ),
]);
```

## Langkah 8: Buat Notifikasi Revisi

```php
AppNotification::create([
    'user_id'
        => $report
            ->scheduleMember
            ->user_id,
    'type' => 'report_revision',
    'title'
        => 'Laporan Perlu Revisi',
    'message'
        => $report->verification_note,
    'url' => route(
        'personnel.report.show'
    ),
]);
```

## Langkah 9: Notifikasi Approved

```php
AppNotification::create([
    'user_id'
        => $report
            ->scheduleMember
            ->user_id,
    'type' => 'report_approved',
    'title'
        => 'Laporan Disetujui',
    'message'
        => 'Laporan kerja Anda telah disetujui.',
    'url' => route(
        'personnel.report.show'
    ),
]);
```

## Langkah 10: Badge Unread

Untuk pemula, pada layout dapat dihitung sederhana:

```php
$unreadCount = auth()
    ->user()
    ->appNotifications()
    ->unread()
    ->count();
```

Blade:

```blade
<a
    href="{{ route('notifications.index') }}"
    class="nav-link"
>
    Notifikasi

    @if ($unreadCount > 0)
        <span class="badge bg-danger">
            {{ $unreadCount }}
        </span>
    @endif
</a>
```

Pada project yang lebih besar, data ini dapat dipindahkan ke View Composer.

## Langkah 11: View Daftar

Tampilkan:

```text
Judul
Pesan
Waktu
Status Dibaca
Tombol Buka
```

## Pengujian Manual

1. Login Pimpinan.
2. Buat tugas Personel A.
3. Login Personel A.
4. Badge harus bertambah.
5. Buka notifikasi.
6. Badge berkurang.
7. Login Personel B.
8. Coba membuka URL notifikasi A → 403.
9. Buat revisi laporan.
10. Pastikan notifikasi revisi muncul.

## Kesalahan Umum

### Badge tidak turun

`read_at` belum diperbarui.

### User lain dapat membuka

Controller belum memeriksa `notification->user_id`.

### Riwayat tetap muncul setelah reset demo

Pastikan tabel yang dipakai benar-benar dikosongkan. Periksa:

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
        dump(
            \$table,
            \Illuminate\Support\Facades\DB
                ::table(\$table)
                ->count()
        );
    }
}
"
```

## Penjelasan untuk Pemula

Notifikasi adalah data biasa yang disimpan di database.

Contoh:

```text
user_id = 10
title = Tugas Baru
read_at = null
```

`read_at = null` berarti belum dibaca.

Setelah pengguna membuka notifikasi:

```text
read_at = 2026-08-11 20:00:00
```

berarti sudah dibaca.

### Badge Notifikasi

Badge hanya menghitung data:

```text
read_at IS NULL
```

yang memang milik user yang sedang login.

## Penjelasan Gamblang: Notifikasi Ini Untuk Apa?

### `app_notifications`
Tabel penyimpanan notifikasi aplikasi.

### `user_id`
Menentukan penerima notifikasi.

### `type`
Jenis notifikasi, misalnya `leader_task` atau `report_revision`.

### `title`
Judul singkat.

### `message`
Isi pesan.

### `url`
Halaman tujuan ketika notifikasi diklik.

### `read_at = null`
Artinya belum dibaca.

### `read_at` terisi
Artinya sudah dibaca.

### `scopeUnread`
Mempermudah query untuk mengambil notifikasi yang belum dibaca.

### Kenapa ownership diperiksa?
Supaya user tidak dapat membuka notifikasi milik orang lain hanya dengan menebak ID.

## Checklist

- [ ] Model
- [ ] Relasi User
- [ ] Index
- [ ] Open
- [ ] Ownership
- [ ] Unread badge
- [ ] Task notification
- [ ] Revision notification
- [ ] Approved notification

## Simpan Checkpoint Git

```bash
git add .
git commit -m "Tambah pusat notifikasi SIKERJA"
```

## Modul Berikutnya

Modul 17 membuat Dashboard Admin, Pimpinan, dan Personel.
