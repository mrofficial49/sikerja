<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel wfh_schedules.
     *
     * Tabel ini menyimpan jadwal pelaksanaan WFH,
     * misalnya jadwal WFH pada hari Jumat tertentu.
     */
    public function up(): void
    {
        Schema::create('wfh_schedules', function (Blueprint $table) {
            // Primary key BIGINT UNSIGNED AUTO_INCREMENT.
            $table->id();

            /*
             * Tanggal pelaksanaan WFH.
             *
             * Dibuat unique karena satu tanggal hanya boleh
             * memiliki satu jadwal WFH.
             */
            $table->date('wfh_date')->unique();

            /*
             * Status jadwal:
             * - draft     : masih disiapkan Admin;
             * - active    : jadwal sudah aktif;
             * - completed : pelaksanaan WFH sudah selesai;
             * - cancelled : jadwal dibatalkan.
             */
            $table->enum('status', [
                'draft',
                'active',
                'completed',
                'cancelled',
            ])->default('draft');

            /*
             * Admin yang membuat jadwal.
             *
             * Data user tidak dihapus permanen, sehingga foreign key
             * menggunakan restrict.
             */
            $table->foreignId('created_by')
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            /*
             * Bernilai true apabila seluruh personel aktif
             * dimasukkan ke dalam jadwal.
             */
            $table->boolean('is_all_personnel')->default(false);

            // Catatan tambahan dari Admin, boleh kosong.
            $table->text('notes')->nullable();

            // Waktu ketika jadwal mulai diaktifkan.
            $table->timestamp('activated_at')->nullable();

            // Membuat kolom created_at dan updated_at.
            $table->timestamps();

            // Mempercepat pencarian berdasarkan status.
            $table->index('status');
        });
    }

    /**
     * Menghapus tabel saat rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('wfh_schedules');
    }
};
