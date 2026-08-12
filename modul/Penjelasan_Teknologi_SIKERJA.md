# Penjelasan Teknologi yang Digunakan pada Project SIKERJA

Dokumen ini menjelaskan teknologi yang digunakan pada project **SIKERJA** dengan bahasa sederhana agar mudah dipahami oleh orang yang sedang belajar membuat website.

---

## 1. Laravel 12

**Laravel 12 adalah framework PHP untuk membuat bagian backend website.**

Framework dapat dibayangkan sebagai **kerangka bangunan yang sudah menyediakan banyak bagian penting**, sehingga programmer tidak perlu membuat semuanya dari nol.

Laravel membantu mengatur:

- login;
- route/URL;
- database;
- validasi form;
- hak akses;
- session;
- upload file;
- controller;
- model;
- keamanan.

Contoh sederhana:

```php
Route::get('/dashboard', function () {
    return view('dashboard');
});
```

Artinya:

```text
Ketika browser membuka /dashboard
↓
Laravel menerima request
↓
Laravel menampilkan halaman dashboard
```

Jadi, **Laravel adalah kerangka utama aplikasi**.

---

## 2. PHP Minimal 8.2

**PHP adalah bahasa pemrograman backend yang digunakan Laravel.**

Kalau HTML membuat tampilan, PHP melakukan proses di server.

Contoh:

```php
$nama = "Dwi";

echo $nama;
```

Hasil:

```text
Dwi
```

Dalam aplikasi sebenarnya PHP digunakan untuk:

```text
Menerima form
↓
Memvalidasi data
↓
Mengambil database
↓
Menyimpan database
↓
Menentukan hak akses
↓
Mengirim hasil ke browser
```

Kenapa disebut **minimal PHP 8.2**?

Karena Laravel versi modern membutuhkan fitur PHP versi tertentu.

Cek versi PHP:

```bash
php -v
```

Hubungannya:

```text
Laravel
   ↓
dibangun menggunakan
   ↓
PHP
```

Jadi **Laravel bukan pengganti PHP**. Laravel justru dibuat menggunakan PHP.

---

## 3. MariaDB atau MySQL

**MySQL/MariaDB adalah sistem database.**

Database digunakan untuk menyimpan data secara permanen.

Contohnya aplikasi SIKERJA mempunyai tabel seperti:

```text
users
units
roles
attendances
work_reports
```

Contoh isi tabel `users`:

```text
id | name          | login_id | role_id
1  | Administrator | ADMIN01  | 1
2  | Budi          | 123456   | 3
```

Tanpa database, aplikasi tidak memiliki tempat penyimpanan data yang terstruktur.

### MySQL dan MariaDB bedanya apa?

Keduanya sangat mirip dan sama-sama menggunakan bahasa SQL.

Contoh SQL:

```sql
SELECT * FROM users;
```

Artinya:

> Ambil seluruh data dari tabel `users`.

Dalam Laravel, programmer biasanya tidak perlu menulis SQL terus-menerus karena tersedia **Eloquent ORM**.

---

## 4. Blade Template

**Blade adalah template engine bawaan Laravel untuk membuat tampilan HTML dinamis.**

File Blade biasanya berada di:

```text
resources/views/
```

Contoh:

```blade
<h1>
    Selamat datang, {{ $user->name }}
</h1>
```

Misalnya nama user:

```text
Budi
```

maka browser akan menampilkan:

```text
Selamat datang, Budi
```

Blade juga dapat membuat kondisi.

```blade
@if ($user->is_active)
    <span>Akun Aktif</span>
@else
    <span>Akun Nonaktif</span>
@endif
```

Dan perulangan:

```blade
@foreach ($users as $user)
    <p>{{ $user->name }}</p>
@endforeach
```

Jadi:

```text
HTML biasa
+
Data Laravel
+
Perintah sederhana
=
Blade
```

---

## 5. Bootstrap 5

**Bootstrap adalah framework CSS untuk mempercepat pembuatan tampilan website.**

Tanpa Bootstrap, kita harus membuat CSS komponen sendiri.

Misalnya:

```css
.tombol {
    background: green;
    color: white;
    padding: 10px;
    border-radius: 5px;
}
```

Dengan Bootstrap cukup:

```html
<button class="btn btn-success">
    Simpan
</button>
```

Bootstrap sudah menyediakan banyak komponen:

