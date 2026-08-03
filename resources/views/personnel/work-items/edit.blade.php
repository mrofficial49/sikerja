@extends('layouts.app')

@section('title', 'Edit Rencana Kerja - SIKERJA')

@section('content')
<div class="mb-4">
    <h1 class="h3 fw-bold mb-1">
        Edit Rencana Kerja
    </h1>

    <p class="text-secondary mb-0">
        Perbarui uraian atau target hasil pekerjaan.
    </p>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form
            method="POST"
            action="{{
                route(
                    'personnel.work-items.update',
                    $workItem
                )
            }}"
        >
            @csrf
            @method('PUT')

            @include('personnel.work-items._form', [
                'workItem' => $workItem,
                'submitLabel' => 'Simpan Perubahan',
                'requiresChangeReason' =>
                    $requiresChangeReason,
            ])
        </form>
    </div>
</div>
@endsection
