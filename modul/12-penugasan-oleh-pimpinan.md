# Modul 12: Penugasan oleh Pimpinan

## Tujuan

Membuat Pimpinan memberikan tugas kepada Personel terjadwal.

## Hasil Akhir

```text
source_type = leader_task
assigned_at terisi
```

## Langkah 1: Controller

```bash
php artisan make:controller Leader/LeaderTaskController
```

## Langkah 2: Ambil Anggota Jadwal

```php
$members = WfhScheduleMember::query()
    ->with([
        'user.unit',
        'workReport',
    ])
    ->where(
        'schedule_id',
        $schedule->id
    )
    ->whereNull('cancelled_at')
    ->get();
```

## Langkah 3: Validasi

```php
$data = $request->validate([
    'schedule_member_id' => [
        'required',
        'exists:wfh_schedule_members,id',
    ],
    'title' => [
        'required',
        'string',
        'max:200',
    ],
    'description' => [
        'nullable',
        'string',
    ],
    'target_result' => [
        'nullable',
        'string',
    ],
]);
```

## Langkah 4: Report

```php
$member = WfhScheduleMember::query()
    ->whereKey(
        $data['schedule_member_id']
    )
    ->whereNull('cancelled_at')
    ->firstOrFail();

$report = WorkReport::firstOrCreate(
    [
        'schedule_member_id'
            => $member->id,
    ],
    [
        'status' => 'draft',
    ]
);
```

## Langkah 5: Store

```php
WorkItem::create([
    'report_id' => $report->id,
    'created_by'
        => $request->user()->id,
    'source_type'
        => 'leader_task',
    'title' => $data['title'],
    'description'
        => $data['description']
            ?? null,
    'target_result'
        => $data['target_result']
            ?? null,
    'status' => 'not_started',
    'progress' => 0,
    'assigned_at' => now(),
]);
```

## Langkah 6: Cancel

```php
$workItem->update([
    'status' => 'cancelled',
    'cancelled_by'
        => $request->user()->id,
    'cancelled_at' => now(),
]);
```

Jangan delete agar histori tetap tersimpan.

## Pengujian

1. Pimpinan buat tugas ke Personel A.
2. Login A → tugas tampil.
3. Login B → tidak tampil.
4. Cancel tugas.
5. Histori masih ada.

## Penjelasan untuk Pemula

Modul ini menunjukkan hubungan antar-role.

Pimpinan membuat tugas, tetapi data tugas akan terlihat oleh Personel target.

Alurnya:

```text
Pimpinan
    ↓
Pilih Personel
    ↓
Buat WorkItem
source_type = leader_task
    ↓
WorkItem masuk ke WorkReport Personel
    ↓
Personel melihat tugas
```

### Kenapa Tugas yang Dibatalkan Tidak Dihapus?

Karena sistem sebaiknya memiliki jejak histori.

Daripada delete:

```text
status = cancelled
cancelled_by
cancelled_at
```

lebih baik untuk audit.

## Penjelasan Gamblang: Tugas Pimpinan Ini Untuk Apa?

### `leader_task`
Menandai pekerjaan berasal dari Pimpinan, bukan rencana pribadi.

### `created_by`
Menyimpan siapa yang membuat tugas.

### `assigned_at`
Mencatat kapan tugas diberikan.

### Kenapa tugas dimasukkan ke WorkReport Personel?
Supaya seluruh pekerjaan pada hari itu, baik pribadi maupun dari Pimpinan, terkumpul pada satu laporan.

### `cancelled_by`
Mencatat siapa yang membatalkan tugas.

### `cancelled_at`
Mencatat kapan dibatalkan.

### Kenapa tidak delete?
Agar jejak penugasan tetap dapat dilihat.

## Checklist

- [ ] Pimpinan route
- [ ] Member
- [ ] Report
- [ ] leader_task
- [ ] assigned_at
- [ ] Cancel history

## Modul Berikutnya

Modul 13 membuat Progress dan Bukti.