```text
Button
Navbar
Card
Form
Table
Modal
Alert
Dropdown
Offcanvas
Grid
Responsive Layout
```

Contoh Card:

```html
<div class="card">
    <div class="card-body">
        <h5 class="card-title">
            Data Pengguna
        </h5>
    </div>
</div>
```

Jadi Bootstrap lebih banyak digunakan untuk **tampilan/frontend**.

---

## 6. JavaScript

**JavaScript digunakan untuk membuat halaman website menjadi interaktif.**

Secara sederhana:

```text
HTML       = Struktur
CSS        = Tampilan
JavaScript = Interaksi
```

Contoh:

```javascript
alert('Selamat datang');
```

Akan menampilkan popup pada browser.

Contoh lain:

```javascript
document
    .getElementById('tombol')
    .addEventListener('click', function () {
        alert('Tombol diklik');
    });
```

Dalam aplikasi modern JavaScript sering digunakan untuk:

```text
Kamera
GPS
Modal
Konfirmasi
Dropdown
Preview foto
Validasi frontend
AJAX
Interaksi tanpa reload
```

Contoh meminta GPS:

```javascript
navigator.geolocation.getCurrentPosition(
    function (position) {
        console.log(
            position.coords.latitude
        );
    }
);
```

Jadi JavaScript bekerja terutama **di browser pengguna**.

---

## 7. Vite

**Vite bukan bahasa pemrograman.**

Vite adalah **tool untuk mengelola dan membangun file frontend**, terutama:

```text
CSS
JavaScript
Bootstrap
```

Misalnya project mempunyai:

```text
resources/css/app.css
resources/js/app.js
```

Laravel memanggilnya:

```blade
@vite([
    'resources/css/app.css',
    'resources/js/app.js',
])
```

Saat development jalankan:

```bash
npm run dev
```

Untuk production:

```bash
npm run build
```

Alurnya:

```text
CSS + JavaScript
       ↓
      Vite
       ↓
File siap digunakan browser
```

Jadi sederhananya:

> **Vite adalah mesin pengolah asset frontend.**

---

## 8. Eloquent ORM

**Eloquent ORM adalah cara Laravel berkomunikasi dengan database menggunakan Model PHP.**

ORM berarti:

```text
Object Relational Mapping
```

Tanpa Eloquent kita mungkin menulis SQL:

```sql
SELECT * FROM users
WHERE is_active = 1;
```

Dengan Eloquent:

```php
$users = User::where(
    'is_active',
    true
)->get();
```

### Mengambil satu user

```php
$user = User::find(1);
```

Artinya:

> Cari user dengan ID 1.

### Menambah data

```php
User::create([
    'name' => 'Budi',
    'login_id' => '12345',
]);
```

### Mengubah data

```php
$user->update([
    'name' => 'Budi Santoso',
]);
```

### Mengakses relasi

Misalnya User memiliki Unit:

```php
$user->unit;
```

Jadi alurnya:

```text
Laravel
   ↓
Model
   ↓
Eloquent
   ↓
MySQL / MariaDB
```

---

## 9. Laravel Middleware

**Middleware adalah penjaga sebelum pengguna masuk ke halaman tertentu.**

Bayangkan seperti petugas pemeriksa pintu masuk.

Misalnya user membuka:

```text
/admin/users
```

Middleware memeriksa:

```text
Sudah login?
↓
Akun aktif?
↓
Password sudah diganti?
↓
Apakah role Admin?
↓
Ya
↓
Silakan masuk
```

Contoh route:

```php
Route::middleware([
    'auth',
    'role:Admin',
])->group(function () {

    Route::get(
        '/admin/users',
        ...
    );

});
```

Jika Personel mencoba membuka URL Admin:

```text
Personel
↓
/admin/users
↓
Middleware
↓
Role bukan Admin
↓
403 Forbidden
```

Middleware sangat penting untuk **keamanan dan pembatasan hak akses**.

---

## 10. PHPUnit

**PHPUnit adalah alat untuk melakukan testing otomatis pada kode PHP.**

Laravel menggunakan PHPUnit sebagai salah satu fondasi pengujian.

Misalnya kita ingin memastikan user yang belum login tidak bisa membuka dashboard:

```php
public function test_guest_cannot_open_dashboard()
{
    $response =
        $this->get('/dashboard');

    $response->assertRedirect(
        '/login'
    );
}
```

Artinya komputer otomatis menguji:

