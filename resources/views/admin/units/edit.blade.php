@extends('layouts.app')

@section('title', 'Edit Unit - SIKERJA')

@section('content')
<div class="mb-4">
    <h1 class="h3 fw-bold mb-1">
        Edit Unit Kerja
    </h1>

    <p class="text-secondary mb-0">
        Perbarui kode, nama, atau keterangan unit.
    </p>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form
            method="POST"
            action="{{ route('admin.units.update', $unit) }}"
        >
            @csrf
            @method('PUT')

            @include('admin.units._form', [
                'unit' => $unit,
                'submitLabel' => 'Simpan Perubahan',
            ])
        </form>
    </div>
</div>
@endsection
