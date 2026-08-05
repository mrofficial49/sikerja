# Changelog SIKERJA

Semua perubahan penting pada aplikasi SIKERJA dicatat dalam dokumen ini.

Format versi mengikuti pola:

```text
MAJOR.MINOR.PATCH
```

- **MAJOR**: perubahan besar atau perubahan yang tidak kompatibel.
- **MINOR**: penambahan fitur yang tetap kompatibel.
- **PATCH**: perbaikan bug atau penyempurnaan kecil.

---

## [1.1.0] — 4 Agustus 2026

### Added

- Ekspor rekap hasil kinerja WFH ke PDF.
- Route PDF untuk Admin.
- Route PDF untuk Pimpinan.
- Controller `MonitoringPdfController`.
- Tampilan PDF monitoring.
- Ringkasan statistik dalam dokumen PDF.
- Rincian pekerjaan Personel pada PDF.
- Status laporan pada PDF.
- Catatan verifikasi pada PDF.
- Kolom pengesahan.
- Nomor halaman PDF.
- Logo SIKERJA pada PDF.
- Bukti foto dan GPS presensi.
- Halaman khusus bukti presensi.
- Route streaming foto privat.
- Tombol **Foto & GPS** pada monitoring.
- Dokumentasi README yang diperbarui.
- Panduan pengguna.
- Panduan presentasi.
- Panduan deployment.
- Changelog proyek.

### Changed

- Layout utama diubah dari navbar standar menjadi sidebar.
- Topbar diperbarui.
- Navigasi mobile menggunakan offcanvas.
- Halaman login menggunakan tema Modern Military Executive.
- Identitas warna menggunakan hijau, putih, dan emas.
- Logo SIKERJA menggantikan huruf “S”.
- Logo diperbesar agar menjadi identitas utama.
- Kartu, tabel, form, badge, dan tombol diperbarui.
- CSS dijadikan entry langsung pada Vite.
- Pemuatan CSS diperbaiki agar navigasi lebih stabil.
- Monitoring menampilkan tombol bukti foto dan GPS.
- README bawaan Laravel diganti dengan dokumentasi SIKERJA.

### Security

- Foto presensi disimpan pada storage privat.
- File foto tidak disajikan langsung melalui URL publik.
- Akses foto diperiksa berdasarkan role dan kepemilikan.
- Route PDF dilindungi autentikasi.
- Route PDF memeriksa status akun.
- Route PDF memeriksa perubahan password.
- Route PDF dibatasi untuk Admin dan Pimpinan.
- Disk local menggunakan `serve => false`.

### Dependencies

Ditambahkan:

```text
barryvdh/laravel-dompdf
```

### Fixed

- Memperbaiki error route yang memanggil method `download()` yang belum tersedia.
- Memperbaiki instalasi DOMPDF yang sebelumnya belum terpasang.
- Memperbaiki pemuatan CSS yang sempat menampilkan flash tanpa style.
- Memperbaiki blank hijau akibat style pengaman pada `<head>`.
- Memperbaiki tampilan logo yang kurang menonjol.
- Memperbaiki akses bukti foto dan GPS yang sebelumnya belum tersedia.

---

## [1.0.0] — Rilis Awal

### Added

#### Autentikasi

- Login menggunakan ID Login.
- Logout.
- Middleware autentikasi.
- Middleware akun aktif.
- Middleware perubahan password.
- Middleware role.

#### Role

- Admin.
- Pimpinan.
- Personel.

#### Pengguna dan Unit

- CRUD unit kerja.
- CRUD pengguna.
- Aktivasi dan nonaktivasi pengguna.
- Reset password.
- Relasi pengguna dengan unit dan role.

#### Jadwal WFH

- Pembuatan jadwal.
- Pengelolaan status jadwal.
- Penambahan anggota jadwal.
- Pembatalan anggota.
- Perubahan status anggota.

#### Presensi

- Check-in.
- Check-out.
- Foto check-in.
- Foto check-out.
- Latitude dan longitude.
- Alasan keterlambatan.
- Status kehadiran.

#### Pekerjaan

- Rencana kerja pribadi.
- Tugas Pimpinan.
- Target hasil.
- Progres.
- Kendala.
- Rencana tindak lanjut.
- Status pekerjaan.
- Lanjutan pekerjaan secara luring.
- Pembatalan pekerjaan.

#### Bukti Pekerjaan

- Upload bukti PDF.
- Penyimpanan file pada storage privat.
- Relasi file dengan pekerjaan.

#### Laporan

- Draft laporan.
- Pengiriman laporan.
- Verifikasi.
- Persetujuan.
- Permintaan revisi.
- Pengiriman ulang.
- Catatan verifikasi.
- Penguncian laporan setelah check-out.

#### Monitoring

- Filter jadwal.
- Filter unit.
- Pencarian Personel.
- Ringkasan presensi.
- Ringkasan status laporan.
- Daftar Personel.
- Pagination.
- Akses detail laporan.

#### Notifikasi

- Notifikasi jadwal.
- Notifikasi tugas.
- Notifikasi verifikasi.
- Notifikasi revisi.
- Notifikasi persetujuan.
- Status dibaca dan belum dibaca.

#### Dashboard

- Dashboard Admin.
- Dashboard Pimpinan.
- Dashboard Personel.

#### Keamanan

- Pembatasan route berdasarkan role.
- File privat.
- Halaman error khusus.
- Pengujian otomatis hak akses.

#### Data dan Pemeliharaan

- DemoSeeder.
- Akun demo.
- Skenario demo.
- Backup database.
- Backup file privat.
- Dokumentasi awal.

---

## Rencana Pengembangan

Fitur berikut dapat dipertimbangkan pada versi selanjutnya:

### Versi 1.2.0

- Export Excel.
- Rekap berdasarkan rentang tanggal.
- Rekap per unit.
- Laporan per Personel.
- Grafik statistik.
- Filter status pekerjaan.
- Filter status laporan.
- Tanda tangan digital.
- QR Code verifikasi dokumen PDF.

### Versi 1.3.0

- Audit log yang lebih lengkap.
- Pengaturan retention file.
- Penghapusan file otomatis sesuai masa simpan.
- Dashboard analitik.
- Notifikasi email.
- Notifikasi WhatsApp melalui layanan resmi.
- Multi-level approval.

### Versi 2.0.0

- Pengembangan SIKERJA menjadi sistem kinerja harian.
- Dukungan tugas luar dan kerja lapangan.
- Integrasi aplikasi mobile.
- Integrasi Single Sign-On.
- Integrasi sistem kepegawaian.
- API untuk aplikasi lain.

---

## Catatan Rilis

Sebelum membuat rilis:

```bash
php artisan optimize:clear
php artisan test
npm run build
git status
```

Commit:

```bash
git add .
git commit -m "Finalisasi SIKERJA"
git push
```

Tag versi:

```bash
git tag -a v1.1.0 \
-m "SIKERJA v1.1.0 redesign dan ekspor rekap PDF"

git push origin v1.1.0
```
