<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel units.
     *
     * Tabel ini menyimpan data unit atau bagian tempat
     * personel ditempatkan.
     */
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            // Primary key:
            // BIGINT UNSIGNED, PRIMARY KEY, AUTO INCREMENT.
            $table->id();

            // Kode unit harus unik.
            // Contoh: TU, BINUM, DUKKUM.
            $table->string('code', 20)->unique();

            // Nama lengkap unit atau bagian.
            $table->string('name', 100);

            // Keterangan tambahan boleh dikosongkan.
            $table->string('description', 255)->nullable();

            // Unit tidak dihapus permanen.
            // Unit cukup diaktifkan atau dinonaktifkan.
            $table->boolean('is_active')->default(true);

            // Membuat created_at dan updated_at.
            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel units ketika migration di-rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
