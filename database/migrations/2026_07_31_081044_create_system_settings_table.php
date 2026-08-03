<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel system_settings.
     *
     * Tabel ini menyimpan pengaturan aplikasi yang dapat
     * digunakan tanpa mengubah kode program.
     */
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            // Primary key BIGINT UNSIGNED AUTO_INCREMENT.
            $table->id();

            /*
             * Kunci atau nama pengaturan.
             *
             * Harus unik agar satu jenis pengaturan hanya
             * mempunyai satu nilai.
             *
             * Contoh:
             * - app_name
             * - app_subtitle
             * - admin_contact_name
             * - checkin_start_time
             */
            $table->string('setting_key', 100)->unique();

            /*
             * Nilai pengaturan.
             *
             * Menggunakan longText agar dapat menyimpan teks pendek,
             * teks panjang, angka, waktu, maupun data JSON.
             */
            $table->longText('setting_value')->nullable();

            /*
             * Jenis data pengaturan.
             *
             * Digunakan agar aplikasi mengetahui cara membaca nilai.
             */
            $table->enum('data_type', [
                'string',
                'integer',
                'boolean',
                'time',
                'date',
                'json',
                'text',
            ])->default('string');

            // Penjelasan fungsi pengaturan.
            $table->text('description')->nullable();

            /*
             * true berarti pengaturan boleh ditampilkan
             * sebelum pengguna login.
             *
             * Contoh: nama aplikasi dan kontak Admin.
             */
            $table->boolean('is_public')->default(false);

            // Membuat created_at dan updated_at.
            $table->timestamps();

            // Mempercepat filter pengaturan publik.
            $table->index('is_public');
        });
    }

    /**
     * Menghapus tabel system_settings saat rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
