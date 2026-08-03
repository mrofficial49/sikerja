<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel users.
     *
     * Seluruh akun Admin, Pimpinan, dan Personel
     * disimpan dalam satu tabel.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // Primary key:
            // BIGINT UNSIGNED, PRIMARY KEY, AUTO INCREMENT.
            $table->id();

            /*
             * Foreign key menuju tabel roles.
             *
             * role_id wajib diisi.
             * Role tidak boleh dihapus apabila masih digunakan user.
             */
            $table->foreignId('role_id')
                ->constrained('roles')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            /*
             * Foreign key menuju tabel units.
             *
             * unit_id wajib bagi Personel.
             * Untuk Admin dan Pimpinan boleh kosong.
             * Aturan wajib atau tidaknya nanti diperiksa melalui validasi Laravel.
             */
            $table->foreignId('unit_id')
                ->nullable()
                ->constrained('units')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            /*
             * ID untuk login.
             *
             * Personel menggunakan NRP/NIP.
             * Admin dan Pimpinan menggunakan ID khusus.
             * Nilainya harus unik dan tidak boleh diubah setelah akun dibuat.
             */
            $table->string('login_id', 50)->unique();

            // Nama lengkap pengguna.
            $table->string('name', 150);

            // Pangkat atau golongan pengguna.
            $table->string('rank', 100);

            // Jabatan pengguna.
            $table->string('position', 150);

            /*
             * Password disimpan dalam bentuk hash.
             * Jangan pernah menyimpan password asli.
             */
            $table->string('password');

            /*
             * Bernilai true untuk:
             * - login pertama;
             * - setelah reset password;
             * - setelah akun diaktifkan kembali.
             */
            $table->boolean('must_change_password')->default(true);

            /*
             * Akun tidak dihapus permanen.
             * Akun cukup dinonaktifkan dengan nilai false.
             */
            $table->boolean('is_active')->default(true);

            // Menyimpan waktu login terakhir.
            $table->timestamp('last_login_at')->nullable();

            // Kolom standar Laravel untuk fitur "remember me".
            $table->rememberToken();

            // Membuat created_at dan updated_at.
            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel users ketika migration di-rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
