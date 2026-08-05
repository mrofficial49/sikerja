# Panduan Pengguna SIKERJA

## 1. Pendahuluan

SIKERJA adalah **Sistem Informasi Kinerja dan Aktivitas Personel** yang digunakan untuk mendukung pelaksanaan Work From Home (WFH), mulai dari penjadwalan, presensi berbasis foto dan GPS, penugasan, pelaporan hasil kerja, verifikasi, monitoring, notifikasi, sampai ekspor rekap kinerja ke PDF.

Panduan ini menjelaskan penggunaan SIKERJA berdasarkan tiga role:

- Admin
- Pimpinan
- Personel

---

## 2. Akses Aplikasi

Buka aplikasi melalui browser:

```text
http://127.0.0.1:8000
```

Pada server production, gunakan alamat resmi yang telah ditentukan oleh pengelola aplikasi.

---

## 3. Login

1. Buka halaman login SIKERJA.
2. Masukkan NRP, NIP, atau ID Login.
3. Masukkan password.
4. Tekan tombol **Masuk ke SIKERJA**.
5. Apabila akun masih menggunakan password sementara, sistem akan meminta pengguna mengganti password terlebih dahulu.

### Akun Demo

Semua akun demo menggunakan password:

```text
DemoSikerja#2026
```

| Role | ID Login |
|---|---|
| Admin | `DEMOADMIN` |
| Pimpinan | `DEMOPIMPINAN` |
| Personel 1 | `DEMOPER001` |
| Personel 2 | `DEMOPER002` |
| Personel 3 | `DEMOPER003` |
| Personel 4 | `DEMOPER004` |
| Personel 5 | `DEMOPER005` |

> Akun demo hanya digunakan untuk pengembangan dan presentasi. Nonaktifkan atau hapus akun demo sebelum aplikasi digunakan pada lingkungan production.

---

## 4. Tampilan Utama

Setelah login, pengguna akan melihat:

- Sidebar navigasi.
- Topbar berisi identitas pengguna.
- Tombol notifikasi.
- Informasi role dan unit.
- Menu keluar dari sistem.
- Konten utama sesuai hak akses.

Pada perangkat mobile, sidebar ditampilkan dalam bentuk menu offcanvas.

---

# BAGIAN A — PANDUAN ADMIN

## 5. Dashboard Admin

Dashboard Admin menampilkan ringkasan:

- Jumlah pengguna.
- Jadwal WFH.
- Status presensi.
- Status laporan.
- Aktivitas penting sistem.

Gunakan dashboard sebagai pusat pemantauan awal.

---

## 6. Mengelola Unit Kerja

1. Buka menu **Unit Kerja**.
2. Tekan tombol **Tambah Unit**.
3. Isi kode unit.
4. Isi nama unit.
5. Isi deskripsi apabila diperlukan.
6. Simpan data.
7. Gunakan tombol edit untuk memperbarui unit.
8. Gunakan fitur status untuk mengaktifkan atau menonaktifkan unit.

Pastikan kode unit tidak sama dengan unit lain.

---

## 7. Mengelola Pengguna

### Menambah Pengguna

1. Buka menu **Pengguna**.
2. Tekan tombol **Tambah Pengguna**.
3. Isi nama.
4. Isi NRP, NIP, atau ID Login.
5. Pilih role.
6. Pilih unit kerja.
7. Isi pangkat dan jabatan.
8. Tentukan password awal.
9. Simpan akun.

### Mengedit Pengguna

1. Buka daftar pengguna.
2. Pilih pengguna.
3. Tekan tombol **Edit**.
4. Perbarui data.
5. Simpan perubahan.

### Menonaktifkan Pengguna

1. Buka daftar pengguna.
2. Pilih pengguna.
3. Tekan tombol status atau nonaktifkan.
4. Konfirmasi tindakan.

Pengguna nonaktif tidak dapat login.

### Reset Password

1. Buka detail atau daftar pengguna.
2. Pilih **Reset Password**.
3. Konfirmasi reset.
4. Sampaikan password sementara kepada pengguna.
5. Saat login berikutnya, pengguna wajib mengganti password.

---

## 8. Mengelola Jadwal WFH

### Membuat Jadwal

1. Buka menu **Jadwal WFH**.
2. Tekan **Tambah Jadwal**.
3. Pilih tanggal WFH.
4. Isi nama atau keterangan jadwal.
5. Simpan sebagai draft.

### Menambahkan Personel

1. Buka detail jadwal.
2. Tekan **Tambah Personel**.
3. Pilih unit atau Personel.
4. Simpan daftar anggota.
5. Periksa kembali anggota yang terjadwal.

### Mengaktifkan Jadwal

1. Pastikan tanggal dan anggota sudah benar.
2. Tekan **Aktifkan Jadwal**.
3. Konfirmasi aktivasi.

Status jadwal yang digunakan:

- Draft
- Active
- Completed
- Cancelled

---

## 9. Monitoring dan Rekap

