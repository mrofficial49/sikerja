# Modul 10: Check-in Foto dan GPS

## Tujuan

Membuat check-in WFH dengan kamera dan lokasi browser.

## Hasil Akhir

Tabel `attendances` menyimpan:

```text
checkin_at
checkin_latitude
checkin_longitude
checkin_photo_path
checkin_late_reason
attendance_status
```

## Prasyarat

- Jadwal aktif.
- Personel ada di jadwal.
- Browser mengizinkan kamera dan lokasi.

## Langkah 1: Storage Privat

Buka `config/filesystems.php`:

```php
'local' => [
    'driver' => 'local',
    'root' => storage_path('app/private'),
    'serve' => false,
    'throw' => false,
    'report' => false,
],
```

Buat folder:

```bash
mkdir -p storage/app/private
```

## Langkah 2: Controller

```bash
php artisan make:controller Personnel/AttendanceController
```

## Langkah 3: Method Show

```php
public function show(
    Request $request
): View {
    $member = WfhScheduleMember::query()
        ->with([
            'schedule',
            'attendance',
            'workReport',
        ])
        ->where(
            'user_id',
            $request->user()->id
        )
        ->whereNull('cancelled_at')
        ->whereHas(
            'schedule',
            fn ($query) =>
                $query->where(
                    'status',
                    'active'
                )
        )
        ->first();

    return view(
        'personnel.attendance.show',
        compact('member')
    );
}
```

## Langkah 4: Method Store

```php
public function store(
    Request $request
): RedirectResponse {
    $member = WfhScheduleMember::query()
        ->with([
            'schedule',
            'attendance',
        ])
        ->where(
            'user_id',
            $request->user()->id
        )
        ->whereNull('cancelled_at')
        ->whereHas(
            'schedule',
            fn ($query) =>
                $query->where(
                    'status',
                    'active'
                )
        )
        ->firstOrFail();

    if ($member->attendance?->checkin_at) {
        return back()->with(
            'error',
            'Anda sudah check-in.'
        );
    }

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
        'late_reason' => [
            'nullable',
            'string',
            'max:500',
        ],
    ]);

    $photo = $request->file('photo');

    $filename = sprintf(
        'checkin_%d_%s.%s',
        $request->user()->id,
        now()->format('Ymd_His'),
        $photo->getClientOriginalExtension()
    );

    $path = $photo->storeAs(
        'attendance/checkin/'
            . $member->schedule_id,
        $filename,
        'local'
    );

    DB::transaction(
        function () use (
            $member,
            $data,
            $path
        ) {
            Attendance::updateOrCreate(
                [
                    'schedule_member_id'
                        => $member->id,
                ],
                [
                    'checkin_at' => now(),
                    'checkin_latitude'
                        => $data['latitude'],
                    'checkin_longitude'
                        => $data['longitude'],
                    'checkin_photo_path'
                        => $path,
                    'checkin_late_reason'
                        => $data['late_reason']
                            ?? null,
                    'attendance_status'
                        => 'incomplete',
                ]
            );

            $member->update([
                'member_status' => 'present',
            ]);

            WorkReport::firstOrCreate(
                [
                    'schedule_member_id'
                        => $member->id,
                ],
                [
                    'status' => 'draft',
                ]
            );
        }
    );

    return back()->with(
        'success',
        'Check-in berhasil.'
    );
}
```

## Langkah 5: View Kamera

```blade
<video
    id="camera"
    autoplay
    playsinline
    class="w-100 rounded"
></video>

<canvas id="canvas" hidden></canvas>

<input
    type="hidden"
    name="latitude"
    id="latitude"
>

<input
    type="hidden"
    name="longitude"
    id="longitude"
>
```

## Langkah 6: JavaScript Kamera

```javascript
const stream =
    await navigator.mediaDevices
        .getUserMedia({
            video: {
                facingMode: 'user',
            },
            audio: false,
        });

document.getElementById(
    'camera'
).srcObject = stream;
```

