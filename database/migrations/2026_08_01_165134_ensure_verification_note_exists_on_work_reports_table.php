<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom catatan verifikasi
     * jika kolom tersebut belum tersedia.
     */
    public function up(): void
    {
        if (! Schema::hasColumn(
            'work_reports',
            'verification_note'
        )) {
            Schema::table(
                'work_reports',
                function (Blueprint $table) {
                    $table
                        ->text('verification_note')
                        ->nullable()
                        ->after('verified_at');
                }
            );
        }
    }

    /**
     * Menghapus kolom saat migration di-rollback.
     */
    public function down(): void
    {
        if (Schema::hasColumn(
            'work_reports',
            'verification_note'
        )) {
            Schema::table(
                'work_reports',
                function (Blueprint $table) {
                    $table->dropColumn(
                        'verification_note'
                    );
                }
            );
        }
    }
};