1. Buka menu **Monitoring & Rekap**.
2. Pilih jadwal WFH.
3. Pilih unit kerja apabila diperlukan.
4. Isi pencarian nama, NRP/NIP, pangkat, atau jabatan apabila diperlukan.
5. Tekan **Tampilkan**.
6. Periksa ringkasan statistik.
7. Periksa tabel rincian Personel.

Informasi yang tersedia:

- Status check-in.
- Waktu check-in.
- Status check-out.
- Waktu check-out.
- Jumlah pekerjaan.
- Status laporan.
- Tombol bukti foto dan GPS.
- Tombol detail laporan.

---

## 10. Melihat Foto dan GPS Presensi

1. Buka **Monitoring & Rekap**.
2. Pilih Personel.
3. Tekan tombol **Foto & GPS**.
4. Periksa foto check-in.
5. Periksa foto check-out.
6. Periksa koordinat latitude dan longitude.
7. Tekan tautan Google Maps untuk membuka lokasi.

Foto presensi disimpan secara privat dan hanya dapat dibuka melalui controller yang memeriksa hak akses.

---

## 11. Verifikasi Laporan

1. Buka menu **Verifikasi Laporan**.
2. Pilih laporan berstatus **Menunggu Verifikasi**.
3. Periksa identitas Personel.
4. Periksa daftar pekerjaan.
5. Periksa progres.
6. Periksa target hasil.
7. Periksa kendala dan tindak lanjut.
8. Periksa bukti pekerjaan PDF.
9. Pilih salah satu tindakan:

### Setujui

Gunakan apabila laporan sudah benar dan lengkap.

### Minta Revisi

Gunakan apabila laporan masih perlu diperbaiki.

Saat meminta revisi:

- Isi catatan secara jelas.
- Jelaskan bagian yang harus diperbaiki.
- Hindari catatan yang terlalu umum.

---

## 12. Ekspor Rekap Kinerja PDF

1. Buka **Monitoring & Rekap**.
2. Pilih jadwal WFH.
3. Pilih unit apabila diperlukan.
4. Isi pencarian apabila diperlukan.
5. Tekan **Tampilkan**.
6. Tekan **Ekspor Rekap PDF**.
7. Browser akan mengunduh dokumen.

Format nama file:

```text
rekap-kinerja-wfh-YYYY-MM-DD.pdf
```

Isi PDF:

- Logo SIKERJA.
- Tanggal WFH.
- Status jadwal.
- Unit kerja.
- Nama pembuat.
- Ringkasan presensi.
- Jumlah pekerjaan.
- Jumlah pekerjaan selesai.
- Identitas Personel.
- Unit dan jabatan.
- Waktu check-in dan check-out.
- Hasil kinerja.
- Progres.
- Kendala.
- Status laporan.
- Catatan verifikasi.
- Kolom pengesahan.
- Nomor halaman.

Filter pada halaman monitoring ikut diterapkan pada PDF.

---

# BAGIAN B — PANDUAN PIMPINAN

## 13. Dashboard Pimpinan

Dashboard Pimpinan menampilkan:

- Ringkasan Personel.
- Status presensi.
- Status tugas.
- Status laporan.
- Laporan yang menunggu pemeriksaan.

---

## 14. Memberikan Tugas

1. Buka menu **Tugas Personel**.
2. Tekan **Berikan Tugas**.
3. Pilih jadwal WFH.
4. Pilih Personel.
5. Isi judul tugas.
6. Isi uraian tugas.
7. Isi target hasil.
8. Simpan tugas.

Personel akan menerima notifikasi tugas baru.

---

## 15. Memantau Pelaksanaan Tugas

1. Buka **Monitoring & Rekap**.
2. Pilih jadwal.
3. Gunakan filter unit atau pencarian.
4. Periksa jumlah pekerjaan.
5. Buka detail laporan.
6. Periksa progres dan kendala.

---

## 16. Memverifikasi Laporan

Langkah verifikasi Pimpinan sama dengan Admin:

1. Buka **Verifikasi Laporan**.
2. Pilih laporan.
3. Periksa hasil pekerjaan.
4. Setujui atau minta revisi.
5. Isi catatan verifikasi apabila diperlukan.

---

## 17. Ekspor Rekap PDF

Pimpinan dapat mengekspor rekap melalui menu **Monitoring & Rekap**.

Langkah:

1. Pilih jadwal.
2. Terapkan filter.
3. Tekan **Ekspor Rekap PDF**.
4. Buka file hasil unduhan.

---

# BAGIAN C — PANDUAN PERSONEL

## 18. Dashboard Personel

Dashboard Personel menampilkan:

- Jadwal WFH.
- Status check-in.
- Status check-out.
- Daftar pekerjaan.
- Status laporan.
- Notifikasi.

---

## 19. Check-in

