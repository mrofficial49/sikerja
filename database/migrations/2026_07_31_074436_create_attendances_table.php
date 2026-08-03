<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel attendances.
     *
     * Tabel ini menyimpan data check-in dan check-out
     * setiap personel pada jadwal WFH.
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            // Primary key BIGINT UNSIGNED AUTO_INCREMENT.
            $table->id();

            /*
             * Menghubungkan presensi dengan anggota jadwal WFH.
             *
             * unique() berarti satu anggota jadwal hanya boleh
             * memiliki satu data presensi.
             */
            $table->foreignId('schedule_member_id')
                ->unique()
                ->constrained('wfh_schedule_members')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            /*
             * =========================
             * DATA CHECK-IN
             * =========================
             */

            // Waktu personel melakukan check-in.
            $table->timestamp('checkin_at')->nullable();

            /*
             * Status ketepatan waktu check-in:
             * - on_time : pukul 07.00–07.10;
             * - late    : pukul 07.11–08.00;
             * - missed  : melewati batas check-in.
             */
            $table->enum('checkin_status', [
                'on_time',
                'late',
                'missed',
            ])->nullable();

            // Wajib diisi apabila check-in terlambat.
            $table->text('checkin_reason')->nullable();

            // Koordinat GPS saat check-in.
            $table->decimal('checkin_latitude', 10, 7)->nullable();
            $table->decimal('checkin_longitude', 10, 7)->nullable();

            // Lokasi penyimpanan foto kamera saat check-in.
            $table->string('checkin_photo_path')->nullable();

            // Tanggal kedaluwarsa file foto check-in.
            $table->timestamp('checkin_photo_expires_at')->nullable();

            // Waktu file foto check-in dihapus secara fisik.
            $table->timestamp('checkin_photo_deleted_at')->nullable();

            /*
             * =========================
             * DATA CHECK-OUT
             * =========================
             */

            // Waktu personel melakukan check-out.
            $table->timestamp('checkout_at')->nullable();

            /*
             * Status ketepatan waktu check-out:
             * - on_time : pukul 15.00–15.30;
             * - late    : pukul 15.31–16.00;
             * - missed  : tidak check-out sampai batas waktu.
             */
            $table->enum('checkout_status', [
                'on_time',
                'late',
                'missed',
            ])->nullable();

            // Wajib diisi apabila check-out terlambat.
            $table->text('checkout_reason')->nullable();

            // Koordinat GPS saat check-out.
            $table->decimal('checkout_latitude', 10, 7)->nullable();
            $table->decimal('checkout_longitude', 10, 7)->nullable();

            // Lokasi penyimpanan foto kamera saat check-out.
            $table->string('checkout_photo_path')->nullable();

            // Tanggal kedaluwarsa file foto check-out.
            $table->timestamp('checkout_photo_expires_at')->nullable();

            // Waktu file foto check-out dihapus secara fisik.
            $table->timestamp('checkout_photo_deleted_at')->nullable();

            /*
             * Status akhir presensi:
             * - present    : check-in dan check-out lengkap;
             * - absent     : tidak melakukan check-in;
             * - incomplete : check-in ada tetapi check-out tidak lengkap.
             */
            $table->enum('attendance_status', [
                'present',
                'absent',
                'incomplete',
            ])->nullable();

            // Membuat created_at dan updated_at.
            $table->timestamps();

            // Mempercepat pencarian berdasarkan status presensi.
            $table->index('attendance_status');
        });
    }

    /**
     * Menghapus tabel attendances saat rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
