# Panduan Presentasi SIKERJA

## 1. Tujuan Presentasi

Presentasi bertujuan menjelaskan bahwa SIKERJA dapat digunakan untuk mengelola pelaksanaan WFH secara terukur, terdokumentasi, aman, dan mudah dipantau.

Fokus paparan:

- Penjadwalan WFH.
- Presensi foto dan GPS.
- Penugasan.
- Pelaporan hasil kerja.
- Verifikasi.
- Monitoring.
- Ekspor rekap PDF.

---

## 2. Persiapan Sebelum Presentasi

### Periksa layanan

Pastikan:

- MySQL atau MariaDB berjalan.
- Laravel berjalan.
- Vite atau hasil build tersedia.
- Kamera berfungsi.
- Lokasi browser aktif.
- Internet tersedia untuk membuka Google Maps.

### Jalankan pemeriksaan

```bash
php artisan optimize:clear
php artisan test
php artisan route:list --name=monitoring.pdf
composer show barryvdh/laravel-dompdf
```

### Siapkan data demo

```bash
php artisan db:seed --class=DemoSeeder
```

### Jalankan aplikasi

Terminal pertama:

```bash
npm run dev
```

Terminal kedua:

```bash
php artisan serve
```

Buka:

```text
http://127.0.0.1:8000
```

---

## 3. Akun Presentasi

Password seluruh akun demo:

```text
DemoSikerja#2026
```

| Role | ID Login |
|---|---|
| Admin | `DEMOADMIN` |
| Pimpinan | `DEMOPIMPINAN` |
| Personel | `DEMOPER001` |

Akun alternatif:

| Akun | Skenario |
|---|---|
| `DEMOPER001` | Menunggu verifikasi |
| `DEMOPER002` | Perlu revisi |
| `DEMOPER003` | Sudah disetujui |
| `DEMOPER004` | Pekerjaan berlangsung |
| `DEMOPER005` | Belum check-in |

---

## 4. Susunan Paparan

### Bagian 1 — Latar Belakang

Jelaskan:

> Pelaksanaan WFH memerlukan sistem yang tidak hanya mencatat kehadiran, tetapi juga memastikan tugas, hasil kerja, bukti kegiatan, dan proses verifikasi dapat dipantau secara terintegrasi.

### Bagian 2 — Solusi

Jelaskan:

> SIKERJA mengintegrasikan penjadwalan, presensi foto dan GPS, penugasan, pelaporan, monitoring, notifikasi, serta rekap PDF dalam satu aplikasi.

### Bagian 3 — Demo

Urutan demo:

1. Login Admin.
2. Tampilkan pengelolaan jadwal.
3. Tampilkan monitoring.
4. Tampilkan bukti foto dan GPS.
5. Ekspor rekap PDF.
6. Login Pimpinan.
7. Tampilkan penugasan dan verifikasi.
8. Login Personel.
9. Tampilkan presensi, pekerjaan, laporan, dan notifikasi.

---

## 5. Demo Admin

### Langkah 1 — Login

Gunakan:

```text
ID Login : DEMOADMIN
Password : DemoSikerja#2026
```

### Narasi

> Admin memiliki kewenangan untuk mengelola pengguna, unit kerja, jadwal WFH, anggota jadwal, monitoring, verifikasi laporan, dan ekspor rekap.

### Langkah 2 — Dashboard

Tunjukkan:

- Ringkasan pengguna.
- Ringkasan jadwal.
- Ringkasan presensi.
- Ringkasan laporan.

### Langkah 3 — Pengguna dan Unit

Tunjukkan menu:

- Unit Kerja.
- Pengguna.

Jelaskan bahwa data pengguna dikelompokkan berdasarkan unit dan role.

### Langkah 4 — Jadwal WFH

Tunjukkan:

- Daftar jadwal.
- Status jadwal.
- Anggota jadwal.
- Aktivasi jadwal.

### Narasi

> Jadwal dibuat oleh Admin dan hanya Personel yang terdaftar pada jadwal yang dapat melaksanakan presensi WFH.

### Langkah 5 — Monitoring

Buka **Monitoring & Rekap**.

Tunjukkan:

- Filter jadwal.
- Filter unit.
- Pencarian Personel.
- Statistik check-in.
- Statistik check-out.
- Status laporan.
- Jumlah pekerjaan.

### Langkah 6 — Foto dan GPS

Tekan **Foto & GPS**.

Tunjukkan:

- Foto check-in.
- Waktu check-in.
- Koordinat check-in.
- Foto check-out.
- Waktu check-out.
- Koordinat check-out.
- Tautan Google Maps.

### Narasi

> Foto dan lokasi tidak disimpan pada folder publik. File hanya dapat dibuka oleh pengguna yang memiliki hak akses.

### Langkah 7 — Ekspor PDF

Tekan **Ekspor Rekap PDF**.

Buka file hasil unduhan.

Tunjukkan:

- Logo SIKERJA.
- Informasi jadwal.
- Ringkasan presensi.
- Rincian hasil kinerja.
- Status laporan.
- Kolom pengesahan.
- Nomor halaman.

### Narasi

> Data PDF mengikuti filter monitoring, sehingga dapat digunakan untuk mencetak rekap per jadwal, unit, atau Personel tertentu.

---

## 6. Demo Pimpinan

### Login

```text
ID Login : DEMOPIMPINAN
Password : DemoSikerja#2026
```

### Langkah 1 — Dashboard Pimpinan

Tunjukkan ringkasan Personel, tugas, dan laporan.

### Langkah 2 — Tugas Personel

Buka **Tugas Personel**.

Tunjukkan:

- Pilihan jadwal.
- Pilihan Personel.
- Judul tugas.
- Uraian.
- Target hasil.

### Narasi

