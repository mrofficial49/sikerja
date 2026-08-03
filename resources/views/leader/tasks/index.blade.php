@extends('layouts.app')

@section('title', 'Tugas Pimpinan - SIKERJA')

@section('content')
<div class="container-fluid py-4">

    <div
        class="d-flex flex-column flex-md-row
               justify-content-between align-items-md-center
               gap-3 mb-4"
    >
        <div>
            <h1 class="h3 fw-bold mb-1">
                Tugas Pimpinan
            </h1>

            <p class="text-secondary mb-0">
                Kelola tugas yang diberikan kepada Personel WFH.
            </p>
        </div>

        <a
            href="{{ route('leader.tasks.create') }}"
            class="btn btn-primary"
        >
            Berikan Tugas
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form
                method="GET"
                action="{{ route('leader.tasks.index') }}"
                class="row g-3"
            >
                <div class="col-md-6">
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
                        placeholder="Nama Personel atau judul tugas"
                    >
                </div>

                <div class="col-md-4">
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
                            value="not_started"
                            @selected($status === 'not_started')
                        >
                            Belum Dimulai
                        </option>

                        <option
                            value="in_progress"
                            @selected($status === 'in_progress')
                        >
                            Sedang Dikerjakan
                        </option>

                        <option
                            value="blocked"
                            @selected($status === 'blocked')
                        >
                            Terkendala
                        </option>

                        <option
                            value="completed"
                            @selected($status === 'completed')
                        >
                            Selesai
                        </option>

                        <option
                            value="cancelled"
                            @selected($status === 'cancelled')
                        >
                            Dibatalkan
                        </option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button
                        type="submit"
                        class="btn btn-primary flex-grow-1"
                    >
                        Filter
                    </button>

                    <a
                        href="{{ route('leader.tasks.index') }}"
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
                            <th>Personel</th>
                            <th>Tugas</th>
                            <th>Tanggal WFH</th>
                            <th>Status</th>
                            <th class="text-end px-4">
                                Tindakan
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($tasks as $task)
                            @php
                                $member = $task
                                    ->report
                                    ?->scheduleMember;

                                $person = $member?->user;
                                $schedule = $member?->schedule;

                                $statusClass = match ($task->status) {
                                    'completed' => 'success',
                                    'in_progress' => 'primary',
                                    'blocked' => 'danger',
                                    'cancelled' => 'secondary',
                                    default => 'warning',
                                };

                                $statusLabel = match ($task->status) {
                                    'not_started' => 'Belum Dimulai',
                                    'in_progress' => 'Dikerjakan',
                                    'blocked' => 'Terkendala',
                                    'completed' => 'Selesai',
                                    'cancelled' => 'Dibatalkan',
                                    default => $task->status,
                                };
                            @endphp

                            <tr>
                                <td class="px-4">
                                    {{
                                        $tasks->firstItem()
                                        + $loop->index
                                    }}
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $person?->name ?? '-' }}
                                    </div>

                                    <small class="text-secondary">
                                        {{ $person?->login_id ?? '-' }}
                                        ·
                                        {{ $person?->unit?->code ?? '-' }}
                                    </small>
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $task->title }}
                                    </div>

                                    <small class="text-secondary">
                                        {{
                                            \Illuminate\Support\Str::limit(
                                                $task->description,
                                                80
                                            )
                                        }}
                                    </small>
                                </td>

                                <td>
                                    {{
                                        $schedule?->wfh_date
                                            ?->translatedFormat(
                                                'd F Y'
                                            )
                                        ?? '-'
                                    }}
                                </td>

                                <td>
                                    <span
                                        class="badge
                                               text-bg-{{ $statusClass }}"
                                    >
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td class="text-end px-4">
                                    @if (
                                        ! in_array(
                                            $task->status,
                                            [
                                                'completed',
                                                'cancelled',
                                            ],
                                            true
                                        )
                                    )
                                        <form
                                            method="POST"
                                            action="{{
                                                route(
                                                    'leader.tasks.cancel',
                                                    $task
                                                )
                                            }}"
                                            class="d-inline"
                                            onsubmit="
                                                return confirm(
                                                    'Batalkan tugas ini?'
                                                );
                                            "
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="btn btn-sm
                                                       btn-outline-danger"
                                            >
                                                Batalkan
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-secondary">
                                            -
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="6"
                                    class="text-center py-5
                                           text-secondary"
                                >
                                    Belum ada tugas Pimpinan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($tasks->hasPages())
            <div class="card-footer bg-white">
                {{ $tasks->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
