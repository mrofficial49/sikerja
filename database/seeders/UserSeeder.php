<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Mengisi akun awal Admin dan Pimpinan.
     */
    public function run(): void
    {
        // Mengambil ID role Admin dari tabel roles.
        $adminRole = Role::where('name', 'Admin')->firstOrFail();

        // Mengambil ID role Pimpinan dari tabel roles.
        $leaderRole = Role::where('name', 'Pimpinan')->firstOrFail();

        /*
         * Membuat atau mengambil akun Admin.
         *
         * firstOrNew digunakan agar password tidak selalu
         * di-reset ketika seeder dijalankan ulang.
         */
        $admin = User::firstOrNew([
            'login_id' => 'ADMIN001',
        ]);

        $admin->role_id = $adminRole->id;
        $admin->unit_id = null;
        $admin->name = 'Administrator SIKERJA';
        $admin->rank = 'Admin';
        $admin->position = 'Administrator Sistem';
        $admin->must_change_password = true;
        $admin->is_active = true;

        // Password hanya dibuat ketika akun masih baru.
        if (! $admin->exists) {
            $admin->password = Hash::make('SikerjaAdmin#2026');
        }

        $admin->save();

        /*
         * Membuat atau mengambil akun Pimpinan.
         */
        $leader = User::firstOrNew([
            'login_id' => 'PIMPINAN001',
        ]);

        $leader->role_id = $leaderRole->id;
        $leader->unit_id = null;
        $leader->name = 'Pimpinan SIKERJA';
        $leader->rank = 'Pimpinan';
        $leader->position = 'Pimpinan';
        $leader->must_change_password = true;
        $leader->is_active = true;

        // Password hanya dibuat ketika akun masih baru.
        if (! $leader->exists) {
            $leader->password = Hash::make('SikerjaPimpinan#2026');
        }

        $leader->save();
    }
}
