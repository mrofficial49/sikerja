<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel roles.
     *
     * Tabel ini menyimpan jenis hak akses pengguna:
     * Admin, Pimpinan, dan Personel.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            // Primary key:
            // BIGINT UNSIGNED, PRIMARY KEY, AUTO INCREMENT.
            $table->id();

            // Nama role harus unik.
            // Contoh: Admin, Pimpinan, Personel.
            $table->string('name', 50)->unique();

            // Keterangan role boleh dikosongkan.
            $table->string('description', 255)->nullable();

            // Menentukan apakah role masih aktif.
            $table->boolean('is_active')->default(true);

            // Membuat kolom created_at dan updated_at.
            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel roles ketika migration di-rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
