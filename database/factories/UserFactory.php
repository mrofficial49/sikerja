<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Factory untuk membuat akun pengguna pada pengujian
 * dan data simulasi SIKERJA.
 *
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Password disimpan sementara agar proses factory
     * tidak melakukan hashing berulang kali.
     */
    protected static ?string $password;

    /**
     * Nilai bawaan akun pengguna.
     *
     * Role tetap harus diberikan ketika factory digunakan
     * karena setiap akun SIKERJA harus memiliki role.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            /*
             * Nilai role_id harus ditimpa saat factory dipakai.
             *
             * Contoh:
             * User::factory()->create([
             *     'role_id' => $role->id,
             * ]);
             */
            'role_id' => null,

            /*
             * Unit boleh kosong untuk kebutuhan pengujian dasar.
             */
            'unit_id' => null,

            /*
             * ID login dibuat unik untuk setiap akun.
             */
            'login_id' => fake()
                ->unique()
                ->bothify('USR-####-??'),

            'name' => fake()->name(),

            /*
             * Data kepangkatan dan jabatan simulasi.
             */
            'rank' => '-',
            'position' => 'Personel',

            /*
             * Password akun pengujian adalah: password
             */
            'password' => static::$password
                ??= Hash::make('password'),

            /*
             * Secara bawaan akun pengujian sudah mengganti
             * password sementara dan berstatus aktif.
             */
            'must_change_password' => false,
            'is_active' => true,

            'last_login_at' => null,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Membuat akun dengan status nonaktif.
     */
    public function inactive(): static
    {
        return $this->state(
            fn (array $attributes): array => [
                'is_active' => false,
            ]
        );
    }

    /**
     * Membuat akun yang masih wajib mengganti
     * password sementara.
     */
    public function mustChangePassword(): static
    {
        return $this->state(
            fn (array $attributes): array => [
                'must_change_password' => true,
            ]
        );
    }
}
