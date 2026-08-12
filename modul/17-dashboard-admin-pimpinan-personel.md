# Modul 17: Dashboard Admin, Pimpinan, dan Personel

## Tujuan

Membuat halaman dashboard yang berbeda untuk setiap role.

## Hasil Akhir

Route:

```text
/dashboard
```

menampilkan informasi yang sesuai dengan tanggung jawab pengguna.

## Struktur File

```text
app/Http/Controllers/DashboardController.php
resources/views/admin/dashboard.blade.php
resources/views/leader/dashboard.blade.php
resources/views/personnel/dashboard.blade.php
routes/web.php
```

## Langkah 1: Buat Controller

```bash
php artisan make:controller DashboardController
```

## Langkah 2: Method Index

```php
public function index(
    Request $request
) {
    $role = $request
        ->user()
        ->role
        ?->name;

    return match ($role) {
        'Admin'
            => $this->admin(
                $request
            ),

        'Pimpinan'
            => $this->leader(
                $request
            ),

        'Personel'
            => $this->personnel(
                $request
            ),

        default
            => abort(403),
    };
}
```

## Langkah 3: Dashboard Admin

```php
private function admin(
    Request $request
): View {
    $totalUsers = User::query()
        ->where('is_active', true)
        ->count();

    $totalUnits = Unit::query()
        ->where('is_active', true)
        ->count();

    $activeSchedule =
        WfhSchedule::query()
            ->where('status', 'active')
            ->orderByDesc('wfh_date')
            ->first();

    $waitingReports =
        WorkReport::query()
            ->where(
                'status',
                'waiting_verification'
            )
            ->count();

    return view(
        'admin.dashboard',
        compact(
            'totalUsers',
            'totalUnits',
            'activeSchedule',
            'waitingReports'
        )
    );
}
```

## Langkah 4: Dashboard Pimpinan

Contoh:

```php
private function leader(
    Request $request
): View {
    $activeSchedule =
        WfhSchedule::query()
            ->where('status', 'active')
            ->orderByDesc('wfh_date')
            ->first();

    $waitingReports =
        WorkReport::query()
            ->where(
                'status',
                'waiting_verification'
            )
            ->count();

    $assignedTasks =
        WorkItem::query()
            ->where(
                'created_by',
                $request->user()->id
            )
            ->where(
                'source_type',
                'leader_task'
            )
            ->where(
                'status',
                '!=',
                'cancelled'
            )
            ->count();

    return view(
        'leader.dashboard',
        compact(
            'activeSchedule',
            'waitingReports',
            'assignedTasks'
        )
    );
}
```

## Langkah 5: Dashboard Personel

```php
private function personnel(
    Request $request
): View {
    $member =
        WfhScheduleMember::query()
            ->with([
                'schedule',
                'attendance',
                'workReport.items',
            ])
            ->where(
                'user_id',
                $request->user()->id
            )
            ->whereNull('cancelled_at')
            ->whereHas(
                'schedule',
                fn ($query) =>
                    $query->where(
                        'status',
                        'active'
                    )
            )
            ->first();

    return view(
        'personnel.dashboard',
        compact('member')
    );
}
```

## Langkah 6: Route

```php
Route::get('/dashboard', [
    DashboardController::class,
    'index',
])
    ->middleware([
        'auth',
        'active',
        EnsurePasswordIsChanged::class,
    ])
    ->name('dashboard');
```

## Langkah 7: Card Admin

Contoh:

```blade
<div class="row g-3">
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="text-muted small">
                    Pengguna Aktif
                </div>

                <div class="display-6 fw-bold">
                    {{ $totalUsers }}
                </div>
            </div>
        </div>
    </div>
</div>
```

## Langkah 8: Informasi Personel

Dashboard Personel sebaiknya menampilkan:

```text
Tanggal WFH
Status check-in
Status check-out
Jumlah pekerjaan
Progress
Status laporan
Notifikasi
```

## Pengujian Manual

1. Login Admin.
2. Catat dashboard.
3. Logout.
4. Login Pimpinan.
5. Pastikan informasi berbeda.
6. Login Personel.
7. Pastikan hanya data miliknya tampil.

## Kesalahan Umum

### Dashboard semua role sama

Controller belum membedakan role.

### Dashboard lambat

Jangan mengambil semua record untuk menghitung. Gunakan `count()`.

### Data Personel lain terlihat

Query Personel belum dibatasi `user_id`.

## Penjelasan untuk Pemula

Dashboard adalah halaman ringkasan, bukan tempat menampilkan seluruh data.

Tujuannya menjawab:

```text
Apa yang paling penting untuk user ini saat login?
```

Admin perlu melihat informasi berbeda dari Personel.

Karena itu satu route `/dashboard` dapat mengarahkan ke tiga tampilan berbeda berdasarkan role.

## Penjelasan Gamblang: Dashboard Ini Untuk Apa?

### Dashboard
Halaman pertama setelah login yang menampilkan informasi paling penting.

### Kenapa dashboard berbeda per role?
Karena kebutuhan masing-masing berbeda.

Admin membutuhkan:
- jumlah user;
- unit;
- jadwal;
- laporan menunggu.

Pimpinan membutuhkan:
- tugas;
- laporan;
- monitoring.

Personel membutuhkan:
- jadwal;
- presensi;
- pekerjaan;
- status laporan.

### `match ($role)`
Memilih method dashboard berdasarkan role.

## Checklist

- [ ] Dashboard Admin
- [ ] Dashboard Pimpinan
- [ ] Dashboard Personel
- [ ] Route auth
- [ ] Data sesuai role
- [ ] Tidak bocor data

## Simpan Checkpoint Git

```bash
git add .
git commit -m "Tambah dashboard tiga role SIKERJA"
```

## Modul Berikutnya

Modul 18 membuat Redesign UI Modern Military Executive.
