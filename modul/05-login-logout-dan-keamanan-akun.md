# Modul 05: Login, Logout, dan Keamanan Akun


> **Untuk pemula:** jangan menyalin semua modul sekaligus. Kerjakan satu modul, jalankan, uji, lalu lanjut.  
> Semua contoh kode diberi komentar pada bagian penting agar alur Laravel mudah dipahami.


## Tujuan

Membuat autentikasi manual menggunakan `login_id` dan password.

## Hasil Akhir

Pengguna dapat:

- login;
- logout;
- ditolak bila akun nonaktif;
- diarahkan mengganti password sementara.

## Struktur File

```text
app/Http/Controllers/AuthController.php
resources/views/auth/login.blade.php
resources/views/auth/change-password.blade.php
routes/web.php
```

## Langkah 1: Buat Controller

```bash
php artisan make:controller AuthController
```

Isi:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(
        Request $request
    ): RedirectResponse {
        $credentials = $request->validate([
            'login_id' => [
                'required',
                'string',
            ],
            'password' => [
                'required',
                'string',
            ],
        ]);

        if (! Auth::attempt($credentials)) {
            return back()
                ->withErrors([
                    'login_id' =>
                        'ID Login atau password salah.',
                ])
                ->onlyInput('login_id');
        }

        // Mencegah session fixation.
        $request->session()->regenerate();

        if (! $request->user()->is_active) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'login_id' =>
                    'Akun Anda sedang dinonaktifkan.',
            ]);
        }

        return redirect()
            ->intended(route('dashboard'));
    }

    public function logout(
        Request $request
    ): RedirectResponse {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with(
                'success',
                'Anda telah keluar.'
            );
    }

    public function editPassword(): View
    {
        return view(
            'auth.change-password'
        );
    }

    public function updatePassword(
        Request $request
    ): RedirectResponse {
        $data = $request->validate([
            'password' => [
                'required',
                'confirmed',
                Password::min(8),
            ],
        ]);

        $request->user()->update([
            'password' => Hash::make(
                $data['password']
            ),
            'must_change_password' => false,
        ]);

        return redirect()
            ->route('dashboard')
            ->with(
                'success',
                'Password berhasil diubah.'
            );
    }
}
```

## Langkah 2: Route

```php
use App\Http\Controllers\AuthController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [
        AuthController::class,
        'showLogin',
    ])->name('login');

    Route::post('/login', [
        AuthController::class,
        'login',
    ])->name('login.process');
});

Route::post('/logout', [
    AuthController::class,
    'logout',
])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/ubah-password', [
        AuthController::class,
        'editPassword',
    ])->name('password.edit');

    Route::put('/ubah-password', [
        AuthController::class,
        'updatePassword',
    ])->name('password.update');
});
```

## Langkah 3: View Login

```blade
@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 mb-4">
                        Login SIKERJA
                    </h1>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            { $errors->first() }
                        </div>
                    @endif

                    <form
                        method="POST"
                        action="{ route('login.process') }"
                    >
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">
                                ID Login / NRP / NIP
                            </label>

                            <input
                                type="text"
                                name="login_id"
                                class="form-control"
                                value="{ old('login_id') }"
                                required
                                autofocus
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Password
                            </label>

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                required
                            >
                        </div>

                        <button
                            class="btn btn-success w-100"
                            type="submit"
                        >
                            Masuk
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
```

## Langkah 4: Buat User Admin Sementara

```bash
php artisan tinker
```

Contoh:

```php
$role = App\Models\Role::where(
    'name',
    'Admin'
)->firstOrFail();

$unit = App\Models\Unit::first();

App\Models\User::create([
    'name' => 'Administrator',
    'login_id' => 'ADMIN001',
    'email' => 'admin@example.test',
    'role_id' => $role->id,
    'unit_id' => $unit?->id,
    'password' => Hash::make(
        'AdminSikerja#2026'
    ),
    'is_active' => true,
    'must_change_password' => false,
]);
```

## Pengujian Browser

1. Buka `/login`.
2. Login password benar.
3. Logout.
4. Login password salah.
5. Nonaktifkan akun via Tinker lalu coba login.
6. Set `must_change_password = true`.

## Kesalahan Umum

### 419 Page Expired

Form belum `@csrf`.

### Login selalu gagal

Password database tidak di-hash.

### Setelah logout masih bisa kembali dengan Back

Browser mungkin menampilkan cache visual, tetapi request baru harus tetap butuh login.

## Penjelasan untuk Pemula

Login adalah proses membuktikan identitas pengguna.

Pada SIKERJA pengguna memasukkan:

```text
login_id
password
```

Laravel kemudian mencocokkannya dengan database.

### Apa itu `Auth::attempt()`?

```php
Auth::attempt($credentials)
```

berarti:

> Coba login menggunakan data yang diberikan pengguna.

Jika benar Laravel membuat session login.

### Apa itu Session?

Session adalah cara website "mengingat" bahwa pengguna sudah login.

Tanpa session, setiap membuka halaman baru website akan lupa siapa pengguna tersebut.

### Kenapa Password Harus di-hash?

Password tidak boleh disimpan seperti:

```text
rahasia123
```

di database.

Laravel menyimpannya dalam bentuk hash sehingga lebih aman.

### Kenapa Session Diregenerate?

```php
$request->session()->regenerate();
```

digunakan sebagai langkah keamanan untuk mengurangi risiko session fixation.

## Penjelasan Gamblang: Setiap Bagian Login Ini Untuk Apa?

### `$request->validate(...)`
Memeriksa data dari form sebelum dipakai. Ini mencegah input kosong atau format salah.

### `Auth::attempt($credentials)`
Mencoba mencocokkan `login_id` dan password dengan database.

### `session()->regenerate()`
Membuat session ID baru setelah login agar lebih aman.

### `is_active`
Dipakai untuk memblokir akun yang sudah dinonaktifkan Admin.

### `Auth::logout()`
Menghapus status autentikasi pengguna.

### `session()->invalidate()`
Menghapus session lama.

### `regenerateToken()`
Membuat token CSRF baru setelah logout.

### `Hash::make()`
Mengubah password biasa menjadi hash sebelum disimpan.

### `must_change_password`
Penanda bahwa password saat ini masih password sementara dan wajib diganti.

### `confirmed`
Memastikan `password` sama dengan `password_confirmation`.

## Checklist

- [ ] Login
- [ ] Logout
- [ ] CSRF
- [ ] Session regenerate
- [ ] Akun nonaktif ditolak
- [ ] Ubah password
- [ ] Hash password


## Simpan Checkpoint Git

Setelah modul berhasil:

```bash
git status
git add .
git commit -m "Selesaikan modul ini"
```

Tujuan checkpoint adalah agar project mudah dikembalikan bila modul berikutnya mengalami error.


## Modul Berikutnya

Modul 06 membuat middleware role dan pembatasan akses.
