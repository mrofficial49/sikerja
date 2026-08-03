<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel notifications.
     *
     * Tabel ini menyimpan notifikasi yang ditampilkan
     * di dalam website kepada setiap pengguna.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            // Primary key BIGINT UNSIGNED AUTO_INCREMENT.
            $table->id();

            /*
             * Pengguna yang menerima notifikasi.
             *
             * User tidak boleh dihapus permanen apabila masih
             * memiliki riwayat notifikasi.
             */
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
             * Jenis notifikasi.
             *
             * Contoh:
             * - schedule
             * - leader_task
             * - report_approved
             * - report_revision
             * - announcement
             */
            $table->string('type', 50);

            // Judul singkat notifikasi.
            $table->string('title', 200);

            // Isi lengkap notifikasi.
            $table->text('message');

            /*
             * Nama jenis data yang berhubungan dengan notifikasi.
             *
             * Contoh:
             * - WorkReport
             * - WorkItem
             * - WfhSchedule
             *
             * Kolom ini tidak menggunakan foreign key karena
             * dapat menunjuk ke berbagai jenis tabel.
             */
            $table->string('related_type', 100)->nullable();

            /*
             * ID data yang berhubungan dengan notifikasi.
             *
             * Kolom ini juga tidak menggunakan foreign key
             * karena tabel tujuan dapat berbeda-beda.
             */
            $table->unsignedBigInteger('related_id')->nullable();

            // Menandai apakah notifikasi sudah dibaca.
            $table->boolean('is_read')->default(false);

            // Waktu ketika notifikasi dibaca.
            $table->timestamp('read_at')->nullable();

            // Membuat created_at dan updated_at.
            $table->timestamps();

            // Mempercepat pencarian notifikasi milik pengguna.
            $table->index('user_id');

            // Mempercepat pencarian notifikasi belum dibaca.
            $table->index(['user_id', 'is_read']);

            // Mempercepat pencarian data yang berhubungan.
            $table->index(
                ['related_type', 'related_id'],
                'notifications_related_index'
            );
        });
    }

    /**
     * Menghapus tabel notifications saat rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
