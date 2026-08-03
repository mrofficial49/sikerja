<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    /*
     * Setiap test menggunakan database baru.
     *
     * Karena phpunit.xml menggunakan SQLite :memory:,
     * database hanya hidup selama pengujian berlangsung.
     */
    use RefreshDatabase;

    /**
     * Membuat akun berdasarkan nama role.
     */
    private function createUser(
        string $roleName,
        bool $isActive = true,
        bool $mustChangePassword = false
    ): User {
        /*
         * Membuat role apabila role tersebut
         * belum tersedia di database pengujian.
         */
        $role = Role::query()->firstOrCreate(
            [
                'name' => $roleName,
            ],
            [
                'description' =>
                    'Role pengujian '.$roleName,

                'is_active' => true,
            ]
        );

        /*
         * Membuat akun pengujian yang terhubung
         * dengan role tersebut.
         */
        return User::factory()->create([
            'role_id' => $role->id,
            'position' => $roleName,
            'is_active' => $isActive,

            'must_change_password' =>
                $mustChangePassword,
        ]);
    }

    /**
     * Pengunjung yang belum login harus diarahkan
     * menuju halaman login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $protectedRoutes = [
            'dashboard',
            'admin.dashboard',
            'admin.reports.index',
            'admin.monitoring.index',
            'leader.dashboard',
            'leader.tasks.index',
            'leader.reports.index',
            'leader.monitoring.index',
            'personnel.dashboard',
            'notifications.index',
        ];

        foreach ($protectedRoutes as $routeName) {
            $this
                ->get(route($routeName))
                ->assertRedirect(route('login'));
        }
    }

    /**
     * Admin boleh membuka area Admin, tetapi tidak boleh
     * membuka dashboard Pimpinan atau Personel.
     */
    public function test_admin_access_is_restricted_by_role(): void
    {
        $admin = $this->createUser('Admin');

        $this->actingAs($admin);

        /*
         * Halaman yang boleh dibuka Admin.
         */
        $this
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this
            ->get(route('admin.reports.index'))
            ->assertOk();

        $this
            ->get(route('admin.monitoring.index'))
            ->assertOk();

        $this
            ->get(route('notifications.index'))
            ->assertOk();

        /*
         * Halaman yang tidak boleh dibuka Admin.
         */
        $this
            ->get(route('leader.dashboard'))
            ->assertForbidden();

        $this
            ->get(route('leader.tasks.index'))
            ->assertForbidden();

        $this
            ->get(route('personnel.dashboard'))
            ->assertForbidden();
    }

    /**
     * Pimpinan boleh membuka area Pimpinan, tetapi tidak
     * boleh membuka area Admin dan Personel.
     */
    public function test_leader_access_is_restricted_by_role(): void
    {
        $leader = $this->createUser('Pimpinan');

        $this->actingAs($leader);

        /*
         * Halaman yang boleh dibuka Pimpinan.
         */
        $this
            ->get(route('leader.dashboard'))
            ->assertOk();

        $this
            ->get(route('leader.tasks.index'))
            ->assertOk();

        $this
            ->get(route('leader.reports.index'))
            ->assertOk();

        $this
            ->get(route('leader.monitoring.index'))
            ->assertOk();

        $this
            ->get(route('notifications.index'))
            ->assertOk();

        /*
         * Halaman yang tidak boleh dibuka Pimpinan.
         */
        $this
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        $this
            ->get(route('admin.reports.index'))
            ->assertForbidden();

        $this
            ->get(route('personnel.dashboard'))
            ->assertForbidden();
    }

    /**
     * Personel hanya boleh membuka area Personel
     * dan pusat notifikasi.
     */
    public function test_personnel_access_is_restricted_by_role(): void
    {
        $personnel = $this->createUser('Personel');

        $this->actingAs($personnel);

        /*
         * Halaman yang boleh dibuka Personel.
         */
        $this
            ->get(route('personnel.dashboard'))
            ->assertOk();

        $this
            ->get(route('notifications.index'))
            ->assertOk();

        /*
         * Halaman yang tidak boleh dibuka Personel.
         */
        $this
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        $this
            ->get(route('admin.reports.index'))
            ->assertForbidden();

        $this
            ->get(route('leader.dashboard'))
            ->assertForbidden();

        $this
            ->get(route('leader.tasks.index'))
            ->assertForbidden();
    }

    /**
     * Akun nonaktif harus dikeluarkan dari sistem,
     * meskipun sebelumnya memiliki session login.
     */
    public function test_inactive_user_is_logged_out(): void
    {
        $inactiveAdmin = $this->createUser(
            roleName: 'Admin',
            isActive: false
        );

        $response = $this
            ->actingAs($inactiveAdmin)
            ->get(route('admin.reports.index'));

        /*
         * Pengguna diarahkan kembali ke login.
         */
        $response
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('login_id');

        /*
         * Session autentikasi sudah dihapus.
         */
        $this->assertGuest();
    }

    /**
     * Akun yang masih memakai password sementara harus
     * diarahkan ke halaman perubahan password.
     */
    public function test_temporary_password_user_is_redirected(): void
    {
        $leader = $this->createUser(
            roleName: 'Pimpinan',
            mustChangePassword: true
        );

        $response = $this
            ->actingAs($leader)
            ->get(route('leader.tasks.index'));

        $response->assertRedirect(
            route('password.change')
        );

        /*
         * Pengguna tetap login karena hanya diwajibkan
         * mengganti password, bukan dikeluarkan.
         */
        $this->assertAuthenticatedAs($leader);
    }
}
