<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel activity_logs.
     *
     * Tabel ini mencatat aktivitas penting pengguna
     * untuk kebutuhan audit dan keamanan sistem.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            // Primary key BIGINT UNSIGNED AUTO_INCREMENT.
            $table->id();

            /*
             * Pengguna yang melakukan aktivitas.
             *
             * Nullable karena beberapa aktivitas dapat dilakukan
             * oleh sistem, misalnya penghapusan file otomatis.
             */
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            /*
             * Nama tindakan yang dilakukan.
             *
             * Contoh:
             * - login_success
             * - login_failed
             * - schedule_created
             * - checkin
             * - report_submitted
             * - report_approved
             * - file_deleted
             */
            $table->string('action', 100);

            // Penjelasan aktivitas.
            $table->text('description');

            /*
             * Nama jenis data yang terkena aktivitas.
             *
             * Contoh:
             * - User
             * - WorkReport
             * - Attendance
             * - WfhSchedule
             */
            $table->string('subject_type', 100)->nullable();

            /*
             * ID data yang terkena aktivitas.
             *
             * Tidak menggunakan foreign key karena dapat menunjuk
             * ke berbagai tabel.
             */
            $table->unsignedBigInteger('subject_id')->nullable();

            /*
             * Alamat IP pengguna.
             *
             * Panjang 45 karakter dapat menyimpan IPv4 maupun IPv6.
             */
            $table->string('ip_address', 45)->nullable();

            /*
             * Informasi browser dan perangkat pengguna.
             *
             * Menggunakan text karena isinya dapat cukup panjang.
             */
            $table->text('user_agent')->nullable();

            /*
             * Activity log hanya membutuhkan created_at.
             *
             * Tidak menggunakan updated_at karena riwayat aktivitas
             * tidak boleh diedit.
             */
            $table->timestamp('created_at')->useCurrent();

            // Mempercepat pencarian aktivitas pengguna.
            $table->index('user_id');

            // Mempercepat filter berdasarkan tindakan.
            $table->index('action');

            // Mempercepat pencarian aktivitas suatu data.
            $table->index(
                ['subject_type', 'subject_id'],
                'activity_logs_subject_index'
            );

            // Mempercepat filter berdasarkan tanggal.
            $table->index('created_at');
        });
    }

    /**
     * Menghapus tabel activity_logs saat rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
