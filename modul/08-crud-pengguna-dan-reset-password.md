# Modul 08: CRUD Pengguna dan Reset Password

> Modul ini melanjutkan Modul 07. Jalankan setiap langkah satu per satu dan uji sebelum lanjut.

## Tujuan

Membuat halaman Admin untuk mengelola pengguna SIKERJA.

Fitur yang dibuat:

- daftar pengguna;
- pencarian;
- filter role;
- filter unit;
- tambah pengguna;
- edit pengguna;
- reset password;
- aktifkan/nonaktifkan akun.

## Hasil Akhir

Admin membuka:

```text
http://127.0.0.1:8000/admin/users
```

dan dapat mengelola akun tanpa menghapus histori.

## Prasyarat

- Login Admin bekerja.
- Middleware `role:Admin` bekerja.
- Tabel `users`, `roles`, `units` tersedia.
- Model User memiliki relasi `role()` dan `unit()`.

## Struktur File

```text
app/Http/Controllers/Admin/UserController.php
resources/views/admin/users/index.blade.php
resources/views/admin/users/create.blade.php
resources/views/admin/users/edit.blade.php
routes/web.php
```

## Langkah 1: Buat Controller

```bash
php artisan make:controller Admin/UserController
```

## Langkah 2: Import Class

```php
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
```

## Langkah 3: Method Index

```php
public function index(Request $request): View
{
    // Ambil filter dari query string.
    $search = trim(
        (string) $request->input('search')
    );

    $roleId = $request->integer('role_id');
    $unitId = $request->integer('unit_id');

    $users = User::query()
        // Ambil role dan unit bersamaan agar query lebih efisien.
        ->with([
            'role',
            'unit',
        ])
        ->when(
            $roleId,
            fn ($query) =>
                $query->where('role_id', $roleId)
        )
        ->when(
            $unitId,
            fn ($query) =>
                $query->where('unit_id', $unitId)
        )
        ->when(
            $search !== '',
            function ($query) use ($search) {
                $query->where(
                    function ($sub) use ($search) {
                        $sub->where(
                            'name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'login_id',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'rank',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'position',
                            'like',
                            "%{$search}%"
                        );
                    }
                );
            }
        )
        ->orderBy('name')
        ->paginate(25)
        ->withQueryString();

    $roles = Role::query()
        ->orderBy('name')
        ->get();

    $units = Unit::query()
        ->where('is_active', true)
        ->orderBy('name')
        ->get();

    return view(
        'admin.users.index',
        compact(
            'users',
            'roles',
            'units',
            'search',
            'roleId',
            'unitId'
        )
    );
}
```

## Penjelasan Index

`with(['role','unit'])` mengambil data relasi bersamaan.

`when()` membuat filter bersifat opsional.

`paginate(25)` membatasi jumlah data per halaman.

`withQueryString()` mempertahankan filter saat pindah halaman.

## Langkah 4: Method Create

```php
public function create(): View
{
    $roles = Role::query()
        ->orderBy('name')
        ->get();

    $units = Unit::query()
        ->where('is_active', true)
        ->orderBy('name')
        ->get();

    return view(
        'admin.users.create',
        compact('roles', 'units')
    );
}
```

## Langkah 5: Method Store

```php
public function store(
    Request $request
): RedirectResponse {
    $data = $request->validate([
        'name' => [
            'required',
            'string',
            'max:150',
        ],
        'login_id' => [
            'required',
            'string',
            'max:50',
            'unique:users,login_id',
        ],
        'email' => [
            'nullable',
            'email',
            'max:150',
            'unique:users,email',
        ],
        'role_id' => [
            'required',
            'exists:roles,id',
        ],
        'unit_id' => [
            'nullable',
            'exists:units,id',
        ],
        'rank' => [
            'nullable',
            'string',
            'max:100',
        ],
        'position' => [
            'nullable',
            'string',
            'max:150',
        ],
        'password' => [
            'required',
            'string',
            'min:8',
        ],
    ]);

    User::create([
        'name' => $data['name'],
        'login_id' => $data['login_id'],
        'email' => $data['email'] ?? null,
        'role_id' => $data['role_id'],
        'unit_id' => $data['unit_id'] ?? null,
        'rank' => $data['rank'] ?? null,
        'position' => $data['position'] ?? null,

        // Password harus di-hash.
        'password' => Hash::make(
            $data['password']
        ),

        'is_active' => true,

        // Password pertama wajib diganti.
        'must_change_password' => true,
    ]);

    return redirect()
        ->route('admin.users.index')
        ->with(
            'success',
            'Pengguna berhasil ditambahkan.'
        );
}
```

## Langkah 6: Edit dan Update

Validasi `login_id`:

```php
'login_id' => [
    'required',
    'string',
    'max:50',
    Rule::unique(
        'users',
        'login_id'
    )->ignore($user->id),
],
```

Update:

```php
$user->update([
    'name' => $data['name'],
    'login_id' => $data['login_id'],
    'email' => $data['email'] ?? null,
    'role_id' => $data['role_id'],
    'unit_id' => $data['unit_id'] ?? null,
    'rank' => $data['rank'] ?? null,
    'position' => $data['position'] ?? null,
]);
```

