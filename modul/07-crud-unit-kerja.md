# Modul 07: CRUD Unit Kerja


> **Untuk pemula:** jangan menyalin semua modul sekaligus. Kerjakan satu modul, jalankan, uji, lalu lanjut.  
> Semua contoh kode diberi komentar pada bagian penting agar alur Laravel mudah dipahami.


## Tujuan

Membuat fitur Admin untuk mengelola unit.

## Hasil Akhir

Admin dapat:

- melihat daftar unit;
- tambah unit;
- edit unit;
- menonaktifkan unit;
- mengaktifkan kembali.

## Struktur File

```text
app/Http/Controllers/Admin/UnitController.php
resources/views/admin/units/index.blade.php
resources/views/admin/units/create.blade.php
resources/views/admin/units/edit.blade.php
routes/web.php
```

## Langkah 1: Buat Controller

```bash
php artisan make:controller Admin/UnitController
```

Isi:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(
        Request $request
    ): View {
        $search = trim(
            (string) $request->input('search')
        );

        $units = Unit::query()
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(
                        function ($sub) use ($search) {
                            $sub->where(
                                'code',
                                'like',
                                "%{ $search }%"
                            )->orWhere(
                                'name',
                                'like',
                                "%{ $search }%"
                            );
                        }
                    );
                }
            )
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view(
            'admin.units.index',
            compact(
                'units',
                'search'
            )
        );
    }

    public function create(): View
    {
        return view('admin.units.create');
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                'unique:units,code',
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ]);

        Unit::create([
            'code' => strtoupper(
                $data['code']
            ),
            'name' => $data['name'],
            'description' =>
                $data['description'] ?? null,
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.units.index')
            ->with(
                'success',
                'Unit berhasil ditambahkan.'
            );
    }

    public function edit(
        Unit $unit
    ): View {
        return view(
            'admin.units.edit',
            compact('unit')
        );
    }

    public function update(
        Request $request,
        Unit $unit
    ): RedirectResponse {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique(
                    'units',
                    'code'
                )->ignore($unit->id),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ]);

        $unit->update([
            'code' => strtoupper(
                $data['code']
            ),
            'name' => $data['name'],
            'description' =>
                $data['description'] ?? null,
        ]);

        return redirect()
            ->route('admin.units.index')
            ->with(
                'success',
                'Unit berhasil diperbarui.'
            );
    }

    public function toggle(
        Unit $unit
    ): RedirectResponse {
        $unit->update([
            'is_active' => ! $unit->is_active,
        ]);

        return back()->with(
            'success',
            'Status unit berhasil diubah.'
        );
    }
}
```

## Langkah 2: Route

Dalam group Admin:

```php
Route::get('/units', [
    UnitController::class,
    'index',
])->name('units.index');

Route::get('/units/create', [
    UnitController::class,
    'create',
])->name('units.create');

Route::post('/units', [
    UnitController::class,
    'store',
])->name('units.store');

Route::get('/units/{unit}/edit', [
    UnitController::class,
    'edit',
])->name('units.edit');

Route::put('/units/{unit}', [
    UnitController::class,
    'update',
])->name('units.update');

Route::patch('/units/{unit}/toggle', [
    UnitController::class,
    'toggle',
])->name('units.toggle');
```

## Langkah 3: View Index

Buat `resources/views/admin/units/index.blade.php`.

Minimal:

```blade
@extends('layouts.app')

@section('title', 'Unit Kerja')

@section('content')
    <div class="d-flex justify-content-between mb-3">
        <h1 class="h3">Unit Kerja</h1>

        <a
            href="{ route('admin.units.create') }"
            class="btn btn-success"
        >
            Tambah Unit
        </a>
    </div>

    <form method="GET" class="mb-3">
        <input
            type="text"
            name="search"
            value="{ $search }"
            class="form-control"
            placeholder="Cari unit..."
        >
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($units as $unit)
                        <tr>
                            <td>{ $unit->code }</td>
                            <td>{ $unit->name }</td>
                            <td>
                                {
                                    $unit->is_active
                                        ? 'Aktif'
                                        : 'Nonaktif'
                                }
                            </td>
                            <td>
                                <a
                                    href="{
                                        route(
                                            'admin.units.edit',
                                            $unit
                                        )
                                    }"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    Edit
                                </a>

                                <form
                                    method="POST"
                                    action="{
                                        route(
                                            'admin.units.toggle',
                                            $unit
                                        )
                                    }"
                                    class="d-inline"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        class="btn btn-sm btn-outline-secondary"
                                    >
                                        Ubah Status
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        { $units->links() }
    </div>
@endsection
```

## Langkah 4: Create/Edit

Buat form Bootstrap dengan:

```text
code
name
description
```

Gunakan:

```blade
@csrf
```

dan pada edit:

```blade
@method('PUT')
```

## Kenapa Tidak Ada Delete?

Unit yang sudah dipakai user sebaiknya tidak dihapus.

Gunakan aktif/nonaktif.

## Pengujian Browser

1. Login Admin.
2. Tambah unit.
3. Coba kode duplikat.
4. Edit nama.
5. Nonaktifkan.
6. Aktifkan kembali.
7. Cari unit.

## Penjelasan untuk Pemula

CRUD adalah singkatan dari:

```text
Create = Tambah
Read   = Lihat
Update = Ubah
Delete = Hapus
```

Pada Unit Kerja kita sedikit memodifikasi konsep tersebut.

Daripada menghapus unit, kita menggunakan:

```text
Aktif
Nonaktif
```

karena unit mungkin sudah dipakai oleh pengguna.

### Bagaimana Data Mengalir?

Saat Admin menambah unit:

```text
Form Blade
    ↓
POST /admin/units
    ↓
UnitController@store
    ↓
Validasi
    ↓
Unit::create()
    ↓
Database
```

Ini adalah pola dasar yang akan sering dipakai di Laravel.

## Penjelasan Gamblang: CRUD Unit Ini Untuk Apa?

### `index()`
Menampilkan daftar unit.

### `create()`
Menampilkan form tambah unit.

### `store()`
Memproses data dari form dan menyimpannya ke database.

### `edit()`
Menampilkan form edit unit yang sudah ada.

### `update()`
Menyimpan perubahan unit.

### `toggle()`
Mengubah `is_active` dari aktif ke nonaktif atau sebaliknya.

### `paginate(20)`
Membatasi daftar menjadi 20 baris per halaman agar halaman tetap ringan.

### `withQueryString()`
Mempertahankan pencarian/filter saat berpindah halaman.

### `Rule::unique(...)->ignore($unit->id)`
Menjaga kode unit tetap unik, tetapi tidak menganggap nilai lama milik unit itu sendiri sebagai duplikat.

### Kenapa unit tidak dihapus permanen?
Karena unit mungkin sudah terhubung ke histori user dan laporan.

## Checklist

- [ ] Index
- [ ] Search
- [ ] Create
- [ ] Store
- [ ] Edit
- [ ] Update
- [ ] Toggle status
- [ ] Pagination


## Simpan Checkpoint Git

Setelah modul berhasil:

```bash
git status
git add .
git commit -m "Selesaikan modul ini"
```

Tujuan checkpoint adalah agar project mudah dikembalikan bila modul berikutnya mengalami error.


## Modul Berikutnya

Modul 08 membuat CRUD Pengguna dan Reset Password.