## Langkah 7: GPS

```javascript
navigator.geolocation.getCurrentPosition(
    (position) => {
        document.getElementById(
            'latitude'
        ).value =
            position.coords.latitude;

        document.getElementById(
            'longitude'
        ).value =
            position.coords.longitude;
    },
    () => {
        alert(
            'Lokasi tidak dapat diperoleh.'
        );
    },
    {
        enableHighAccuracy: true,
        timeout: 15000,
    }
);
```

## Langkah 8: Aturan Waktu

Contoh kebijakan:

```text
07.00-07.10 = tepat waktu
setelah 07.10 = terlambat
```

Server harus mewajibkan alasan jika check-in lewat batas.

## Langkah 9: Route

Pada group Personel:

```php
Route::get('/presensi', [
    AttendanceController::class,
    'show',
])->name('attendance.show');

Route::post('/presensi/check-in', [
    AttendanceController::class,
    'store',
])->name('attendance.checkin');
```

## Pengujian Manual

1. Login Personel.
2. Buka Presensi.
3. Izinkan kamera.
4. Izinkan lokasi.
5. Capture foto.
6. Check-in.
7. Refresh.
8. Check-in kedua harus ditolak.
9. Periksa database/Tinker.

## Kesalahan Umum

- camera permission denied: cek browser.
- GPS timeout: aktifkan Location Services.
- jangan gunakan `attendance_status=complete`.
- foto belum dapat dibuka langsung karena memang privat.

## Penjelasan untuk Pemula

Pada modul ini frontend dan backend bekerja bersama.

### Frontend Bertugas

- membuka kamera;
- mengambil foto;
- meminta koordinat GPS;
- mengirim form.

### Backend Bertugas

- memastikan user memang terjadwal;
- memvalidasi foto;
- memvalidasi koordinat;
- menyimpan file;
- menyimpan database.

### Kenapa Validasi Harus Tetap di Server?

JavaScript di browser dapat dimanipulasi pengguna.

Jadi meskipun frontend sudah memeriksa data, backend tetap wajib melakukan validasi.

### Kenapa Foto Disimpan Privat?

Foto presensi bukan file publik.

Kalau disimpan sembarangan di folder publik, siapa pun yang mengetahui URL dapat mencoba membukanya.

Karena itu foto disimpan di:

```text
storage/app/private
```

dan nanti diakses melalui controller yang memeriksa izin.

## Penjelasan Gamblang: Check-in Foto dan GPS Ini Untuk Apa?

### `navigator.mediaDevices.getUserMedia()`
Meminta akses kamera dari browser.

### `navigator.geolocation.getCurrentPosition()`
Meminta koordinat lokasi perangkat.

### Hidden input latitude/longitude
Digunakan untuk membawa nilai GPS dari JavaScript ke form Laravel.

### `$request->file('photo')`
Mengambil file foto yang dikirim browser.

### `storeAs(..., 'local')`
Menyimpan foto ke storage privat dengan nama file yang kita tentukan.

### Kenapa storage `local` privat?
Agar foto tidak bisa dibuka langsung melalui URL publik.

### `DB::transaction()`
Memastikan proses penyimpanan terkait berjalan sebagai satu paket. Jika salah satu gagal, perubahan database dapat dibatalkan.

### `Attendance::updateOrCreate()`
Membuat attendance jika belum ada atau memperbarui baris yang sudah ada.

### `WorkReport::firstOrCreate()`
Membuat draft laporan otomatis setelah check-in.

### Kenapa validasi GPS dilakukan server?
Karena JavaScript di browser dapat dimodifikasi. Backend tetap harus memeriksa data.

## Checklist

- [ ] Private storage
- [ ] Kamera
- [ ] GPS
- [ ] Foto
- [ ] Check-in sekali
- [ ] Member present
- [ ] WorkReport draft

## Modul Berikutnya

Modul 10A membuat Check-out.
