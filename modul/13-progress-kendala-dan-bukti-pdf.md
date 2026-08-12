# Modul 13: Progress, Kendala, dan Bukti Pekerjaan

## Tujuan

Membuat Personel memperbarui pelaksanaan pekerjaan dan mengunggah bukti PDF.

## Langkah 1: Controller

```bash
php artisan make:controller Personnel/WorkExecutionController
```

## Langkah 2: Ownership

```php
private function authorizeItem(
    Request $request,
    WorkItem $workItem
): void {
    $workItem->loadMissing(
        'report.scheduleMember'
    );

    abort_unless(
        $workItem
            ->report
            ->scheduleMember
            ->user_id
        === $request->user()->id,
        403
    );
}
```

## Langkah 3: Validasi Progress

```php
$data = $request->validate([
    'status' => [
        'required',
        Rule::in([
            'not_started',
            'in_progress',
            'blocked',
            'completed',
        ]),
    ],
    'progress' => [
        'required',
        'integer',
        'between:0,100',
    ],
    'obstacle' => [
        'nullable',
        'string',
    ],
    'follow_up_plan' => [
        'nullable',
        'string',
    ],
]);
```

## Langkah 4: Completed Harus 100%

```php
if (
    $data['status'] === 'completed'
    && $data['progress'] !== 100
) {
    return back()->withErrors([
        'progress'
            => 'Pekerjaan selesai harus 100%.',
    ]);
}
```

## Langkah 5: Update

```php
$workItem->update([
    'status' => $data['status'],
    'progress' => $data['progress'],
    'obstacle'
        => $data['obstacle'] ?? null,
    'follow_up_plan'
        => $data['follow_up_plan'] ?? null,
    'continue_offline'
        => $request->boolean(
            'continue_offline'
        ),
]);
```

## Langkah 6: Upload PDF

```php
$request->validate([
    'file' => [
        'required',
        'file',
        'mimes:pdf',
        'max:10240',
    ],
]);
```

```php
$file = $request->file('file');

$path = $file->store(
    'work-items/' . $workItem->id,
    'local'
);

WorkItemFile::create([
    'item_id' => $workItem->id,
    'file_path' => $path,
    'original_name'
        => $file->getClientOriginalName(),
    'mime_type'
        => $file->getMimeType(),
    'file_size'
        => $file->getSize(),
]);
```

**Kolom benar adalah `item_id`, bukan `work_item_id`.**

## Pengujian

- progress 50;
- blocked + kendala;
- completed 100;
- completed 50 ditolak;
- upload PDF;
- upload JPG ditolak;
- user lain 403.

## Penjelasan untuk Pemula

Progress menjelaskan seberapa jauh pekerjaan dikerjakan.

Contoh:

```text
0   = belum mulai
25  = seperempat
50  = setengah
75  = hampir selesai
100 = selesai
```

Status memberi arti pada progress.

Contoh:

```text
in_progress = sedang dikerjakan
blocked     = ada kendala
completed   = selesai
```

### Apa itu Ownership?

Ownership berarti memastikan data benar-benar milik pengguna.

Contoh:

```text
Personel A
```

tidak boleh mengubah pekerjaan milik:

```text
Personel B
```

Karena itu controller memeriksa hubungan:

```text
WorkItem
→ WorkReport
→ ScheduleMember
→ User
```

## Penjelasan Gamblang: Progress dan Bukti Ini Untuk Apa?

### `progress`
Angka 0 sampai 100 untuk menunjukkan persentase pekerjaan.

### `status`
Memberi makna terhadap kondisi pekerjaan, misalnya `in_progress`, `blocked`, atau `completed`.

### `obstacle`
Menyimpan kendala yang dihadapi.

### `follow_up_plan`
Menyimpan rencana tindak lanjut.

### `continue_offline`
Menandai pekerjaan akan dilanjutkan di luar sesi WFH jika belum selesai.

### `WorkItemFile`
Menyimpan metadata bukti pekerjaan.

### `item_id`
Menghubungkan file dengan WorkItem.

### Kenapa file PDF saja?
Agar bukti berbentuk dokumen terstruktur dan ukuran/format lebih mudah dikendalikan.

### Kenapa completed harus 100%?
Supaya status dan angka progress konsisten.

## Checklist

- [ ] Ownership
- [ ] Status
- [ ] Progress
- [ ] Kendala
- [ ] Follow up
- [ ] Continue offline
- [ ] PDF private
- [ ] item_id

## Modul Berikutnya

Modul 14 membuat Laporan Kerja.
