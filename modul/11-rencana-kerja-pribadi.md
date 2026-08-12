# Modul 11: Rencana Kerja Pribadi

## Tujuan

Membuat Personel menambahkan pekerjaan sendiri ke WorkReport hari WFH.

## Hasil Akhir

```text
source_type = personal_plan
status = not_started
progress = 0
```

## Langkah 1: Controller

```bash
php artisan make:controller Personnel/WorkItemController
```

## Langkah 2: Ambil Current Report

```php
private function currentReport(
    Request $request
): WorkReport {
    return WorkReport::query()
        ->whereHas(
            'scheduleMember',
            fn ($query) =>
                $query->where(
                    'user_id',
                    $request->user()->id
                )
        )
        ->whereHas(
            'scheduleMember.schedule',
            fn ($query) =>
                $query->where(
                    'status',
                    'active'
                )
        )
        ->firstOrFail();
}
```

## Langkah 3: Index

```php
$report = $this->currentReport(
    $request
);

$report->load([
    'items.files',
]);
```

## Langkah 4: Store

```php
$data = $request->validate([
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

WorkItem::create([
    'report_id' => $report->id,
    'created_by'
        => $request->user()->id,
    'source_type'
        => 'personal_plan',
    'title' => $data['title'],
    'description'
        => $data['description']
            ?? null,
    'target_result'
        => $data['target_result']
            ?? null,
    'status' => 'not_started',
    'progress' => 0,
]);
```

## Langkah 5: Form

Field:

```text
Judul
Deskripsi
Target Hasil
```

## Pengujian

1. Check-in.
2. Tambah rencana.
3. Pastikan item muncul.
4. Pastikan `source_type=personal_plan`.
5. Personel lain tidak boleh mengubah.

## Penjelasan untuk Pemula

Satu WorkReport dapat memiliki banyak WorkItem.

Bayangkan:

```text
Laporan Hari Jumat
├── Membuat surat
├── Memeriksa dokumen
└── Menyusun bahan paparan
```

Setiap baris pekerjaan adalah satu `WorkItem`.

### `personal_plan`

Digunakan untuk pekerjaan yang dibuat sendiri oleh Personel.

Nanti tugas Pimpinan menggunakan:

```text
leader_task
```

Dengan satu tabel WorkItem, kita dapat menangani dua sumber pekerjaan tanpa membuat tabel terpisah.

## Penjelasan Gamblang: Rencana Kerja Ini Untuk Apa?

### `WorkReport`
Sampul laporan hari WFH.

### `WorkItem`
Satu pekerjaan di dalam laporan.

### `source_type = personal_plan`
Menandai pekerjaan dibuat sendiri oleh Personel.

### `title`
Nama singkat pekerjaan.

### `description`
Penjelasan pekerjaan.

### `target_result`
Hasil yang ingin dicapai.

### `status = not_started`
Status awal sebelum dikerjakan.

### `progress = 0`
Progress awal.

### Kenapa satu report memiliki banyak items?
Karena dalam satu hari Personel bisa mengerjakan lebih dari satu pekerjaan.

## Checklist

- [ ] Current report
- [ ] Form
- [ ] Store
- [ ] personal_plan
- [ ] Ownership

## Modul Berikutnya

Modul 12 membuat tugas dari Pimpinan.
