<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel announcements.
     *
     * Tabel ini menyimpan pengumuman berbentuk teks
     * yang dibuat oleh Admin.
     */
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            // Primary key BIGINT UNSIGNED AUTO_INCREMENT.
            $table->id();

            // Admin yang membuat pengumuman.
            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Judul pengumuman.
            $table->string('title', 200);

            // Isi lengkap pengumuman.
            $table->text('content');

            /*
             * Hanya satu pengumuman yang nantinya boleh aktif.
             *
             * Aturan tersebut akan diterapkan melalui
             * service atau controller Laravel.
             */
            $table->boolean('is_active')->default(true);

            // Waktu ketika pengumuman dinonaktifkan.
            $table->timestamp('deactivated_at')->nullable();

            // Membuat created_at dan updated_at.
            $table->timestamps();

            // Mempercepat pencarian pengumuman aktif.
            $table->index('is_active');

            // Mempercepat pencarian berdasarkan pembuat.
            $table->index('created_by');
        });
    }

    /**
     * Menghapus tabel announcements saat rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
