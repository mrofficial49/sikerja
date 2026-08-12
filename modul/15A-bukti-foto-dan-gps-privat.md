# Modul 15A: Bukti Foto dan GPS Privat

## Tujuan

Menampilkan foto check-in/check-out dan GPS secara aman.

## Hak Akses

```text
Admin 200
Pimpinan 200
Owner 200
Personel lain 403
Guest redirect login
File hilang 404
```

## Langkah 1: Controller

```bash
php artisan make:controller AttendanceEvidenceController
```

## Langkah 2: Authorization

```php
private function authorizeAttendance(
    Request $request,
    Attendance $attendance
): void {
    $attendance->loadMissing(
        'scheduleMember'
    );

    $role = $request
        ->user()
        ->role
        ?->name;

    if (
        in_array(
            $role,
            ['Admin', 'Pimpinan'],
            true
        )
    ) {
        return;
    }

    abort_unless(
        $attendance
            ->scheduleMember
            ?->user_id
        === $request->user()->id,
        403
    );
}
```

## Langkah 3: Show

Load:

```php
[
    'scheduleMember.user.unit',
    'scheduleMember.schedule',
]
```

lalu authorize.

## Langkah 4: Stream Foto

```php
abort_unless(
    in_array(
        $type,
        ['checkin', 'checkout'],
        true
    ),
    404
);

$column =
    $type . '_photo_path';

$path = $attendance->{$column};

abort_if(blank($path), 404);

abort_unless(
    Storage::disk('local')
        ->exists($path),
    404
);

return Storage::disk('local')
    ->response(
        $path,
        null,
        [
            'Cache-Control'
                => 'private, no-store',
        ]
    );
```

## Langkah 5: Route

```php
Route::middleware([
    'auth',
    'active',
    EnsurePasswordIsChanged::class,
])
    ->prefix('bukti-presensi')
    ->name('attendance.evidence.')
    ->group(function () {
        Route::get('/{attendance}', [
            AttendanceEvidenceController::class,
            'show',
        ])->name('show');

        Route::get(
            '/{attendance}/foto/{type}',
            [
                AttendanceEvidenceController::class,
                'photo',
            ]
        )
            ->where(
                'type',
                'checkin|checkout'
            )
            ->name('photo');
    });
```

## Langkah 6: Maps

```blade
<a
    href="{{ 'https://www.google.com/maps?q='
        . $attendance->checkin_latitude
        . ','
        . $attendance->checkin_longitude }}"
    target="_blank"
    rel="noopener"
>
    Buka Google Maps
</a>
```

## Pengujian

Uji seluruh skenario hak akses.

## Penjelasan untuk Pemula

File privat tidak boleh dibuka langsung melalui URL.

Alurnya harus:

```text
User meminta foto
        ↓
Controller menerima request
        ↓
Controller memeriksa role/ownership
        ↓
Kalau boleh → file dikirim
Kalau tidak → 403
```

### 403 dan 404

```text
403 = file/data ada, tetapi Anda tidak berhak
404 = file/data tidak ditemukan
```

Ini penting untuk keamanan aplikasi.

## Penjelasan Gamblang: Evidence Privat Ini Untuk Apa?

### Kenapa file tidak ditampilkan langsung?
Karena foto presensi bersifat sensitif.

### `authorizeAttendance()`
Memeriksa apakah pengguna berhak melihat attendance.

### `Storage::disk('local')->exists($path)`
Memastikan file benar-benar tersedia.

### `Storage::disk('local')->response(...)`
Mengirim file ke browser setelah izin diperiksa.

### `Cache-Control: private, no-store`
Meminta browser/proxy agar tidak menyimpan salinan file sensitif secara permanen.

### 403
Data ada, tetapi pengguna tidak punya izin.

### 404
Data/file tidak ditemukan.

### Google Maps URL
Mengubah koordinat latitude/longitude menjadi tautan peta yang mudah dibuka.

## Checklist

- [ ] Private storage
- [ ] Authorization
- [ ] Show
- [ ] Photo checkin
- [ ] Photo checkout
- [ ] GPS
- [ ] 403
- [ ] 404

## Modul Berikutnya

Modul 15B membuat Export PDF.
