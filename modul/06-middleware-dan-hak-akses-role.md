# Modul 06: Middleware dan Hak Akses Role


> **Untuk pemula:** jangan menyalin semua modul sekaligus. Kerjakan satu modul, jalankan, uji, lalu lanjut.  
> Semua contoh kode diberi komentar pada bagian penting agar alur Laravel mudah dipahami.


## Tujuan

Melindungi route berdasarkan:

- login;
- akun aktif;
- perubahan password;
- role.

## Hasil Akhir

Admin, Pimpinan, dan Personel tidak dapat membuka menu yang bukan haknya.

## Langkah 1: EnsureRole

```bash
php artisan make:middleware EnsureRole
```

Isi:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {
        $roleName = $request
            ->user()
            ?->role
            ?->name;

        abort_unless(
            in_array(
                $roleName,
                $roles,
                true
            ),
            403
        );

        return $next($request);
    }
}
```

## Langkah 2: Active Middleware

```bash
php artisan make:middleware EnsureUserIsActive
```

```php
if (! $request->user()?->is_active) {
    auth()->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()
        ->route('login')
        ->withErrors([
            'login_id' =>
                'Akun tidak aktif.',
        ]);
}

return $next($request);
```

## Langkah 3: Password Middleware

```bash
php artisan make:middleware EnsurePasswordIsChanged
```

```php
if (
    $request->user()?->must_change_password
    && ! $request->routeIs('password.*')
    && ! $request->routeIs('logout')
) {
    return redirect()
        ->route('password.edit');
}

return $next($request);
```

## Langkah 4: Alias Middleware

Buka `bootstrap/app.php`.

Tambahkan alias:

```php
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Foundation\Configuration\Middleware;
```

Pada `withMiddleware`:

```php
->withMiddleware(
    function (Middleware $middleware) {
        $middleware->alias([
            'role' => EnsureRole::class,
            'active' =>
                EnsureUserIsActive::class,
        ]);
    }
)
```

## Langkah 5: Contoh Route Admin

```php
Route::middleware([
    'auth',
    'active',
    EnsurePasswordIsChanged::class,
    'role:Admin',
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Semua route Admin ada di sini.
    });
```

## Langkah 6: Pimpinan

```php
'role:Pimpinan'
```

## Langkah 7: Personel

```php
'role:Personel'
```

## Catatan Penting

Menyembunyikan menu di Blade **tidak cukup**.

Route tetap harus memiliki middleware.

## Pengujian Manual

- Guest buka URL internal.
- Personel buka URL Admin.
- Pimpinan buka URL Admin.
- Admin buka URL Personel.
- User nonaktif buka dashboard.

## Hasil

```text
Guest           -> Login
Salah Role      -> 403
Akun Nonaktif   -> Logout
Password Temp   -> Ubah Password
```

## Penjelasan untuk Pemula

Middleware dapat dibayangkan sebagai satpam.

Sebelum pengguna masuk ke halaman tertentu, middleware memeriksa:

```text
Apakah sudah login?
Apakah akun aktif?
Apakah password sudah diganti?
Apakah role sesuai?
```

Contoh:

```text
Personel
    ↓
mencoba membuka /admin/users
    ↓
Middleware role memeriksa
    ↓
Role bukan Admin
    ↓
403 Forbidden
```

### Kenapa Menu Saja Tidak Cukup Disembunyikan?

Misalnya menu Admin tidak terlihat oleh Personel.

Personel masih bisa mencoba mengetik langsung URL:

```text
/admin/users
```

Karena itu keamanan harus berada di route/middleware, bukan hanya di tampilan.

## Penjelasan Gamblang: Middleware Ini Untuk Apa?

### Middleware
Middleware adalah filter sebelum request masuk ke controller.

### `EnsureRole`
Memeriksa apakah role user sesuai dengan route.

### `EnsureUserIsActive`
Memeriksa apakah akun masih aktif.

### `EnsurePasswordIsChanged`
Memaksa user mengganti password sementara sebelum mengakses fitur lain.

### `abort_unless(..., 403)`
Jika syarat tidak terpenuhi, Laravel menghentikan request dan mengirim error 403.

### Alias middleware
Alias seperti:

```php
'role' => EnsureRole::class
```

membuat route dapat memakai penulisan pendek:

```php
'role:Admin'
```

### Kenapa middleware ditempatkan di route?
Supaya keamanan tetap berlaku walaupun user mengetik URL secara manual.

## Checklist

- [ ] EnsureRole
- [ ] Active middleware
- [ ] Password middleware
- [ ] Alias tersedia
- [ ] Admin dibatasi
- [ ] Pimpinan dibatasi
- [ ] Personel dibatasi


## Simpan Checkpoint Git

Setelah modul berhasil:

```bash
git status
git add .
git commit -m "Selesaikan modul ini"
```

Tujuan checkpoint adalah agar project mudah dikembalikan bila modul berikutnya mengalami error.


## Modul Berikutnya

Modul 07 membuat CRUD Unit Kerja.
