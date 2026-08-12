# Cara Membaca Kode SIKERJA untuk Pemula

Jangan membaca kode seperti membaca paragraf biasa. Pecah menjadi bagian kecil.

Contoh:

```php
$users = User::query()
    ->where('is_active', true)
    ->orderBy('name')
    ->get();
```

Baca dari atas:

### `User`
Kita akan bekerja dengan tabel `users`.

### `::query()`
Memulai query database.

### `->where('is_active', true)`
Hanya pilih user aktif.

### `->orderBy('name')`
Urutkan berdasarkan nama.

### `->get()`
Jalankan query dan ambil hasilnya.

Jadi arti seluruh kode:

> Ambil seluruh user aktif dari database, urutkan berdasarkan nama, lalu simpan hasilnya ke variabel `$users`.

## Cara Membaca Route

```php
Route::get(
    '/admin/users',
    [UserController::class, 'index']
)->name('admin.users.index');
```

Artinya:

- Browser membuka GET `/admin/users`.
- Laravel menjalankan `UserController`.
- Method yang dijalankan adalah `index`.
- Route diberi nama `admin.users.index`.

## Cara Membaca Controller

```php
return view(
    'admin.users.index',
    compact('users')
);
```

Artinya:

- Buka view `admin/users/index.blade.php`.
- Kirim variabel `$users` ke view.

## Cara Membaca Blade

```blade
@foreach ($users as $user)
    {{ $user->name }}
@endforeach
```

Artinya:

- Ulangi setiap data pada `$users`.
- Untuk setiap user, tampilkan namanya.

## Cara Membaca Relasi

```php
$user->unit?->name
```

Artinya:

- Ambil user.
- Ambil unit milik user.
- Ambil nama unit.
- Tanda `?->` mencegah error kalau unit kosong.

## Aturan Belajar

Setiap menemukan kode baru, tanyakan:

1. Data apa yang sedang dipakai?
2. Datanya datang dari mana?
3. Kode ini memeriksa apa?
4. Kode ini menyimpan apa?
5. Hasilnya dikirim ke mana?
