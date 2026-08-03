<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    /**
     * Mengisi data awal unit kerja.
     */
    public function run(): void
    {
        /*
         * updateOrInsert mencegah data unit menjadi ganda
         * ketika seeder dijalankan lebih dari satu kali.
         */
        DB::table('units')->updateOrInsert(
            ['code' => 'DITKUMAD'],
            [
                'name' => 'Direktorat Hukum TNI Angkatan Darat',
                'description' => 'Unit utama aplikasi SIKERJA.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
