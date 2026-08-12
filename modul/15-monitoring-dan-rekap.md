# Modul 15: Monitoring dan Rekap

## Tujuan

Membuat halaman monitoring WFH untuk Admin/Pimpinan.

## Fitur

- filter jadwal;
- filter unit;
- search;
- statistik;
- pagination;
- evidence;
- laporan.

## Langkah 1: Controller

```bash
php artisan make:controller MonitoringController
```

## Langkah 2: Filter

```php
$scheduleId = $request->integer(
    'schedule_id'
);

$unitId = $request->integer(
    'unit_id'
);

$search = trim(
    (string) $request->input('search')
);
```

## Langkah 3: Jadwal Terpilih

```php
$schedules = WfhSchedule::query()
    ->orderByDesc('wfh_date')
    ->get();

$selectedSchedule = null;

if ($scheduleId) {
    $selectedSchedule =
        $schedules->firstWhere(
            'id',
            $scheduleId
        );
}

$selectedSchedule ??=
    $schedules->firstWhere(
        'status',
        'active'
    );

$selectedSchedule ??=
    $schedules->first();
```

## Langkah 4: Query

```php
$membersQuery =
    WfhScheduleMember::query()
        ->with([
            'user.unit',
            'schedule',
            'attendance',
            'workReport'
                => fn ($query) =>
                    $query->withCount(
                        'items'
                    ),
        ])
        ->whereNull('cancelled_at');

if ($selectedSchedule) {
    $membersQuery->where(
        'schedule_id',
        $selectedSchedule->id
    );
}
```

## Langkah 5: Unit

Gunakan `whereHas('user')` untuk filter `unit_id`.

## Langkah 6: Search

Cari pada:

```text
name
login_id
rank
position
```

## Langkah 7: Summary

```php
$summaryMembers =
    (clone $membersQuery)->get();
```

Hitung:

```text
total
checked_in
not_checked_in
checked_out
not_checked_out
waiting_verification
needs_revision
approved
not_submitted
```

## Langkah 8: Pagination

```php
$members = $membersQuery
    ->paginate(25)
    ->withQueryString();
```

## Langkah 9: View

Kolom:

```text
No
Personel
Check-in
Check-out
Pekerjaan
Status Laporan
Tindakan
```

## Kesalahan Umum

Jangan menghitung summary dari paginator karena hanya mewakili halaman saat ini.

## Penjelasan untuk Pemula

Monitoring adalah halaman yang menggabungkan data dari banyak tabel.

Contohnya satu baris Personel dapat membutuhkan:

```text
User
Unit
Schedule Member
Attendance
Work Report
Work Items
```

Karena itu kita menggunakan:

```php
with(...)
```

atau **eager loading**.

### Apa itu Pagination?

Jika ada 500 Personel, kita tidak ingin menampilkan semuanya sekaligus.

```php
paginate(25)
```

artinya tampilkan 25 data per halaman.

### Kenapa Summary Dihitung Sebelum Pagination?

Karena summary harus mewakili semua data hasil filter, bukan hanya 25 data pada halaman saat ini.

## Penjelasan Gamblang: Monitoring Ini Untuk Apa?

### Monitoring
Menggabungkan data banyak tabel menjadi satu tampilan ringkas untuk Pimpinan/Admin.

### `with(...)`
Mengambil relasi sekaligus agar tidak terjadi terlalu banyak query.

### `schedule_id`
Filter jadwal.

### `unit_id`
Filter unit.

### `search`
Pencarian nama/login/pangkat/jabatan.

### `clone $membersQuery`
Membuat salinan query. Satu dipakai untuk summary, satu untuk pagination.

### Summary
Menghitung kondisi seluruh Personel hasil filter.

### Pagination
Membagi daftar menjadi beberapa halaman agar browser tidak memuat terlalu banyak baris.

### `withQueryString`
Mempertahankan filter ketika berpindah halaman.

## Checklist

- [ ] Schedule
- [ ] Unit
- [ ] Search
- [ ] Summary
- [ ] Pagination
- [ ] Eager load
- [ ] Admin/Pimpinan

## Modul Berikutnya

Modul 15A membuat Evidence Privat.
