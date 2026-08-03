<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Mengisi data awal ke tabel roles.
     */
    public function run(): void
    {
        /*
         * updateOrInsert digunakan agar data tidak menjadi ganda
         * ketika seeder dijalankan lebih dari satu kali.
         */
        DB::table('roles')->updateOrInsert(
            ['name' => 'Admin'],
            [
                'description' => 'Mengelola pengguna, jadwal WFH, pengumuman, rekap, dan pengaturan sistem.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('roles')->updateOrInsert(
            ['name' => 'Pimpinan'],
            [
                'description' => 'Memberikan tugas, memeriksa laporan, dan melakukan verifikasi pekerjaan personel.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('roles')->updateOrInsert(
            ['name' => 'Personel'],
            [
                'description' => 'Melakukan presensi, membuat rencana kerja, dan mengirim laporan pekerjaan.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