> Pimpinan dapat memberikan tugas secara langsung kepada Personel yang terjadwal WFH.

### Langkah 3 — Monitoring

Tunjukkan bahwa Pimpinan dapat memantau:

- Presensi.
- Pekerjaan.
- Laporan.
- Bukti foto dan GPS.

### Langkah 4 — Verifikasi

Buka laporan yang menunggu verifikasi.

Tunjukkan:

- Hasil pekerjaan.
- Progres.
- Kendala.
- Bukti PDF.
- Tombol setujui.
- Tombol minta revisi.

### Narasi

> Hasil pemeriksaan disampaikan kepada Personel melalui status laporan dan notifikasi.

---

## 7. Demo Personel

### Login

```text
ID Login : DEMOPER001
Password : DemoSikerja#2026
```

### Langkah 1 — Dashboard Personel

Tunjukkan:

- Jadwal aktif.
- Status presensi.
- Tugas.
- Status laporan.
- Notifikasi.

### Langkah 2 — Presensi

Buka menu **Presensi WFH**.

Jelaskan:

- Kamera digunakan untuk foto.
- GPS digunakan untuk lokasi.
- Waktu disimpan otomatis.
- Alasan terlambat dicatat bila diperlukan.

### Langkah 3 — Pekerjaan

Buka menu **Pekerjaan**.

Tunjukkan:

- Rencana pribadi.
- Tugas Pimpinan.
- Status pekerjaan.
- Progres.
- Kendala.
- Tindak lanjut.
- Upload bukti.

### Langkah 4 — Laporan

Buka **Laporan & Check-out**.

Tunjukkan:

- Ringkasan pekerjaan.
- Tombol kirim laporan.
- Status verifikasi.
- Proses check-out.

### Langkah 5 — Notifikasi

Tunjukkan pusat notifikasi.

Jelaskan jenis notifikasi yang tersedia.

---

## 8. Skenario Revisi

Login menggunakan:

```text
DEMOPER002
```

Tunjukkan:

- Status perlu revisi.
- Catatan pemeriksa.
- Proses memperbaiki pekerjaan.
- Pengiriman ulang laporan.

---

## 9. Skenario Persetujuan

Login menggunakan:

```text
DEMOPER003
```

Tunjukkan laporan yang sudah disetujui.

Jelaskan bahwa status akhir menjadi bagian dari rekap monitoring.

---

## 10. Kalimat Paparan Siap Pakai

### Pembukaan

> SIKERJA merupakan Sistem Informasi Kinerja dan Aktivitas Personel yang dirancang untuk mendukung pelaksanaan WFH secara terukur, transparan, dan terdokumentasi.

### Presensi

> Presensi tidak hanya mencatat waktu, tetapi juga dilengkapi foto dan koordinat GPS sebagai bukti pelaksanaan.

### Pekerjaan

> Setiap Personel dapat menyusun rencana kerja pribadi dan menerima tugas langsung dari Pimpinan.

### Verifikasi

> Hasil kerja diperiksa secara berjenjang. Pimpinan atau Admin dapat menyetujui laporan atau meminta perbaikan disertai catatan yang jelas.

### Monitoring

> Monitoring menyajikan status presensi, jumlah pekerjaan, status laporan, serta rincian setiap Personel dalam satu halaman.

### PDF

> Rekap dapat diekspor ke PDF untuk digunakan sebagai bahan evaluasi, dokumentasi, dan pertanggungjawaban pelaksanaan WFH.

### Penutup

> Dengan SIKERJA, pelaksanaan WFH tetap dapat dipantau berdasarkan kehadiran, tugas, hasil kerja, dan proses verifikasi yang terdokumentasi.

---

## 11. Pertanyaan yang Mungkin Muncul

### Bagaimana bila kebijakan WFH dihentikan?

SIKERJA dapat dikembangkan menjadi sistem monitoring kinerja harian, tugas luar, kerja lapangan, atau pelaporan aktivitas Personel.

### Apakah foto dapat diakses publik?

Tidak. Foto disimpan pada storage privat dan hanya dapat dibuka melalui controller yang memeriksa autentikasi dan hak akses.

### Apakah Personel dapat melihat data Personel lain?

Tidak. Akses dibatasi berdasarkan role dan kepemilikan data.

### Apakah rekap dapat dicetak?

Ya. Admin dan Pimpinan dapat mengekspor rekap kinerja ke PDF.

### Apakah aplikasi dapat digunakan melalui ponsel?

Ya. Tampilan responsif dan sidebar mobile menggunakan offcanvas.

### Apakah sistem memiliki pengujian?

Ya. Terdapat pengujian otomatis untuk hak akses dan beberapa fitur penting.

---

## 12. Checklist Hari Presentasi

- [ ] MySQL/MariaDB aktif.
- [ ] Laravel berjalan.
- [ ] Vite berjalan atau aset sudah dibuild.
- [ ] Data demo tersedia.
- [ ] Akun demo berhasil login.
- [ ] Kamera mendapat izin.
- [ ] Lokasi browser aktif.
- [ ] Internet tersedia untuk Google Maps.
- [ ] Ekspor PDF berhasil.
- [ ] Backup database tersedia.
- [ ] Browser sudah dibersihkan dari tab yang tidak diperlukan.
- [ ] File PDF contoh sudah disiapkan.
- [ ] Script paparan sudah dipelajari.

---

## 13. Urutan Demo Singkat

Untuk presentasi singkat:

1. Login Admin.
2. Buka Monitoring.
3. Tampilkan Foto & GPS.
4. Ekspor PDF.
5. Login Pimpinan.
6. Tampilkan tugas dan verifikasi.
7. Login Personel.
8. Tampilkan pekerjaan dan laporan.
9. Sampaikan manfaat.
