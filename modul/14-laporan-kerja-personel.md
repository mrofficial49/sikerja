# Modul 14: Laporan Kerja Personel

## Tujuan

Membuat ringkasan pekerjaan dan proses submit laporan.

## Hasil Akhir

```text
draft -> waiting_verification
```

## Langkah 1: Controller

```bash
php artisan make:controller Personnel/WorkReportController
```

## Langkah 2: Show

```php
$report = WorkReport::query()
    ->with([
        'scheduleMember.schedule',
        'scheduleMember.user.unit',
        'items.files',
    ])
    ->whereHas(
        'scheduleMember',
        fn ($query) =>
            $query->where(
                'user_id',
                $request->user()->id
            )
    )
    ->latest()
    ->firstOrFail();
```

## Langkah 3: Validasi Submit

```php
$items = $report->items()
    ->where(
        'status',
        '!=',
        'cancelled'
    )
    ->get();

if ($items->isEmpty()) {
    return back()->with(
        'error',
        'Belum ada pekerjaan.'
    );
}
```

## Langkah 4: Status yang Boleh Submit

```php
abort_unless(
    in_array(
        $report->status,
        [
            'draft',
            'needs_revision',
        ],
        true
    ),
    422
);
```

## Langkah 5: Submit

```php
$report->update([
    'status'
        => 'waiting_verification',
    'submitted_at' => now(),
]);
```

## Langkah 6: View

Tampilkan:

```text
Tanggal
Personel
Unit
Pekerjaan
Sumber
Progress
Target
Kendala
Bukti
Status Laporan
Catatan Verifikasi
```

## Aturan

Check-out tidak menunggu approval, tetapi laporan harus sudah dikirim.

## Pengujian

1. Report kosong.
2. Tambah item.
3. Submit.
4. Status waiting.
5. Check-out tersedia.

## Penjelasan untuk Pemula

WorkReport berfungsi seperti sampul laporan.

Isi detail pekerjaannya berada di WorkItem.

Contoh:

```text
WORK REPORT
Nama: Dwi
Tanggal: Jumat
Status: waiting_verification

Isi:
1. Pekerjaan A
2. Pekerjaan B
3. Pekerjaan C
```

Saat tombol Kirim Laporan ditekan:

```text
draft
  ↓
waiting_verification
```

Artinya Personel sudah selesai mengirim dan menunggu pemeriksaan.

## Penjelasan Gamblang: Laporan Kerja Ini Untuk Apa?

### `draft`
Laporan masih disusun.

### `waiting_verification`
Laporan sudah dikirim dan menunggu reviewer.

### `submitted_at`
Mencatat kapan laporan dikirim.

### Kenapa item cancelled tidak dihitung?
Karena pekerjaan yang sudah dibatalkan tidak perlu dianggap pekerjaan aktif.

### Kenapa setelah submit edit dibatasi?
Supaya isi laporan yang sedang dinilai tidak berubah diam-diam.

### Kenapa check-out tidak menunggu approved?
Karena presensi selesai pada hari itu, sedangkan proses verifikasi dapat dilakukan setelahnya.

## Checklist

- [ ] Show
- [ ] Items
- [ ] Validation
- [ ] Submit
- [ ] waiting_verification
- [ ] Ownership

## Modul Berikutnya

Modul 14A membuat Verifikasi/Revisi.
