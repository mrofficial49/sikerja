# Modul 14A: Verifikasi dan Revisi Laporan

## Tujuan

Membuat Admin/Pimpinan menilai laporan Personel.

## Workflow

```text
waiting_verification
        ↓
approved
atau
needs_revision
        ↓
Personel memperbaiki
        ↓
waiting_verification
```

## Langkah 1: Controller

```bash
php artisan make:controller ReportReviewController
```

## Langkah 2: Index

```php
$reports = WorkReport::query()
    ->with([
        'scheduleMember.user.unit',
        'scheduleMember.schedule',
    ])
    ->whereIn('status', [
        'waiting_verification',
        'needs_revision',
        'approved',
    ])
    ->orderByDesc('submitted_at')
    ->paginate(25);
```

## Langkah 3: Approve

```php
$data = $request->validate([
    'verification_note' => [
        'nullable',
        'string',
    ],
]);

$report->update([
    'status' => 'approved',
    'verification_note'
        => $data['verification_note'] ?? null,
    'verified_at' => now(),
    'verified_by'
        => $request->user()->id,
]);
```

## Langkah 4: Revisi

```php
$data = $request->validate([
    'verification_note' => [
        'required',
        'string',
    ],
]);

$report->update([
    'status' => 'needs_revision',
    'verification_note'
        => $data['verification_note'],
    'verified_at' => now(),
    'verified_by'
        => $request->user()->id,
]);
```

## Langkah 5: Resubmit

```php
$report->update([
    'status'
        => 'waiting_verification',
    'submitted_at' => now(),
]);
```

## Hak Akses

Admin dan Pimpinan boleh review. Personel tidak boleh membuka route review.

## Pengujian

1. Approve.
2. Revisi.
3. Revisi tanpa note → gagal.
4. Personel melihat note.
5. Resubmit.
6. Approve.

## Penjelasan untuk Pemula

Verifikasi adalah workflow atau alur status.

Pimpinan/Admin tidak mengubah pekerjaan Personel secara langsung.

Mereka hanya memberikan keputusan:

```text
Approved
```

atau:

```text
Needs Revision
```

Jika revisi:

```text
Reviewer memberi catatan
        ↓
Personel membaca
        ↓
Personel memperbaiki
        ↓
Submit ulang
```

Workflow seperti ini banyak digunakan pada aplikasi administrasi.

## Penjelasan Gamblang: Verifikasi dan Revisi Ini Untuk Apa?

### `approved`
Reviewer menyatakan laporan diterima.

### `needs_revision`
Reviewer meminta perbaikan.

### `verification_note`
Catatan reviewer agar Personel tahu apa yang harus diperbaiki.

### `verified_at`
Waktu verifikasi.

### `verified_by`
Siapa yang memverifikasi.

### Resubmit
Mengubah laporan kembali ke `waiting_verification` setelah Personel selesai memperbaiki.

### Kenapa reviewer tidak mengubah pekerjaan langsung?
Karena pemilik laporan tetap Personel. Reviewer hanya memberi keputusan dan catatan.

## Checklist

- [ ] Index
- [ ] Detail
- [ ] Approve
- [ ] Revisi
- [ ] Note
- [ ] Resubmit
- [ ] Role protection

## Modul Berikutnya

Modul 15 membuat Monitoring.
