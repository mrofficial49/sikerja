<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel work_reports.
     *
     * Tabel ini menjadi data utama laporan kerja
     * seorang personel pada satu jadwal WFH.
     */
    public function up(): void
    {
        Schema::create('work_reports', function (Blueprint $table) {
            // Primary key BIGINT UNSIGNED AUTO_INCREMENT.
            $table->id();

            /*
             * Satu anggota jadwal hanya boleh mempunyai
             * satu laporan kerja.
             */
            $table->foreignId('schedule_member_id')
                ->unique()
                ->constrained('wfh_schedule_members')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            /*
             * Status laporan:
             * - draft              : masih dikerjakan;
             * - waiting_verification: sudah dikirim;
             * - approved           : disetujui Pimpinan;
             * - needs_revision     : perlu diperbaiki;
             * - incomplete         : laporan tidak lengkap;
             * - completed_offline  : pekerjaan diselesaikan offline.
             */
            $table->enum('status', [
                'draft',
                'waiting_verification',
                'approved',
                'needs_revision',
                'incomplete',
                'completed_offline',
            ])->default('draft');

            // Waktu personel menekan tombol Kirim Laporan.
            $table->timestamp('submitted_at')->nullable();

            /*
             * Alasan perubahan terakhir apabila laporan
             * diedit setelah pernah dikirim.
             */
            $table->text('last_change_reason')->nullable();

            // Waktu perubahan terakhir setelah laporan dikirim.
            $table->timestamp('last_changed_at')->nullable();

            /*
             * Pimpinan yang melakukan verifikasi.
             *
             * nullable karena laporan baru belum tentu
             * sudah diperiksa.
             */
            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // Waktu laporan diverifikasi Pimpinan.
            $table->timestamp('verified_at')->nullable();

            /*
             * Waktu Pimpinan menetapkan pekerjaan
             * telah selesai secara offline.
             */
            $table->timestamp('completed_offline_at')->nullable();

            // Waktu laporan dikunci secara permanen.
            $table->timestamp('locked_at')->nullable();

            /*
             * true berarti laporan tidak dapat diubah lagi,
             * misalnya setelah check-out selesai.
             */
            $table->boolean('is_locked')->default(false);

            // Membuat created_at dan updated_at.
            $table->timestamps();

            // Mempercepat filter laporan berdasarkan status.
            $table->index('status');

            // Mempercepat pencarian laporan berdasarkan Pimpinan.
            $table->index('verified_by');
        });
    }

    /**
     * Menghapus tabel work_reports saat rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_reports');
    }
};
