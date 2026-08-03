@extends('layouts.app')

@section('title', 'Jadwal WFH - SIKERJA')

@section('content')
<div
    class="d-flex flex-column flex-md-row
           justify-content-between align-items-md-center
           gap-3 mb-4"
>
    <div>
        <h1 class="h3 fw-bold mb-1">
            Jadwal WFH
        </h1>

        <p class="text-secondary mb-0">
            Kelola jadwal WFH hari Jumat dan daftar personelnya.
        </p>
    </div>

    <a
        href="{{ route('admin.wfh-schedules.create') }}"
        class="btn btn-sikerja"
    >
        + Buat Jadwal
    </a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form
            method="GET"
            action="{{ route('admin.wfh-schedules.index') }}"
            class="row g-3"
        >
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
                    <option value="draft" @selected($status === 'draft')>
                        Draft
                    </option>
                    <option value="active" @selected($status === 'active')>
                        Aktif
                    </option>
                    <option value="completed" @selected($status === 'completed')>
                        Selesai
                    </option>
                    <option value="cancelled" @selected($status === 'cancelled')>
                        Dibatalkan
                    </option>
                </select>
            </div>

            <div class="col-md-3">
                <label
                    for="month"
                    class="form-label fw-semibold"
                >
                    Bulan
                </label>

                <select
                    id="month"
                    name="month"
                    class="form-select"
                >
                    <option value="">Semua Bulan</option>

                    @for ($number = 1; $number <= 12; $number++)
                        <option
                            value="{{ $number }}"
                            @selected((int) $month === $number)
                        >
                            {{ str_pad($number, 2, '0', STR_PAD_LEFT) }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="col-md-3">
                <label
                    for="year"
                    class="form-label fw-semibold"
                >
                    Tahun
                </label>

                <input
                    type="number"
                    id="year"
                    name="year"
                    value="{{ $year }}"
                    class="form-control"
                    min="2026"
                    max="2100"
                    placeholder="Contoh: 2026"
                >
            </div>

            <div class="col-md-2 d-flex align-items-end gap-2">
                <button
                    type="submit"
                    class="btn btn-sikerja flex-grow-1"
                >
                    Terapkan
                </button>

                <a
                    href="{{ route('admin.wfh-schedules.index') }}"
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
                        <th>Tanggal</th>
                        <th>Anggota</th>
                        <th>Pilihan</th>
                        <th>Status</th>
                        <th>Dibuat Oleh</th>
                        <th class="text-end px-4">Tindakan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($schedules as $schedule)
                        <tr>
                            <td class="px-4">
                                {{
                                    $schedules->firstItem()
                                    + $loop->index
                                }}
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    {{
                                        $schedule->wfh_date
                                            ->translatedFormat('d F Y')
                                    }}
                                </div>

                                <small class="text-secondary">
                                    Jumat
                                </small>
                            </td>

                            <td>
                                {{ $schedule->members_count }} personel
                            </td>

                            <td>
                                {{
                                    $schedule->is_all_personnel
                                        ? 'Seluruh Personel'
                                        : 'Personel Terpilih'
                                }}
                            </td>

                            <td>
                                @php
                                    $badge = match ($schedule->status) {
                                        'draft' => 'warning',
                                        'active' => 'success',
                                        'completed' => 'primary',
                                        'cancelled' => 'secondary',
                                        default => 'light',
                                    };

                                    $statusLabel = match ($schedule->status) {
                                        'draft' => 'Draft',
                                        'active' => 'Aktif',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Dibatalkan',
                                        default => $schedule->status,
                                    };
                                @endphp

                                <span class="badge text-bg-{{ $badge }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>

                            <td>
                                {{ $schedule->creator?->name ?? '-' }}
                            </td>

                            <td class="text-end px-4">
                                <a
                                    href="{{
                                        route(
                                            'admin.wfh-schedules.show',
                                            $schedule
                                        )
                                    }}"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="7"
                                class="text-center py-5 text-secondary"
                            >
                                Jadwal WFH belum tersedia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($schedules->hasPages())
        <div class="card-footer bg-white">
            {{ $schedules->links() }}
        </div>
    @endif
</div>
@endsection