1. Login pada tanggal WFH.
2. Buka menu **Presensi WFH**.
3. Izinkan browser menggunakan kamera.
4. Izinkan browser menggunakan lokasi.
5. Ambil foto check-in.
6. Tunggu sampai koordinat GPS diperoleh.
7. Isi alasan apabila terlambat.
8. Tekan **Check-in**.

Pastikan:

- Kamera tidak tertutup.
- Wajah terlihat jelas.
- Layanan lokasi aktif.
- Browser mendapat izin lokasi.

---

## 20. Membuat Rencana Kerja Pribadi

1. Setelah check-in, buka menu **Pekerjaan**.
2. Tekan **Tambah Rencana Kerja**.
3. Isi judul pekerjaan.
4. Isi uraian.
5. Isi target hasil.
6. Simpan.

Rencana pribadi digunakan untuk pekerjaan yang tidak berasal dari penugasan Pimpinan.

---

## 21. Menerima Tugas Pimpinan

Tugas Pimpinan akan muncul pada:

- Dashboard Personel.
- Menu Pekerjaan.
- Pusat Notifikasi.

Buka tugas untuk membaca:

- Judul.
- Uraian.
- Target hasil.
- Jadwal terkait.

---

## 22. Memperbarui Pekerjaan

1. Buka menu **Pekerjaan**.
2. Pilih pekerjaan.
3. Tekan **Perbarui Pelaksanaan**.
4. Pilih status.
5. Isi progres.
6. Isi kendala apabila ada.
7. Isi rencana tindak lanjut.
8. Tentukan apakah pekerjaan dilanjutkan secara luring.
9. Upload bukti pekerjaan PDF apabila diperlukan.
10. Simpan.

Status pekerjaan:

- Belum dimulai.
- Sedang berlangsung.
- Terkendala.
- Selesai.
- Dibatalkan.

---

## 23. Mengunggah Bukti Pekerjaan

1. Buka detail pekerjaan.
2. Pilih file PDF.
3. Pastikan file benar.
4. Upload file.
5. Simpan perubahan.

File bukti disimpan pada storage privat.

---

## 24. Mengirim Laporan

1. Pastikan pekerjaan sudah diperbarui.
2. Buka menu **Laporan & Check-out**.
3. Periksa ringkasan pekerjaan.
4. Tekan **Kirim Laporan**.
5. Status berubah menjadi **Menunggu Verifikasi**.

Laporan tidak dapat dikirim apabila data wajib belum lengkap.

---

## 25. Check-out

1. Pastikan laporan sudah dikirim.
2. Buka halaman check-out.
3. Izinkan kamera dan lokasi.
4. Ambil foto check-out.
5. Tunggu sampai GPS diperoleh.
6. Tekan **Check-out**.

Setelah check-out, laporan akan dikunci sesuai aturan aplikasi.

---

## 26. Memperbaiki Laporan

1. Buka notifikasi revisi.
2. Baca catatan Admin atau Pimpinan.
3. Buka laporan.
4. Perbaiki pekerjaan.
5. Perbarui progres, kendala, bukti, atau tindak lanjut.
6. Kirim ulang laporan.

Check-out tidak perlu dilakukan ulang apabila sebelumnya sudah selesai.

---

## 27. Pusat Notifikasi

Notifikasi dapat berisi:

- Jadwal baru.
- Perubahan jadwal.
- Tugas baru.
- Laporan menunggu verifikasi.
- Permintaan revisi.
- Persetujuan laporan.

Tekan notifikasi untuk membuka halaman terkait.

---

## 28. Keluar dari Sistem

1. Tekan tombol **Keluar dari Sistem**.
2. Konfirmasi tindakan.
3. Sistem akan menghapus sesi login.

Selalu logout setelah menggunakan perangkat bersama.

---

## 29. Kendala Umum

### Kamera tidak muncul

- Periksa izin kamera browser.
- Gunakan browser modern.
- Pastikan kamera tidak digunakan aplikasi lain.

### GPS tidak terbaca

- Aktifkan layanan lokasi.
- Izinkan lokasi pada browser.
- Tunggu beberapa detik.
- Coba muat ulang halaman.

### Tombol tidak muncul

- Periksa status jadwal.
- Periksa role pengguna.
- Periksa apakah tahap sebelumnya sudah selesai.

### Foto tidak tampil

- Pastikan file masih tersedia.
- Pastikan pengguna memiliki hak akses.
- Hubungi Admin apabila file sudah terhapus.

### PDF tidak terunduh

- Periksa koneksi.
- Periksa jadwal yang dipilih.
- Pastikan login sebagai Admin atau Pimpinan.
- Hubungi pengelola aplikasi.

---

## 30. Keamanan Penggunaan

- Jangan membagikan password.
- Gunakan password yang kuat.
- Ganti password sementara.
- Logout setelah selesai.
- Jangan mengunggah file yang tidak berkaitan dengan pekerjaan.
- Jangan mengubah URL untuk mencoba membuka data pengguna lain.
- Laporkan aktivitas yang mencurigakan kepada Admin.
