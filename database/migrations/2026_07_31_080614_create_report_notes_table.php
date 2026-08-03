<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel report_notes.
     *
     * Tabel ini menyimpan catatan, koreksi,
     * atau arahan dari Pimpinan.
     */
    public function up(): void
    {
        Schema::create('report_notes', function (Blueprint $table) {
            // Primary key BIGINT UNSIGNED AUTO_INCREMENT.
            $table->id();

            /*
             * Laporan kerja yang diberi catatan.
             *
             * Satu laporan dapat mempunyai lebih dari
             * satu catatan dari Pimpinan.
             */
            $table->foreignId('report_id')
                ->constrained('work_reports')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Pimpinan yang membuat catatan.
            $table->foreignId('leader_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Isi catatan atau arahan dari Pimpinan.
            $table->text('note');

            // Membuat created_at dan updated_at.
            $table->timestamps();

            // Mempercepat pencarian catatan berdasarkan laporan.
            $table->index('report_id');

            // Mempercepat pencarian catatan berdasarkan Pimpinan.
            $table->index('leader_id');
        });
    }

    /**
     * Menghapus tabel report_notes saat rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_notes');
    }
};
