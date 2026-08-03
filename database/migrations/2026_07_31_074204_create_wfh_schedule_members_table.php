<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel wfh_schedule_members.
     *
     * Tabel ini menjadi penghubung antara jadwal WFH
     * dengan personel yang mengikuti jadwal tersebut.
     */
    public function up(): void
    {
        Schema::create('wfh_schedule_members', function (Blueprint $table) {
            // Primary key BIGINT UNSIGNED AUTO_INCREMENT.
            $table->id();

            /*
             * Jadwal WFH yang diikuti personel.
             *
             * Jika jadwal dihapus saat pengembangan, daftar anggotanya
             * ikut dihapus melalui cascade.
             */
            $table->foreignId('schedule_id')
                ->constrained('wfh_schedules')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            /*
             * Personel yang dimasukkan ke jadwal.
             *
             * User tidak boleh dihapus apabila masih memiliki
             * riwayat jadwal WFH.
             */
            $table->foreignId('user_id')
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            /*
             * Status keikutsertaan personel:
             * - scheduled       : dijadwalkan mengikuti WFH;
             * - cancelled       : keikutsertaan dibatalkan;
             * - schedule_change : ditambahkan karena perubahan jadwal;
             * - present         : berhasil melakukan check-in;
             * - absent          : tidak melakukan check-in.
             */
            $table->enum('member_status', [
                'scheduled',
                'cancelled',
                'schedule_change',
                'present',
                'absent',
            ])->default('scheduled');

            // Admin yang memasukkan personel ke jadwal.
            $table->foreignId('added_by')
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            /*
             * Bernilai true jika personel ditambahkan pada hari Jumat
             * karena adanya perubahan jadwal atau perintah.
             */
            $table->boolean('is_schedule_change')->default(false);

            // Alasan perubahan jadwal, boleh kosong.
            $table->text('change_reason')->nullable();

            // Waktu personel dimasukkan ke jadwal.
            $table->timestamp('added_at')->useCurrent();

            /*
             * Batas waktu check-in.
             *
             * Untuk jadwal normal dapat diatur pukul 08.00.
             * Untuk perubahan jadwal, dapat diatur 30 menit
             * setelah personel ditambahkan.
             */
            $table->timestamp('checkin_deadline')->nullable();

            // Waktu keikutsertaan personel dibatalkan.
            $table->timestamp('cancelled_at')->nullable();

            // Membuat created_at dan updated_at.
            $table->timestamps();

            /*
             * Satu personel hanya boleh tercatat satu kali
             * pada jadwal WFH yang sama.
             */
            $table->unique(
                ['schedule_id', 'user_id'],
                'wfh_schedule_member_unique'
            );

            // Mempercepat pencarian berdasarkan status anggota.
            $table->index('member_status');
        });
    }

    /**
     * Menghapus tabel saat rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('wfh_schedule_members');
    }
};
