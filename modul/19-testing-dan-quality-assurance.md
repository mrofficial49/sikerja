# Modul 19: Testing dan Quality Assurance

## Tujuan

Membuat pengujian otomatis untuk memastikan fitur keamanan dan hak akses tidak rusak setelah perubahan kode.

## Hasil Akhir

```bash
php artisan test
```

berjalan dengan hasil PASS.

## Struktur File

```text
tests/Feature/
phpunit.xml
```

## Langkah 1: Buat Test

```bash
php artisan make:test AccessControlTest
```

## Langkah 2: RefreshDatabase

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;
}
```

## Langkah 3: Test Guest

```php
public function test_guest_cannot_open_dashboard(): void
{
    $response =
        $this->get('/dashboard');

    $response->assertRedirect(
        '/login'
    );
}
```

## Langkah 4: Test Role

Contoh Personel membuka halaman Admin:

```php
public function test_personnel_cannot_open_admin_page(): void
{
    $personnel =
        $this->createUserWithRole(
            'Personel'
        );

    $response = $this
        ->actingAs($personnel)
        ->get(
            route(
                'admin.users.index'
            )
        );

    $response->assertForbidden();
}
```

Helper `createUserWithRole()` dapat dibuat di class test untuk mempercepat setup.

## Langkah 5: Test Akun Nonaktif

```php
$user = $this->createUserWithRole(
    'Personel',
    [
        'is_active' => false,
    ]
);

$this
    ->actingAs($user)
    ->get('/dashboard')
    ->assertRedirect('/login');
```

## Langkah 6: Storage Fake

```php
Storage::fake('local');
```

Gunakan untuk evidence dan upload agar test tidak menyentuh file asli.

## Langkah 7: Test Evidence

Skenario:

```text
Admin            200
Pimpinan         200
Pemilik          200
Personel lain    403
Guest            redirect
File hilang      404
```

## Langkah 8: Test PDF

Uji:

```text
Admin boleh
Pimpinan boleh
Personel dilarang
Guest redirect
```

## Langkah 9: Jalankan

```bash
php artisan optimize:clear
php artisan test
```

## Quality Assurance Manual

Automated test tidak menggantikan seluruh pengujian manual.

Tetap uji:

- permission kamera;
- GPS;
- responsive;
- PDF secara visual;
- Google Maps;
- pengalaman login/logout.

## Kesalahan Umum

### Test tergantung data database lokal

Test harus membuat datanya sendiri.

### Foreign key fail

Urutan pembuatan model/factory salah.

### File asli terhapus

Gunakan `Storage::fake('local')`.

## Penjelasan untuk Pemula

Testing otomatis adalah cara meminta komputer memeriksa aplikasi.

Contoh pertanyaan yang diuji:

```text
Apakah Personel bisa membuka halaman Admin?
```

Test harus menjawab:

```text
Tidak → 403
```

### Kenapa Testing Penting?

Saat kita memperbaiki fitur A, kita bisa tanpa sengaja merusak fitur B.

Automated test membantu mengetahui masalah tersebut lebih cepat.

### `RefreshDatabase`

Digunakan agar test memakai kondisi database yang bersih dan konsisten.

## Penjelasan Gamblang: Testing Ini Untuk Apa?

### Feature Test
Menguji perilaku aplikasi dari sudut pandang request HTTP.

### `actingAs($user)`
Mensimulasikan user sedang login.

### `assertForbidden()`
Memastikan response adalah 403.

### `assertRedirect('/login')`
Memastikan guest diarahkan login.

### `RefreshDatabase`
Membuat kondisi database test bersih dan konsisten.

### `Storage::fake('local')`
Membuat storage palsu saat test supaya file asli tidak tersentuh.

### Kenapa test penting?
Supaya perubahan fitur baru tidak merusak keamanan/fitur lama tanpa kita sadari.

## Checklist

- [ ] Guest test
- [ ] Admin test
- [ ] Pimpinan test
- [ ] Personel test
- [ ] Active user test
- [ ] Evidence test
- [ ] PDF test
- [ ] Semua PASS

## Simpan Checkpoint Git

```bash
git add .
git commit -m "Tambah automated test SIKERJA"
```

## Modul Berikutnya

Modul 20 membahas Backup, Restore, dan Reset Demo.
