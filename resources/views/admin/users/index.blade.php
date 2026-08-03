@extends('layouts.app')

@section('title', 'Data Pengguna - SIKERJA')

@section('content')
<div
    class="d-flex flex-column flex-md-row
           justify-content-between align-items-md-center
           gap-3 mb-4"
>
    <div>
        <h1 class="h3 fw-bold mb-1">
            Data Pengguna
        </h1>

        <p class="text-secondary mb-0">
            Kelola akun Admin, Pimpinan, dan Personel.
        </p>
    </div>

    <a
        href="{{ route('admin.users.create') }}"
        class="btn btn-sikerja"
    >
        + Tambah Pengguna
    </a>
</div>

@if (session('temporary_password'))
    <div class="alert alert-warning">
        <strong>Password sementara untuk
            {{ session('temporary_user_name') }}:</strong>

        <div class="fs-5 mt-2">
            <code>{{ session('temporary_password') }}</code>
        </div>

        <small>
            Salin password ini sekarang. Password tidak disimpan
            dalam bentuk teks asli dan tidak dapat ditampilkan kembali.
        </small>
    </div>
@endif

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form
            method="GET"
            action="{{ route('admin.users.index') }}"
            class="row g-3"
        >
            <div class="col-lg-4">
                <label
                    for="search"
                    class="form-label fw-semibold"
                >
                    Pencarian
                </label>

                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ $search }}"
                    class="form-control"
                    placeholder="ID, nama, pangkat, atau jabatan"
                >
            </div>

            <div class="col-md-4 col-lg-2">
                <label
                    for="role_id"
                    class="form-label fw-semibold"
                >
                    Role
                </label>

                <select
                    id="role_id"
                    name="role_id"
                    class="form-select"
                >
                    <option value="">Semua Role</option>

                    @foreach ($roles as $role)
                        <option
                            value="{{ $role->id }}"
                            @selected($roleId == $role->id)
                        >
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 col-lg-2">
                <label
                    for="unit_id"
                    class="form-label fw-semibold"
                >
                    Unit
                </label>

                <select
                    id="unit_id"
                    name="unit_id"
                    class="form-select"
                >
                    <option value="">Semua Unit</option>

                    @foreach ($units as $unit)
                        <option
                            value="{{ $unit->id }}"
                            @selected($unitId == $unit->id)
                        >
                            {{ $unit->code }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 col-lg-2">
                <label
                    for="status"
                    class="form-label fw-semibold"
                >
                    Status
                </label>

                <select
                    id="status"
                    name="status"
                    class="form-select"
                >
                    <option value="">Semua Status</option>

                    <option
                        value="active"
                        @selected($status === 'active')
                    >
                        Aktif
                    </option>

                    <option
                        value="inactive"
                        @selected($status === 'inactive')
                    >
                        Tidak Aktif
                    </option>
                </select>
            </div>

            <div class="col-lg-2 d-flex align-items-end gap-2">
                <button
                    type="submit"
                    class="btn btn-sikerja flex-grow-1"
                >
                    Terapkan
                </button>

                <a
                    href="{{ route('admin.users.index') }}"
                    class="btn btn-outline-secondary"
                >
                    Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4">No.</th>
                        <th>ID Login</th>
                        <th>Nama</th>
                        <th>Role</th>
                        <th>Unit</th>
                        <th>Status</th>
                        <th class="text-end px-4">Tindakan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-4">
                                {{ $users->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <span class="fw-semibold">
                                    {{ $user->login_id }}
                                </span>
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    {{ $user->name }}
                                </div>

                                <small class="text-secondary">
                                    {{ $user->rank }} —
                                    {{ $user->position }}
                                </small>
                            </td>

                            <td>
                                {{ $user->role?->name ?? '-' }}
                            </td>

                            <td>
                                {{ $user->unit?->code ?? '-' }}
                            </td>

                            <td>
                                @if ($user->is_active)
                                    <span class="badge text-bg-success">
                                        Aktif
                                    </span>
                                @else
                                    <span class="badge text-bg-secondary">
                                        Tidak Aktif
                                    </span>
                                @endif
                            </td>

                            <td class="text-end px-4">
                                <div
                                    class="d-flex justify-content-end
                                           flex-wrap gap-2"
                                >
                                    <a
                                        href="{{ route(
                                            'admin.users.edit',
                                            $user
                                        ) }}"
                                        class="btn btn-sm
                                               btn-outline-primary"
                                    >
                                        Edit
                                    </a>

                                    @if (! auth()->user()->is($user))
                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'admin.users.reset-password',
                                                $user
                                            ) }}"
                                            onsubmit="
                                                return confirm(
                                                    'Reset password akun ini?'
                                                )
                                            "
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="btn btn-sm
                                                       btn-outline-warning"
                                            >
                                                Reset Password
                                            </button>
                                        </form>

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'admin.users.toggle-status',
                                                $user
                                            ) }}"
                                            onsubmit="
                                                return confirm(
                                                    '{{ $user->is_active
                                                        ? 'Nonaktifkan akun ini?'
                                                        : 'Aktifkan kembali akun ini?'
                                                    }}'
                                                )
                                            "
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="btn btn-sm
                                                    {{
                                                        $user->is_active
                                                        ? 'btn-outline-danger'
                                                        : 'btn-outline-success'
                                                    }}"
                                            >
                                                {{
                                                    $user->is_active
                                                    ? 'Nonaktifkan'
                                                    : 'Aktifkan'
                                                }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="badge text-bg-light">
                                            Akun Anda
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="7"
                                class="text-center py-5 text-secondary"
                            >
                                Data pengguna tidak ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($users->hasPages())
        <div class="card-footer bg-white">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
