@extends('layouts.app')

@section('title', 'Edit Pengguna - SIKERJA')

@section('content')
<div class="mb-4">
    <h1 class="h3 fw-bold mb-1">
        Edit Pengguna
    </h1>

    <p class="text-secondary mb-0">
        Perbarui profil pengguna tanpa mengubah ID Login.
    </p>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form
            method="POST"
            action="{{ route('admin.users.update', $user) }}"
        >
            @csrf
            @method('PUT')

            @include('admin.users._form', [
                'user' => $user,
                'isEdit' => true,
                'submitLabel' => 'Simpan Perubahan',
            ])
        </form>
    </div>
</div>
@endsection
