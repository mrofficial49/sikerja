# Modul 10A: Check-out Foto dan GPS

## Tujuan

Membuat check-out setelah Personel mengirim laporan.

## Prasyarat Check-out

- sudah check-in;
- belum pernah check-out;
- laporan tidak lagi berstatus `draft`.

## Langkah 1: Controller

```bash
php artisan make:controller Personnel/CheckoutController
```

## Langkah 2: Ambil Data

Load:

```php
[
    'schedule',
    'attendance',
    'workReport',
]
```

## Langkah 3: Validasi

```php
$attendance = $member->attendance;

abort_unless(
    $attendance?->checkin_at,
    422,
    'Anda belum check-in.'
);

abort_if(
    filled($attendance->checkout_at),
    422,
    'Anda sudah check-out.'
);

abort_if(
    $member->workReport?->status
        === 'draft',
    422,
    'Laporan belum dikirim.'
);
```

## Langkah 4: Input Foto/GPS

```php
$data = $request->validate([
    'latitude' => [
        'required',
        'numeric',
        'between:-90,90',
    ],
    'longitude' => [
        'required',
        'numeric',
        'between:-180,180',
    ],
    'photo' => [
        'required',
        'image',
        'max:5120',
    ],
]);
```

## Langkah 5: Simpan

Folder:

```text
attendance/checkout/{schedule_id}
```

Update:

```php
$attendance->update([
    'checkout_at' => now(),
    'checkout_latitude'
        => $data['latitude'],
    'checkout_longitude'
        => $data['longitude'],
    'checkout_photo_path'
        => $path,
    'attendance_status'
        => 'present',
]);
```

## Langkah 6: Route

```php
Route::get(
    '/presensi/check-out',
    [
        CheckoutController::class,
        'show',
    ]
)->name('attendance.checkout.show');

Route::post(
    '/presensi/check-out',
    [
        CheckoutController::class,
        'store',
    ]
)->name('attendance.checkout.store');
```

## Pengujian

1. Belum check-in → gagal.
2. Report draft → gagal.
3. Submit report.
4. Check-out → berhasil.
5. Check-out kedua → gagal.

## Penjelasan untuk Pemula

Check-out hampir sama dengan check-in, tetapi memiliki syarat tambahan.

Sistem harus memastikan:

```text
Sudah check-in?
Sudah mengirim laporan?
Sudah pernah check-out?
```

Baru setelah semua syarat benar, check-out disimpan.

Ini disebut **business rule** atau aturan bisnis.

Aturan bisnis penting karena website bukan hanya menyimpan data, tetapi juga memastikan proses berjalan sesuai aturan organisasi.

## Penjelasan Gamblang: Check-out Ini Untuk Apa?

### Kenapa harus sudah check-in?
Karena tidak masuk akal seseorang check-out tanpa check-in.

### Kenapa laporan harus sudah dikirim?
Karena proses kerja hari itu harus terdokumentasi sebelum pengguna menutup aktivitas.

### `checkout_at`
Mencatat waktu keluar.

### `checkout_latitude` dan `checkout_longitude`
Mencatat lokasi saat check-out.

### `checkout_photo_path`
Mencatat lokasi file foto check-out.

### `attendance_status = present`
Menandai presensi selesai dan sah sebagai hadir.

### Kenapa check-out kedua ditolak?
Agar satu hari hanya memiliki satu waktu check-out yang konsisten.

## Checklist

- [ ] Check-in prerequisite
- [ ] Report prerequisite
- [ ] Kamera
- [ ] GPS
- [ ] Checkout
- [ ] Status present

## Modul Berikutnya

Modul 11 membuat Rencana Kerja Pribadi.
