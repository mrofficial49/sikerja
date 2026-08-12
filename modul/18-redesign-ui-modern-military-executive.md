# Modul 18: Redesign UI Modern Military Executive

## Tujuan

Mengubah tampilan Bootstrap standar menjadi tampilan SIKERJA yang profesional tanpa mengubah logika backend.

## Hasil Akhir

SIKERJA memiliki:

- login modern;
- sidebar desktop;
- topbar;
- menu berdasarkan role;
- logo;
- offcanvas mobile;
- card statistik;
- tabel responsif.

## Tema

```text
Modern Military Executive
```

Palet:

```text
Hijau Gelap
Hijau Militer
Putih
Emas
Abu-abu Terang
```

## Struktur File

```text
resources/views/layouts/app.blade.php
resources/views/layouts/partials/sidebar-menu.blade.php
resources/views/auth/login.blade.php
resources/css/app.css
resources/js/app.js
vite.config.js
public/images/logo-sikerja.png
```

## Langkah 1: CSS Variable

```css
:root {
    --sikerja-green-950: #10291e;
    --sikerja-green-900: #173b2b;
    --sikerja-green-800: #224d39;
    --sikerja-green-700: #2d6349;
    --sikerja-gold: #d0aa55;
    --sikerja-bg: #f3f6f4;
    --sikerja-text: #1f2923;
    --sikerja-border: #dfe7e2;
}
```

## Langkah 2: Body

```css
body {
    background:
        var(--sikerja-bg);
    color:
        var(--sikerja-text);
}
```

## Langkah 3: Sidebar

```css
.sikerja-sidebar {
    width: 280px;
    min-height: 100vh;
    background:
        var(--sikerja-green-950);
    color: #fff;
}

.sikerja-sidebar .nav-link {
    color: rgba(255,255,255,.78);
    border-radius: 12px;
    padding: .75rem 1rem;
}

.sikerja-sidebar .nav-link.active {
    color: #fff;
    background:
        rgba(208,170,85,.18);
    border:
        1px solid
        rgba(208,170,85,.4);
}
```

## Langkah 4: Logo

Simpan:

```text
public/images/logo-sikerja.png
```

Blade:

```blade
<img
    src="{{ asset('images/logo-sikerja.png') }}"
    alt="Logo SIKERJA"
    class="sikerja-logo"
>
```

## Langkah 5: Partial Sidebar

Buat:

```text
resources/views/layouts/partials/sidebar-menu.blade.php
```

Contoh menu Admin:

```blade
@if (auth()->user()->isAdmin())
    <a
        href="{{ route('admin.users.index') }}"
        class="nav-link {{
            request()->routeIs(
                'admin.users.*'
            )
                ? 'active'
                : ''
        }}"
    >
        Pengguna
    </a>
@endif
```

Tambahkan menu berbeda untuk Pimpinan/Personel.

## Langkah 6: Mobile Offcanvas

```blade
<div
    class="offcanvas offcanvas-start"
    tabindex="-1"
    id="mobileSidebar"
>
    <div class="offcanvas-body">
        @include(
            'layouts.partials.sidebar-menu'
        )
    </div>
</div>
```

## Langkah 7: Topbar

Tampilkan:

```text
Judul halaman
Nama user
Role
Unit
Notifikasi
Logout
```

## Langkah 8: Vite Anti FOUC

`vite.config.js`:

```javascript
input: [
    'resources/css/app.css',
    'resources/js/app.js',
],
```

Pada Blade:

```blade
@vite([
    'resources/css/app.css',
    'resources/js/app.js',
])
```

Jika sebelumnya ada:

```javascript
import '../css/app.css';
```

di `app.js`, hapus karena CSS sudah menjadi entry langsung.

## Langkah 9: Build

```bash
rm -f public/hot
npm run build
php artisan optimize:clear
```

## Langkah 10: Login

Gunakan layout khusus login dengan:

- logo besar;
- judul SIKERJA;
- subjudul;
- panel hijau gelap;
- form putih;
- aksen emas.

## Pengujian Manual

1. Login Admin.
2. Pindah banyak menu.
3. Pastikan tidak ada flash HTML polos.
4. Resize browser.
5. Uji mobile offcanvas.
6. Login Pimpinan.
7. Login Personel.
8. Pastikan menu sesuai role.

## Kesalahan Umum

### Flash tanpa CSS

CSS masih dimuat melalui JS saja. Gunakan CSS sebagai entry Vite langsung.

### Blank hijau

Jangan menggunakan fallback style full-screen untuk menutup masalah asset.

### Offcanvas tidak berfungsi

Pastikan Bootstrap JavaScript aktif.

## Penjelasan untuk Pemula

Pada modul ini kita tidak mengubah logika sistem.

Kita hanya mengubah **presentation layer** atau tampilan.

Penting memisahkan:

```text
Logika Backend
```

dari:

```text
Desain Frontend
```

Dengan pemisahan ini, kita dapat mengganti warna, sidebar, logo, dan card tanpa merusak database atau controller.

### Apa itu Responsive?

Responsive berarti tampilan menyesuaikan ukuran layar.

Contoh:

```text
Desktop → Sidebar tetap
Mobile  → Offcanvas
```

## Penjelasan Gamblang: Redesign UI Ini Untuk Apa?

### CSS Variables
Menyimpan warna utama di satu tempat agar mudah diganti.

### Sidebar
Navigasi utama desktop.

### Offcanvas
Versi sidebar untuk layar kecil/mobile.

### Partial Blade
Mencegah kode menu ditulis dua kali.

### `request()->routeIs(...)`
Mengetahui menu mana yang sedang aktif.

### Vite Entry
Menentukan file CSS/JS yang harus dibangun Vite.

### Kenapa CSS dibuat entry sendiri?
Agar browser dapat memuat stylesheet lebih cepat dan mengurangi flash halaman tanpa gaya.

### Kenapa redesign tidak menyentuh controller/database?
Karena tampilan seharusnya terpisah dari logika bisnis.

## Checklist

- [ ] Theme
- [ ] Login
- [ ] Sidebar
- [ ] Topbar
- [ ] Logo
- [ ] Menu role
- [ ] Mobile
- [ ] Card
- [ ] Table
- [ ] FOUC hilang

## Simpan Checkpoint Git

```bash
git add .
git commit -m "Redesign SIKERJA modern military executive"
```

## Modul Berikutnya

Modul 19 membahas Testing dan Quality Assurance.
