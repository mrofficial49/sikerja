<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Menjalankan seluruh seeder secara berurutan.
     */
    public function run(): void
    {
        $this->call([
            // Role harus dibuat lebih dahulu.
            RoleSeeder::class,

            // Setelah itu membuat unit.
            UnitSeeder::class,

            // User membutuhkan data role.
            UserSeeder::class,

            // Pengaturan awal aplikasi.
            SystemSettingSeeder::class,
        ]);
    }
}