## Langkah 7: Reset Password

```php
public function resetPassword(
    User $user
): RedirectResponse {
    $temporaryPassword =
        'Sikerja#' . now()->format('Ymd');

    $user->update([
        'password' => Hash::make(
            $temporaryPassword
        ),
        'must_change_password' => true,
    ]);

    return back()->with([
        'success' =>
            'Password berhasil direset.',
        'temporary_password'
            => $temporaryPassword,
    ]);
}
```

## Langkah 8: Aktif / Nonaktif

```php
public function toggleStatus(
    User $user
): RedirectResponse {
    abort_if(
        auth()->id() === $user->id,
        422,
        'Admin tidak dapat menonaktifkan akun sendiri.'
    );

    $user->update([
        'is_active' => ! $user->is_active,
    ]);

    return back()->with(
        'success',
        'Status pengguna berhasil diubah.'
    );
}
```

## Langkah 9: Route

Di dalam group Admin:

```php
Route::get('/users', [
    UserController::class,
    'index',
])->name('users.index');

Route::get('/users/create', [
    UserController::class,
    'create',
])->name('users.create');

Route::post('/users', [
    UserController::class,
    'store',
])->name('users.store');

Route::get('/users/{user}/edit', [
    UserController::class,
    'edit',
])->name('users.edit');

Route::put('/users/{user}', [
    UserController::class,
    'update',
])->name('users.update');

Route::patch(
    '/users/{user}/reset-password',
    [
        UserController::class,
        'resetPassword',
    ]
)->name('users.reset-password');

Route::patch(
    '/users/{user}/toggle-status',
    [
        UserController::class,
        'toggleStatus',
    ]
)->name('users.toggle-status');
```

## Langkah 10: View Index

Kolom:

```text
Nama
Login ID
Pangkat
Jabatan
Unit
Role
Status
Aksi
```

Tombol:

```text
Edit
Reset Password
Aktifkan / Nonaktifkan
```

## Kenapa Tidak Ada Hapus Permanen?

Pengguna yang pernah memiliki presensi, tugas, atau laporan sebaiknya hanya dinonaktifkan. Dengan begitu histori tetap konsisten.

## Pengujian Manual

1. Login Admin.
2. Tambah Personel.
3. Login dengan akun baru.
4. Pastikan diminta ganti password.
5. Edit user.
6. Cari nama.
7. Filter role.
8. Filter unit.
9. Reset password.
10. Nonaktifkan akun.
11. Coba login akun tersebut.
12. Aktifkan kembali.

## Kesalahan Umum

- `MassAssignmentException`: periksa `$fillable`.
- password tidak bekerja: pastikan `Hash::make()`.
- akun nonaktif masih login: periksa middleware `active`.
- duplicate login_id: validasi unique.

## Penjelasan untuk Pemula

CRUD Pengguna hampir sama dengan CRUD Unit, tetapi lebih sensitif karena berkaitan dengan login dan hak akses.

### Mengapa Ada `role_id`?

Karena satu user memiliki satu role.

Contoh:

```text
User Dwi
role_id = 3
        ↓
Role ID 3 = Personel
```

### Mengapa Ada `unit_id`?

Untuk mengetahui pengguna berasal dari unit mana.

### Mengapa Password Awal Wajib Diganti?

Admin membuat akun dengan password sementara.

Setelah user pertama kali login:

```text
Password sementara
        ↓
must_change_password = true
        ↓
Middleware mengarahkan
        ↓
User membuat password sendiri
```

### Mengapa User Tidak Dihapus?

Kalau user dihapus, histori lama bisa kehilangan identitas pemilik.

Karena itu:

```text
Tidak digunakan lagi
        ↓
Nonaktifkan akun
```

lebih aman daripada delete permanen.

## Penjelasan Gamblang: CRUD Pengguna Ini Untuk Apa?

### `with(['role','unit'])`
Mengambil user beserta role dan unit dalam query yang efisien.

### `role_id`
Menyimpan role pengguna.

### `unit_id`
Menyimpan unit pengguna.

### `login_id`
Identitas untuk login, misalnya NRP/NIP/ID khusus.

### `Hash::make($password)`
Mengamankan password sebelum masuk database.

### `must_change_password = true`
Memaksa pengguna mengganti password awal.

### `resetPassword()`
Digunakan Admin ketika pengguna lupa password.

### `toggleStatus()`
Menonaktifkan akun tanpa menghapus histori.

### `abort_if(auth()->id() === $user->id, ...)`
Mencegah Admin tidak sengaja menonaktifkan akun dirinya sendiri.

### Kenapa tidak ada tombol Delete?
Karena laporan, tugas, dan presensi lama harus tetap memiliki pemilik yang jelas.

## Checklist

- [ ] Index
- [ ] Search
- [ ] Filter role
- [ ] Filter unit
- [ ] Create
- [ ] Edit
- [ ] Reset password
- [ ] Must change password
- [ ] Toggle aktif/nonaktif
- [ ] Tidak ada delete permanen

## Simpan Checkpoint Git

```bash
git add .
git commit -m "Selesaikan CRUD pengguna SIKERJA"
```

## Modul Berikutnya

Modul 09 membuat Jadwal WFH dan Anggota Jadwal.