```text
User belum login
↓
Buka dashboard
↓
Apakah diarahkan ke login?
```

Kalau benar:

```text
PASS
```

Kalau salah:

```text
FAIL
```

Menjalankan seluruh test:

```bash
php artisan test
```

Testing berguna untuk mengetahui apakah perubahan baru merusak fitur yang sudah ada.

---

## 11. Laravel DOMPDF

**Laravel DOMPDF adalah package untuk mengubah HTML/Blade menjadi PDF.**

Contohnya terdapat data:

```text
Nama Personel
Tanggal
Check-in
Check-out
Pekerjaan
Status laporan
```

Data tersebut dikirim ke Blade khusus PDF:

```text
monitoring/pdf.blade.php
```

Kemudian DOMPDF mengubahnya menjadi:

```text
rekap-kinerja.pdf
```

Contoh:

```php
use Barryvdh\DomPDF\Facade\Pdf;

$pdf = Pdf::loadView(
    'monitoring.pdf',
    compact('members')
);

return $pdf->download(
    'rekap-kinerja.pdf'
);
```

Alurnya:

```text
Database
↓
Laravel Controller
↓
Blade PDF
↓
DOMPDF
↓
File PDF
```

Jadi DOMPDF digunakan untuk **membuat laporan dan rekap aplikasi menjadi PDF**.

---

## 12. Git

**Git adalah Version Control System.**

Fungsinya menyimpan riwayat perubahan source code.

Bayangkan seperti:

```text
Save Point
```

pada game.

Contoh:

```bash
git add .
git commit -m "Selesaikan fitur login"
```

Artinya:

> Simpan kondisi source code saat fitur login selesai.

Kemudian setelah membuat fitur lain:

```bash
git add .
git commit -m "Tambah presensi GPS"
```

Riwayat menjadi:

```text
Commit 1
Project awal
   ↓
Commit 2
Login selesai
   ↓
Commit 3
CRUD user selesai
   ↓
Commit 4
Presensi selesai
```

Git membantu melihat perubahan dan kembali ke versi sebelumnya jika terjadi masalah.

### Git tidak sama dengan GitHub

```text
Git
=
Sistem Version Control

GitHub
=
Layanan online untuk menyimpan repository Git
```

---

# Cara Semua Teknologi Bekerja Bersama

Alur utama aplikasi:

```text
PENGGUNA
   ↓
Browser
   ↓
HTML + Blade
Bootstrap + JavaScript
   ↓
Route Laravel
   ↓
Middleware
   ↓
Controller
   ↓
Model
   ↓
Eloquent ORM
   ↓
MySQL / MariaDB
   ↓
Data kembali ke Controller
   ↓
Blade
   ↓
Browser
```

Sedangkan tool pendukung:

```text
Vite
→ mengelola CSS dan JavaScript

PHPUnit
→ menguji aplikasi

DOMPDF
→ membuat laporan PDF

Git
→ menyimpan riwayat source code
```

---

# Jawaban Singkat untuk Presentasi

Jika instruktur bertanya:

> **“Teknologi apa saja yang digunakan dalam SIKERJA?”**

Jawaban yang dapat digunakan:

> “Backend aplikasi menggunakan **PHP dengan framework Laravel 12**, database menggunakan **MySQL/MariaDB**, frontend menggunakan **Blade Template, Bootstrap 5, dan JavaScript**, asset frontend dikelola menggunakan **Vite**, komunikasi database menggunakan **Eloquent ORM**, keamanan hak akses menggunakan **Laravel Middleware**, pengujian menggunakan **PHPUnit**, pembuatan laporan menggunakan **Laravel DOMPDF**, dan source code dikelola menggunakan **Git**.”

---

# Ringkasan Fungsi

| Teknologi | Fungsi Utama |
|---|---|
| Laravel 12 | Framework utama backend |
| PHP 8.2+ | Bahasa pemrograman backend |
| MariaDB/MySQL | Database |
| Blade Template | Membuat tampilan dinamis |
| Bootstrap 5 | Mempercepat desain frontend |
| JavaScript | Interaksi di browser |
| Vite | Mengolah asset CSS/JS |
| Eloquent ORM | Menghubungkan Laravel dengan database |
| Laravel Middleware | Keamanan dan pembatasan akses |
| PHPUnit | Automated testing |
| Laravel DOMPDF | Membuat file PDF |
| Git | Version control source code |
