# Modul 09: Jadwal WFH dan Anggota Jadwal

## Tujuan

Membuat Admin dapat membuat jadwal WFH dan menentukan Personel peserta.

## Hasil Akhir

Admin dapat:

- membuat jadwal draft;
- menambahkan beberapa Personel;
- membatalkan Personel;
- mengaktifkan jadwal;
- menyelesaikan jadwal.

## Struktur File

```text
app/Http/Controllers/Admin/WfhScheduleController.php
app/Http/Controllers/Admin/WfhScheduleMemberController.php
resources/views/admin/wfh-schedules/
routes/web.php
```

## Langkah 1: Controller Jadwal

```bash
php artisan make:controller Admin/WfhScheduleController
```

## Langkah 2: Index

```php
public function index(): View
{
    $schedules = WfhSchedule::query()
        ->withCount([
            'members as members_count'
                => fn ($query) =>
                    $query->whereNull(
                        'cancelled_at'
                    ),
        ])
        ->orderByDesc('wfh_date')
        ->paginate(20);

    return view(
        'admin.wfh-schedules.index',
        compact('schedules')
    );
}
```

## Langkah 3: Store Jadwal

```php
$data = $request->validate([
    'wfh_date' => [
        'required',
        'date',
    ],
    'title' => [
        'nullable',
        'string',
        'max:150',
    ],
]);

WfhSchedule::create([
    'wfh_date' => $data['wfh_date'],
    'title' => $data['title'] ?? null,
    'status' => 'draft',
]);
```

## Langkah 4: Halaman Detail

```php
$schedule->load([
    'members.user.unit',
]);

$personnel = User::query()
    ->with('unit')
    ->where('is_active', true)
    ->whereHas(
        'role',
        fn ($query) =>
            $query->where(
                'name',
                'Personel'
            )
    )
    ->orderBy('name')
    ->get();
```

## Langkah 5: Controller Member

```bash
php artisan make:controller Admin/WfhScheduleMemberController
```

## Langkah 6: Tambah Banyak Personel

```php
$data = $request->validate([
    'user_ids' => [
        'required',
        'array',
        'min:1',
    ],
    'user_ids.*' => [
        'required',
        'exists:users,id',
    ],
]);

foreach ($data['user_ids'] as $userId) {
    WfhScheduleMember::firstOrCreate([
        'schedule_id' => $schedule->id,
        'user_id' => $userId,
    ], [
        'member_status' => 'scheduled',
    ]);
}
```

`firstOrCreate()` mencegah data anggota ganda.

## Langkah 7: Cancel Member

```php
$member->update([
    'member_status' => 'cancelled',
    'cancelled_at' => now(),
]);
```

Jangan delete baris agar histori perubahan tetap tersedia.

## Langkah 8: Aktifkan Jadwal

```php
$count = $schedule->members()
    ->whereNull('cancelled_at')
    ->count();

if ($count === 0) {
    return back()->with(
        'error',
        'Tambahkan Personel terlebih dahulu.'
    );
}

$schedule->update([
    'status' => 'active',
]);
```

## Langkah 9: Selesaikan Jadwal

```php
$schedule->update([
    'status' => 'completed',
]);
```

## Status

Jadwal:

```text
draft
active
completed
cancelled
```

Member:

```text
scheduled
cancelled
schedule_change
present
absent
```

## Pengujian Manual

1. Buat jadwal draft.
2. Tambah 3 Personel.
3. Coba tambah Personel yang sama.
4. Cancel satu.
5. Aktifkan.
6. Pastikan hanya member aktif dianggap peserta.
7. Selesaikan jadwal latihan.

## Kesalahan Umum

- anggota duplikat: pastikan unique/`firstOrCreate`.
- jadwal aktif tanpa member: validasi sebelum aktivasi.
- Personel tidak tampil: pastikan role `Personel` dan `is_active=true`.

## Penjelasan untuk Pemula

Jadwal WFH terdiri dari dua bagian.

### 1. WfhSchedule

Menyimpan informasi jadwal.

Contoh:

```text
Tanggal: 14 Agustus 2026
Status : Active
```

### 2. WfhScheduleMember

Menyimpan siapa saja yang mengikuti jadwal tersebut.

Kenapa tidak langsung menaruh `user_id` di tabel jadwal?

Karena satu jadwal memiliki banyak Personel.

Hubungannya:

```text
1 Jadwal
   ↓
Banyak Anggota
```

### `firstOrCreate()`

Digunakan agar Personel yang sama tidak ditambahkan dua kali ke jadwal yang sama.

## Penjelasan Gamblang: Jadwal dan Anggota Ini Untuk Apa?

### `wfh_schedules`
Menyimpan informasi satu jadwal WFH.

### `wfh_schedule_members`
Menyimpan siapa saja yang ikut pada jadwal tertentu.

### Kenapa dua tabel?
Karena satu jadwal dapat memiliki banyak Personel.

### `firstOrCreate`
Mencegah member yang sama ditambahkan dua kali.

### `member_status`
Menjelaskan kondisi Personel dalam jadwal.

### `cancelled_at`
Mencatat kapan keikutsertaan dibatalkan.

### Kenapa member dibatalkan, bukan dihapus?
Supaya histori bahwa Personel pernah dijadwalkan masih dapat diketahui.

### `active`
Menandai jadwal sedang dipakai untuk operasional.

### `completed`
Menandai jadwal sudah selesai.

## Checklist

- [ ] Jadwal dibuat
- [ ] Member ditambah
- [ ] Tidak duplikat
- [ ] Cancel
- [ ] Activate
- [ ] Complete

## Modul Berikutnya

Modul 10 membuat Check-in Foto dan GPS.
