<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Menampilkan daftar pengguna.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $roleId = $request->input('role_id');
        $unitId = $request->input('unit_id');
        $status = $request->input('status');

        $users = User::query()
            ->with(['role', 'unit'])

            // Cari berdasarkan ID login, nama, pangkat, atau jabatan.
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('login_id', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('rank', 'like', "%{$search}%")
                        ->orWhere('position', 'like', "%{$search}%");
                });
            })

            // Filter berdasarkan role.
            ->when($roleId, function ($query) use ($roleId) {
                $query->where('role_id', $roleId);
            })

            // Filter berdasarkan unit.
            ->when($unitId, function ($query) use ($unitId) {
                $query->where('unit_id', $unitId);
            })

            // Filter status akun.
            ->when($status === 'active', function ($query) {
                $query->where('is_active', true);
            })
            ->when($status === 'inactive', function ($query) {
                $query->where('is_active', false);
            })

            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $roles = Role::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $units = Unit::query()
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('admin.users.index', compact(
            'users',
            'roles',
            'units',
            'search',
            'roleId',
            'unitId',
            'status'
        ));
    }

    /**
     * Menampilkan formulir tambah pengguna.
     */
    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => Role::where('is_active', true)
                ->orderBy('name')
                ->get(),

            'units' => Unit::where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Menyimpan pengguna baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->normalizeInput($request, true);

        $validated = $request->validate(
            $this->validationRules(),
            $this->validationMessages()
        );

        $role = Role::findOrFail($validated['role_id']);

        // Proyek SIKERJA hanya menggunakan satu akun Pimpinan.
        $this->ensureSingleLeader($role);

        $unitId = $this->resolveUnitId(
            $role,
            $validated['unit_id'] ?? null
        );

        $temporaryPassword = $this->generateTemporaryPassword();

        $user = User::create([
            'role_id' => $role->id,
            'unit_id' => $unitId,
            'login_id' => $validated['login_id'],
            'name' => $validated['name'],
            'rank' => $validated['rank'],
            'position' => $validated['position'],

            /*
             * Model User memakai cast "hashed",
             * sehingga password otomatis disimpan dalam bentuk hash.
             */
            'password' => $temporaryPassword,
            'must_change_password' => true,
            'is_active' => true,
        ]);

        $this->writeLog(
            $request,
            'user_created',
            "Admin membuat akun {$user->login_id} atas nama {$user->name}.",
            $user
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Akun pengguna berhasil dibuat.')
            ->with('temporary_password', $temporaryPassword)
            ->with('temporary_user_name', $user->name);
    }

    /**
     * Menampilkan formulir edit pengguna.
     */
    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'roles' => Role::where('is_active', true)
                ->orderBy('name')
                ->get(),

            'units' => Unit::where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Memperbarui data pengguna.
     *
     * login_id tidak ikut diperbarui karena bersifat permanen.
     */
    public function update(
        Request $request,
        User $user
    ): RedirectResponse {
        $this->normalizeInput($request, false);

        $rules = $this->validationRules(false);

        $validated = $request->validate(
            $rules,
            $this->validationMessages()
        );

        $role = Role::findOrFail($validated['role_id']);

        $this->ensureSingleLeader($role, $user);

        $unitId = $this->resolveUnitId(
            $role,
            $validated['unit_id'] ?? null
        );

        $user->update([
            'role_id' => $role->id,
            'unit_id' => $unitId,
            'name' => $validated['name'],
            'rank' => $validated['rank'],
            'position' => $validated['position'],
        ]);

        $this->writeLog(
            $request,
            'user_updated',
            "Admin memperbarui profil akun {$user->login_id}.",
            $user
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Profil pengguna berhasil diperbarui.');
    }

    /**
     * Mengaktifkan atau menonaktifkan akun.
     */
    public function toggleStatus(
        Request $request,
        User $user
    ): RedirectResponse {
        // Admin tidak boleh menonaktifkan akun sendiri.
        if ($request->user()->is($user)) {
            return back()->with(
                'error',
                'Anda tidak dapat menonaktifkan akun sendiri.'
            );
        }

        $newStatus = ! $user->is_active;

        /*
         * Ketika akun diaktifkan kembali, sistem membuat
         * password sementara baru.
         */
        if ($newStatus) {
            $temporaryPassword = $this->generateTemporaryPassword();

            $user->forceFill([
                'password' => $temporaryPassword,
                'must_change_password' => true,
                'is_active' => true,
            ])->save();

            $this->writeLog(
                $request,
                'user_activated',
                "Admin mengaktifkan kembali akun {$user->login_id}.",
                $user
            );

            return back()
                ->with('success', 'Akun berhasil diaktifkan kembali.')
                ->with('temporary_password', $temporaryPassword)
                ->with('temporary_user_name', $user->name);
        }

        $user->update([
            'is_active' => false,
        ]);

        $this->writeLog(
            $request,
            'user_deactivated',
            "Admin menonaktifkan akun {$user->login_id}.",
            $user
        );

        return back()->with(
            'success',
            'Akun berhasil dinonaktifkan.'
        );
    }

    /**
     * Membuat password sementara baru.
     */
    public function resetPassword(
        Request $request,
        User $user
    ): RedirectResponse {
        // Hindari Admin mereset akun sendiri melalui menu ini.
        if ($request->user()->is($user)) {
            return back()->with(
                'error',
                'Gunakan menu ganti password untuk akun Anda sendiri.'
            );
        }

        $temporaryPassword = $this->generateTemporaryPassword();

        $user->forceFill([
            'password' => $temporaryPassword,
            'must_change_password' => true,
        ])->save();

        $this->writeLog(
            $request,
            'password_reset',
            "Admin mereset password akun {$user->login_id}.",
            $user
        );

        return back()
            ->with('success', 'Password pengguna berhasil direset.')
            ->with('temporary_password', $temporaryPassword)
            ->with('temporary_user_name', $user->name);
    }

    /**
     * Aturan validasi formulir pengguna.
     */
    private function validationRules(
        bool $includeLoginId = true
    ): array {
        $rules = [
            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id')->where(
                    fn ($query) => $query->where('is_active', true)
                ),
            ],

            'unit_id' => [
                'nullable',
                'integer',
                Rule::exists('units', 'id')->where(
                    fn ($query) => $query->where('is_active', true)
                ),
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'rank' => [
                'required',
                'string',
                'max:100',
            ],

            'position' => [
                'required',
                'string',
                'max:150',
            ],
        ];

        if ($includeLoginId) {
            $rules['login_id'] = [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z0-9._-]+$/',
                Rule::unique('users', 'login_id'),
            ];
        }

        return $rules;
    }

    /**
     * Pesan validasi Bahasa Indonesia.
     */
    private function validationMessages(): array
    {
        return [
            'login_id.required' => 'ID Login wajib diisi.',
            'login_id.max' => 'ID Login maksimal 50 karakter.',
            'login_id.regex' =>
                'ID Login hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.',
            'login_id.unique' => 'ID Login sudah digunakan.',

            'role_id.required' => 'Role wajib dipilih.',
            'role_id.exists' => 'Role yang dipilih tidak valid.',

            'unit_id.exists' => 'Unit yang dipilih tidak valid.',

            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama maksimal 150 karakter.',

            'rank.required' => 'Pangkat atau golongan wajib diisi.',
            'rank.max' => 'Pangkat maksimal 100 karakter.',

            'position.required' => 'Jabatan wajib diisi.',
            'position.max' => 'Jabatan maksimal 150 karakter.',
        ];
    }

    /**
     * Membersihkan dan menyeragamkan input.
     */
    private function normalizeInput(
        Request $request,
        bool $includeLoginId
    ): void {
        $data = [
            'name' => trim((string) $request->input('name')),
            'rank' => trim((string) $request->input('rank')),
            'position' => trim((string) $request->input('position')),
        ];

        if ($includeLoginId) {
            $data['login_id'] = strtoupper(
                trim((string) $request->input('login_id'))
            );
        }

        $request->merge($data);
    }

    /**
     * Personel wajib memiliki unit.
     * Admin dan Pimpinan tidak diwajibkan memiliki unit.
     */
    private function resolveUnitId(
        Role $role,
        mixed $unitId
    ): ?int {
        if ($role->name === 'Personel') {
            if (! $unitId) {
                throw ValidationException::withMessages([
                    'unit_id' =>
                        'Unit kerja wajib dipilih untuk akun Personel.',
                ]);
            }

            return (int) $unitId;
        }

        return null;
    }

    /**
     * Memastikan hanya terdapat satu akun Pimpinan.
     */
    private function ensureSingleLeader(
        Role $role,
        ?User $currentUser = null
    ): void {
        if ($role->name !== 'Pimpinan') {
            return;
        }

        $leaderExists = User::query()
            ->where('role_id', $role->id)
            ->when(
                $currentUser,
                fn ($query) => $query->whereKeyNot($currentUser->id)
            )
            ->exists();

        if ($leaderExists) {
            throw ValidationException::withMessages([
                'role_id' =>
                    'SIKERJA hanya menggunakan satu akun Pimpinan.',
            ]);
        }
    }

    /**
     * Membuat password sementara yang memenuhi syarat:
     * huruf besar, huruf kecil, angka, dan simbol.
     */
    private function generateTemporaryPassword(): string
    {
        return 'Sik#'
            . Str::upper(Str::random(3))
            . random_int(1000, 9999)
            . 'a';
    }

    /**
     * Menulis aktivitas ke tabel activity_logs.
     *
     * Password sementara tidak pernah ditulis ke log.
     */
    private function writeLog(
        Request $request,
        string $action,
        string $description,
        User $user
    ): void {
        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => $action,
            'description' => $description,
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
