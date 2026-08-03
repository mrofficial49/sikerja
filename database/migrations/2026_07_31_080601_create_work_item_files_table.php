<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel work_item_files.
     *
     * Tabel ini menyimpan informasi file PDF
     * hasil pekerjaan Personel.
     */
    public function up(): void
    {
        Schema::create('work_item_files', function (Blueprint $table) {
            // Primary key BIGINT UNSIGNED AUTO_INCREMENT.
            $table->id();

            /*
             * Kegiatan yang memiliki file ini.
             *
             * Satu kegiatan dapat memiliki lebih dari satu
             * file hasil pekerjaan.
             */
            $table->foreignId('item_id')
                ->constrained('work_items')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Nama asli file saat diunggah oleh pengguna.
            $table->string('original_name');

            /*
             * Nama file yang dibuat oleh sistem.
             *
             * Nama ini digunakan untuk mencegah benturan
             * ketika ada file dengan nama yang sama.
             */
            $table->string('stored_name');

            // Lokasi penyimpanan file pada server.
            $table->string('file_path', 500);

            // Keterangan tambahan file, boleh kosong.
            $table->text('description')->nullable();

            /*
             * Ukuran file dalam satuan byte.
             *
             * Aturan maksimal 5 MB akan diperiksa
             * melalui validasi Laravel.
             */
            $table->unsignedBigInteger('file_size');

            /*
             * Jenis file.
             *
             * Untuk SIKERJA nantinya hanya diizinkan:
             * application/pdf
             */
            $table->string('mime_type', 100);

            // Pengguna yang mengunggah file.
            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Waktu file diunggah.
            $table->timestamp('uploaded_at')->useCurrent();

            /*
             * File fisik akan disimpan maksimal 30 hari.
             * Kolom ini menyimpan tanggal kedaluwarsanya.
             */
            $table->timestamp('expires_at')->nullable();

            /*
             * Waktu file dihapus secara fisik.
             *
             * Metadata tetap tersimpan walaupun file
             * sudah dihapus dari server.
             */
            $table->timestamp('deleted_at')->nullable();

            /*
             * true  : file masih tersedia.
             * false : file sudah tidak tersedia.
             */
            $table->boolean('is_available')->default(true);

            // Membuat created_at dan updated_at.
            $table->timestamps();

            // Mempercepat pencarian file berdasarkan kegiatan.
            $table->index('item_id');

            // Mempercepat pencarian file yang sudah kedaluwarsa.
            $table->index('expires_at');

            // Mempercepat filter file tersedia atau tidak.
            $table->index('is_available');
        });
    }

    /**
     * Menghapus tabel work_item_files saat rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_item_files');
    }
};
