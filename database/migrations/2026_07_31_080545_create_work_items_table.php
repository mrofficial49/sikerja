<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel work_items.
     *
     * Tabel ini menyimpan:
     * 1. Rencana kerja pribadi dari Personel.
     * 2. Tugas yang diberikan oleh Pimpinan.
     */
    public function up(): void
    {
        Schema::create('work_items', function (Blueprint $table) {
            // Primary key BIGINT UNSIGNED AUTO_INCREMENT.
            $table->id();

            /*
             * Laporan kerja yang memiliki kegiatan ini.
             *
             * Jika laporan kerja dihapus saat pengembangan,
             * detail kegiatannya ikut dihapus.
             */
            $table->foreignId('report_id')
                ->constrained('work_reports')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            /*
             * Pengguna yang membuat kegiatan.
             *
             * Bisa berupa:
             * - Personel yang membuat rencana pribadi.
             * - Pimpinan yang memberikan tugas.
             */
            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
             * Sumber kegiatan:
             * - personal_plan : rencana kerja dari Personel.
             * - leader_task   : tugas dari Pimpinan.
             */
            $table->enum('source_type', [
                'personal_plan',
                'leader_task',
            ]);

            // Judul singkat pekerjaan atau tugas.
            $table->string('title', 200);

            // Penjelasan lengkap pekerjaan.
            $table->text('description');

            // Target hasil yang harus dicapai.
            $table->text('target_result');

            /*
             * Status pekerjaan:
             * - not_started : belum dimulai.
             * - in_progress : sedang dikerjakan.
             * - blocked     : mengalami kendala.
             * - completed   : sudah selesai.
             * - cancelled   : dibatalkan oleh Pimpinan.
             */
            $table->enum('status', [
                'not_started',
                'in_progress',
                'blocked',
                'completed',
                'cancelled',
            ])->default('not_started');

            // Penjelasan perkembangan pekerjaan.
            $table->text('progress')->nullable();

            // Penjelasan kendala yang dihadapi.
            $table->text('obstacle')->nullable();

            // Rencana tindak lanjut pekerjaan.
            $table->text('follow_up_plan')->nullable();

            /*
             * Bernilai true jika pekerjaan akan
             * dilanjutkan di luar sistem atau secara offline.
             */
            $table->boolean('continue_offline')->default(false);

            /*
             * Pimpinan yang membatalkan tugas.
             *
             * Kolom ini boleh kosong karena tidak semua
             * pekerjaan dibatalkan.
             */
            $table->foreignId('cancelled_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // Waktu tugas dibatalkan.
            $table->timestamp('cancelled_at')->nullable();

            /*
             * Waktu tugas diberikan.
             *
             * Untuk rencana kerja pribadi, kolom ini
             * boleh dikosongkan.
             */
            $table->timestamp('assigned_at')->nullable();

            // Membuat created_at dan updated_at.
            $table->timestamps();

            // Mempercepat pencarian kegiatan dalam laporan.
            $table->index('report_id');

            // Mempercepat filter berdasarkan sumber kegiatan.
            $table->index('source_type');

            // Mempercepat filter berdasarkan status pekerjaan.
            $table->index('status');
        });
    }

    /**
     * Menghapus tabel work_items saat rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_items');
    }
};
