# Modul 15B: Ekspor Rekap Kinerja ke PDF

## Tujuan

Membuat rekap monitoring dapat diunduh sebagai PDF.

## Langkah 1: Install

```bash
composer require barryvdh/laravel-dompdf
```

Periksa:

```bash
composer show barryvdh/laravel-dompdf
```

## Langkah 2: Controller

```bash
php artisan make:controller MonitoringPdfController
```

Import:

```php
use Barryvdh\DomPDF\Facade\Pdf;
```

## Langkah 3: Query

Gunakan filter Monitoring yang sama.

Perbedaannya:

```php
$members = $membersQuery->get();
```

bukan paginator.

## Langkah 4: Summary PDF

Hitung:

```text
total
checked_in
checked_out
waiting_verification
needs_revision
approved
total_items
completed_items
```

## Langkah 5: Logo

```php
$logoDataUri = null;

$logoPath = public_path(
    'images/logo-sikerja.png'
);

if (is_file($logoPath)) {
    $logoDataUri =
        'data:'
        . mime_content_type(
            $logoPath
        )
        . ';base64,'
        . base64_encode(
            file_get_contents(
                $logoPath
            )
        );
}
```

## Langkah 6: Generate

```php
$fileName = sprintf(
    'rekap-kinerja-wfh-%s.pdf',
    $selectedSchedule
        ->wfh_date
        ->format('Y-m-d')
);

return Pdf::loadView(
    'monitoring.pdf',
    compact(
        'members',
        'summary',
        'selectedSchedule',
        'selectedUnit',
        'search',
        'generatedBy',
        'generatedAt',
        'logoDataUri'
    )
)
    ->setPaper(
        'a4',
        'landscape'
    )
    ->download($fileName);
```

## Langkah 7: View PDF

Buat:

```text
resources/views/monitoring/pdf.blade.php
```

Gunakan CSS internal.

Bagian:

```text
Logo
Judul
Tanggal
Filter
Summary
Tabel
Detail pekerjaan
Catatan verifikasi
Tanda tangan
Nomor halaman
```

## Langkah 8: Route

Admin:

```text
admin.monitoring.pdf
```

Pimpinan:

```text
leader.monitoring.pdf
```

## Pengujian

```bash
php artisan route:list --name=monitoring.pdf
```

```bash
php artisan tinker --execute="
dump(
    class_exists(
        Barryvdh\\DomPDF\\Facade\\Pdf::class
    )
);
"
```

Harus `true`.

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

## Penjelasan Gamblang: Export PDF Ini Untuk Apa?

### DOMPDF
Library yang mengubah HTML/Blade menjadi PDF.

### Kenapa query tidak `paginate()`?
Karena PDF rekap harus berisi seluruh data hasil filter, bukan hanya halaman pertama.

### `logoDataUri`
Mengubah file logo menjadi data Base64 agar dapat dibaca DOMPDF secara stabil.

### `setPaper('a4', 'landscape')`
Mengatur ukuran A4 horizontal agar tabel lebar muat.

### `download($fileName)`
Mengirim PDF sebagai file unduhan.

### Kenapa punya view PDF sendiri?
Karena layout browser dan layout cetak memiliki kebutuhan berbeda.

## Checklist

- [ ] Package
- [ ] Controller
- [ ] Filter
- [ ] Summary
- [ ] Logo
- [ ] Landscape
- [ ] Admin
- [ ] Pimpinan
- [ ] Personel dilarang

## Modul Berikutnya

Modul 16 membuat Notifikasi.
