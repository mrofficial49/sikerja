# Panduan Presentasi SIKERJA

## Persiapan

Jalankan:

```bash
composer install
npm install
php artisan migrate
php artisan db:seed
php artisan db:seed --class=DemoSeeder
php artisan optimize:clear
php artisan test
```

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

## Akun Presentasi

| Role | ID Login | Password |
|---|---|---|
| Admin | `DEMOADMIN` | `DemoSikerja#2026` |
| Pimpinan | `DEMOPIMPINAN` | `DemoSikerja#2026` |
| Personel | `DEMOPER001` | `DemoSikerja#2026` |

## Urutan Demonstrasi

### 1. Gambaran Umum

Jelaskan bahwa SIKERJA mengintegrasikan:

- Penjadwalan WFH.
- Presensi foto dan GPS.
- Rencana kerja.
- Penugasan Pimpinan.
- Pelaporan.
- Verifikasi.
- Monitoring.

### 2. Demo Admin

1. Login sebagai `DEMOADMIN`.
2. Tampilkan dashboard Admin.
3. Buka data pengguna.
4. Buka jadwal WFH.
5. Buka Monitoring & Rekap.
6. Tampilkan foto serta GPS presensi.
7. Buka laporan Personel.

### 3. Demo Pimpinan

1. Logout dari Admin.
2. Login sebagai `DEMOPIMPINAN`.
3. Buka dashboard Pimpinan.
4. Tampilkan daftar tugas.
5. Berikan tugas kepada Personel.
6. Buka Monitoring & Rekap.
7. Buka laporan menunggu verifikasi.
8. Tunjukkan tombol persetujuan dan revisi.

### 4. Demo Personel

1. Logout dari Pimpinan.
2. Login sebagai `DEMOPER001`.
3. Tampilkan dashboard Personel.
4. Tampilkan status jadwal.
5. Buka daftar pekerjaan.
6. Tampilkan tugas Pimpinan dan rencana pribadi.
7. Buka laporan.
8. Tampilkan pusat notifikasi.
9. Tampilkan bukti foto dan GPS.

## Skenario Alternatif

- `DEMOPER002`: laporan perlu revisi.
- `DEMOPER003`: laporan sudah disetujui.
- `DEMOPER004`: pekerjaan masih berlangsung.
- `DEMOPER005`: belum check-in.

## Pemeriksaan Sebelum Presentasi

```bash
php artisan test
php artisan route:list
git status
```

Pastikan:

- MySQL dan Apache/XAMPP berjalan.
- Kamera browser mendapat izin.
- Lokasi browser mendapat izin.
- Internet tersedia untuk membuka Google Maps.
- Mode test presensi diaktifkan apabila presentasi dilakukan di luar tanggal jadwal.
- Backup database sudah tersedia.

## Penutup Presentasi

Tekankan manfaat utama:

- Pengawasan WFH lebih terukur.
- Kehadiran dibuktikan foto dan GPS.
- Tugas serta hasil pekerjaan terdokumentasi.
- Pimpinan dapat memantau secara real time.
- Riwayat aktivitas dan verifikasi tersimpan.
