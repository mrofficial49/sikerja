# Modul 02: Route, Controller, Blade, dan Bootstrap


> **Untuk pemula:** jangan menyalin semua modul sekaligus. Kerjakan satu modul, jalankan, uji, lalu lanjut.  
> Semua contoh kode diberi komentar pada bagian penting agar alur Laravel mudah dipahami.


## Tujuan

Memahami alur Laravel:

```text
URL -> Route -> Controller -> Blade -> Browser
```

dan membuat layout awal SIKERJA.

## Hasil Akhir

Halaman:

```text
/modul/02
```

menampilkan layout Bootstrap.

## Struktur File

```text
routes/web.php
app/Http/Controllers/LearningController.php
resources/views/layouts/app.blade.php
resources/views/learning/index.blade.php
resources/css/app.css
resources/js/app.js
```

## Langkah 1: Pasang Bootstrap

```bash
npm install bootstrap @popperjs/core
```

## Langkah 2: Edit `resources/css/app.css`

```css
@import 'bootstrap/dist/css/bootstrap.min.css';

body {
    background: #f4f7f5;
}
```

## Langkah 3: Edit `resources/js/app.js`

```javascript
import './bootstrap';
import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;
```

## Langkah 4: Buat Controller

```bash
php artisan make:controller LearningController
```

Isi:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LearningController extends Controller
{
    public function index(): View
    {
        // Data ini akan dikirim ke Blade.
        $title = 'Belajar Dasar Laravel SIKERJA';

        return view(
            'learning.index',
            compact('title')
        );
    }
}
```

## Langkah 5: Buat Route

Di `routes/web.php`:

```php
use App\Http\Controllers\LearningController;

Route::get('/modul/02', [
    LearningController::class,
    'index',
])->name('learning.index');
```

## Langkah 6: Buat Layout

Buat:

```text
resources/views/layouts/app.blade.php
```

Isi:

```blade
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>@yield('title', 'SIKERJA')</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
</head>

<body>
    <nav class="navbar navbar-dark bg-success">
        <div class="container">
            <a
                class="navbar-brand fw-bold"
                href="{ url('/') }"
            >
                SIKERJA
            </a>
        </div>
    </nav>

    <main class="container py-4">
        @yield('content')
    </main>
</body>
</html>
```

## Langkah 7: Buat View

```bash
mkdir -p resources/views/learning
```

Buat:

```text
resources/views/learning/index.blade.php
```

Isi:

```blade
@extends('layouts.app')

@section('title', 'Modul 02')

@section('content')
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h3">
                { $title }
            </h1>

            <p class="mb-0">
                Route, Controller, Blade, dan Bootstrap berhasil.
            </p>
        </div>
    </div>
@endsection
```

## Penjelasan Kode

### `@extends`

Memakai layout induk.

### `@section`

Mengisi bagian tertentu dari layout.

### `{{ $title }}`

Menampilkan variabel dari controller secara aman.

### `@vite`

Memuat CSS dan JS hasil Vite.

## Pengujian

```bash
php artisan route:list --name=learning
```

Buka:

```text
http://127.0.0.1:8000/modul/02
```

Pastikan:

- Bootstrap tampil;
- navbar hijau;
- judul dari controller tampil.

## Kesalahan Umum

### View not found

Periksa nama file.

### Controller not found

Periksa import pada `routes/web.php`.

### CSS tidak muncul

Pastikan `npm run dev` berjalan.

## Penjelasan untuk Pemula

Modul ini sangat penting karena menjelaskan alur dasar Laravel.

### Route

Route menentukan alamat halaman.

Contoh:

```php
Route::get('/modul/02', ...);
```

artinya ketika browser membuka:

```text
/modul/02
```

Laravel tahu kode mana yang harus dijalankan.

### Controller

Controller berisi logika.

Contoh:

```php
$title = 'Belajar Dasar Laravel';
```

Controller dapat mengambil data database, memvalidasi input, lalu mengirim data ke Blade.

### Blade

Blade adalah template HTML milik Laravel.

Contoh:

```blade
{{ $title }}
```

berarti tampilkan nilai `$title`.

### Layout

Layout digunakan agar kita tidak menulis navbar, sidebar, dan struktur HTML berulang-ulang.

Bayangkan:

```text
layouts/app.blade.php
```

sebagai cetakan halaman utama.

Halaman lain tinggal mengisi bagian:

```blade
@section('content')
```

### Bootstrap

Bootstrap membantu membuat tampilan lebih cepat tanpa menulis semua CSS dari nol.

Contoh:

```html
class="btn btn-success"
```

langsung menghasilkan tombol hijau.

## Penjelasan Gamblang: Route, Controller, Blade Itu Untuk Apa?

### `Route`
Route digunakan untuk menentukan URL.

Contoh:

```php
Route::get('/modul/02', ...)
```

berarti Laravel akan menangani URL `/modul/02`.

### `LearningController`
Controller digunakan untuk menaruh logika. Di sini kita menyiapkan data `$title` lalu mengirimkannya ke Blade.

### `return view(...)`
Digunakan untuk memerintahkan Laravel menampilkan file Blade.

### `compact('title')`
Mengirim variabel `$title` dari controller ke view.

### `layouts/app.blade.php`
Digunakan sebagai template utama agar navbar, `<head>`, CSS, dan JavaScript tidak ditulis ulang di setiap halaman.

### `@extends('layouts.app')`
Memberi tahu Blade bahwa halaman ini memakai layout utama.

### `@section('content')`
Mengisi bagian konten yang telah disediakan oleh layout.

### `@vite(...)`
Memuat file CSS dan JavaScript yang dikelola Vite.

### `class="btn btn-success"`
Class Bootstrap untuk membuat tombol hijau tanpa menulis CSS manual.

## Checklist

- [ ] Bootstrap terpasang
- [ ] Controller dibuat
- [ ] Route dibuat
- [ ] Layout dibuat
- [ ] Blade dibuat
- [ ] CSS/JS tampil


## Simpan Checkpoint Git

Setelah modul berhasil:

```bash
git status
git add .
git commit -m "Selesaikan modul ini"
```

Tujuan checkpoint adalah agar project mudah dikembalikan bila modul berikutnya mengalami error.


## Modul Berikutnya

Modul 03 membuat database dan migration inti SIKERJA.
