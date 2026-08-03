<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel absence_reports.
     *
     * Tabel ini digunakan oleh personel yang tidak dapat
     * melakukan check-in WFH.
     */
    public function up(): void
    {
        Schema::create('absence_reports', function (Blueprint $table) {
            // Primary key BIGINT UNSIGNED AUTO_INCREMENT.
            $table->id();

            /*
             * Satu anggota jadwal hanya boleh mengirim
             * satu laporan ketidakhadiran.
             */
            $table->foreignId('schedule_member_id')
                ->unique()
                ->constrained('wfh_schedule_members')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            /*
             * Jenis ketidakhadiran:
             * - sick              : sakit;
             * - permission        : izin;
             * - official_duty     : dinas;
             * - leave             : cuti;
             * - technical_issue   : kendala teknis;
             * - without_explanation: tanpa keterangan;
             * - other             : alasan lainnya.
             */
            $table->enum('absence_type', [
                'sick',
                'permission',
                'official_duty',
                'leave',
                'technical_issue',
                'without_explanation',
                'other',
            ]);

            // Penjelasan lengkap dari personel.
            $table->text('reason');

            // Lokasi file foto bukti dari kamera.
            $table->string('photo_path')->nullable();

            // Tanggal kedaluwarsa file foto.
            $table->timestamp('photo_expires_at')->nullable();

            // Waktu file foto dihapus secara fisik.
            $table->timestamp('photo_deleted_at')->nullable();

            // Waktu laporan ketidakhadiran dikirim.
            $table->timestamp('submitted_at')->nullable();

            /*
             * Setelah dikirim, laporan dikunci agar tidak
             * dapat diubah kembali.
             */
            $table->boolean('is_locked')->default(false);

            // Membuat created_at dan updated_at.
            $table->timestamps();

            // Mempercepat filter jenis ketidakhadiran.
            $table->index('absence_type');
        });
    }

    /**
     * Menghapus tabel absence_reports saat rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('absence_reports');
    }
};
