@extends('layouts.app')

@section('title', 'Detail Jadwal WFH - SIKERJA')

@section('content')
<div
    class="d-flex flex-column flex-lg-row
           justify-content-between align-items-lg-center
           gap-3 mb-4"
>
    <div>
        <h1 class="h3 fw-bold mb-1">
            Detail Jadwal WFH
        </h1>

        <p class="text-secondary mb-0">
            {{
                $wfhSchedule->wfh_date
                    ->translatedFormat('l, d F Y')
            }}
        </p>
    </div>

    <div class="d-flex flex-wrap gap-2">
        @if ($wfhSchedule->status === 'draft')
            <form
                method="POST"
                action="{{
                    route(
                        'admin.wfh-schedules.activate',
                        $wfhSchedule
                    )
                }}"
                onsubmit="return confirm('Aktifkan jadwal WFH ini?')"
            >
                @csrf
                @method('PATCH')

                <button
                    type="submit"
                    class="btn btn-success"
                >
                    Aktifkan Jadwal
                </button>
            </form>
        @endif

        @if (
            in_array(
                $wfhSchedule->status,
                ['draft', 'active'],
                true
            )
        )
            <form
                method="POST"
                action="{{
                    route(
                        'admin.wfh-schedules.cancel',
                        $wfhSchedule
                    )
                }}"
                onsubmit="return confirm('Batalkan jadwal WFH ini?')"
            >
                @csrf
                @method('PATCH')

                <button
                    type="submit"
                    class="btn btn-outline-danger"
                >
                    Batalkan Jadwal
                </button>
            </form>
        @endif

        @if ($wfhSchedule->status === 'cancelled')
            <form
                method="POST"
                action="{{
                    route(
                        'admin.wfh-schedules.restore',
                        $wfhSchedule
                    )
                }}"
                onsubmit="
                    return confirm(
                        'Gunakan kembali jadwal WFH ini? Jadwal akan dikembalikan menjadi draft.'
                    )
                "
            >
                @csrf
                @method('PATCH')

                <button
                    type="submit"
                    class="btn btn-warning"
                >
                    Gunakan Kembali
                </button>
            </form>
        @endif

        <a
            href="{{ route('admin.wfh-schedules.index') }}"
            class="btn btn-outline-secondary"
        >
            Kembali
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-secondary">Status</small>

                <div class="fw-bold fs-5 mt-1">
                    {{
                        match ($wfhSchedule->status) {
                            'draft' => 'Draft',
                            'active' => 'Aktif',
                            'completed' => 'Selesai',
                            'cancelled' => 'Dibatalkan',
                            default => $wfhSchedule->status,
                        }
                    }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-secondary">
                    Jumlah Riwayat Anggota
                </small>

                <div class="fw-bold fs-5 mt-1">
                    {{ $members->total() }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-secondary">Peserta</small>

                <div class="fw-bold fs-5 mt-1">
                    {{
                        $wfhSchedule->is_all_personnel
                            ? 'Seluruh Personel'
                            : 'Personel Terpilih'
                    }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-secondary">Dibuat Oleh</small>

                <div class="fw-bold mt-1">
                    {{ $wfhSchedule->creator?->name ?? '-' }}
                </div>
            </div>
        </div>
    </div>
</div>

@if ($wfhSchedule->notes)
    <div class="alert alert-light border mb-4">
        <strong>Catatan Admin:</strong>

        <div class="mt-1">
            {{ $wfhSchedule->notes }}
        </div>
    </div>
@endif

@if (
    in_array(
        $wfhSchedule->status,
        ['draft', 'active'],
        true
    )
)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h2 class="h5 fw-bold mb-1">
                Tambah Personel
            </h2>

            <small class="text-secondary">
                Penambahan pada hari Jumat setelah pukul 08.00
                mendapat batas check-in selama 30 menit.
            </small>
        </div>

        <div class="card-body">
            @if ($availablePersonnel->isNotEmpty())
                <form
                    method="POST"
                    action="{{
                        route(
                            'admin.wfh-schedules.members.store',
                            $wfhSchedule
                        )
                    }}"
                >
                    @csrf

                    <div class="row g-3">
                        <div class="col-lg-5">
                            <label
                                for="user_id"
                                class="form-label fw-semibold"
                            >
                                Personel
                                <span class="text-danger">*</span>
                            </label>

                            <select
                                id="user_id"
                                name="user_id"
                                class="form-select
                                    @error('user_id') is-invalid @enderror"
                                required
                            >
                                <option value="">
                                    Pilih Personel
                                </option>

                                @foreach ($availablePersonnel as $person)
                                    <option
                                        value="{{ $person->id }}"
                                        @selected(
                                            old('user_id') == $person->id
                                        )
                                    >
                                        {{ $person->name }}
                                        —
                                        {{ $person->login_id }}
                                        —
                                        {{ $person->unit?->code ?? '-' }}
                                    </option>
                                @endforeach
                            </select>

                            @error('user_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-lg-5">
                            <label
                                for="change_reason"
                                class="form-label fw-semibold"
                            >
                                Alasan Perubahan Jadwal
                            </label>

                            <input
                                type="text"
                                id="change_reason"
                                name="change_reason"
                                value="{{ old('change_reason') }}"
                                class="form-control
                                    @error('change_reason')
                                        is-invalid
                                    @enderror"
                                maxlength="1000"
                                placeholder="Wajib jika ditambahkan setelah pukul 08.00"
                            >

                            @error('change_reason')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-lg-2 d-flex align-items-end">
                            <button
                                type="submit"
                                class="btn btn-sikerja w-100"
                            >
                                Tambahkan
                            </button>
                        </div>
                    </div>
                </form>
            @else
                <div class="text-secondary">
                    Semua personel aktif sudah tercatat dalam jadwal.
                </div>
            @endif
        </div>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h2 class="h5 fw-bold mb-0">
            Daftar Personel
        </h2>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4">No.</th>
                        <th>Personel</th>
                        <th>Unit</th>
                        <th>Status Anggota</th>
                        <th>Alasan Perubahan</th>
                        <th>Batas Check-in</th>
                        <th>Presensi</th>
                        <th class="text-end px-4">Tindakan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($members as $member)
                        <tr>
                            <td class="px-4">
                                {{
                                    $members->firstItem()
                                    + $loop->index
                                }}
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    {{ $member->user?->name ?? '-' }}
                                </div>

                                <small class="text-secondary">
                                    {{ $member->user?->login_id ?? '-' }}
                                    ·
                                    {{ $member->user?->rank ?? '-' }}
                                </small>
                            </td>

                            <td>
                                {{ $member->user?->unit?->code ?? '-' }}
                            </td>

                            <td>
                                @php
                                    $memberBadge = match (
                                        $member->member_status
                                    ) {
                                        'scheduled' => 'primary',
                                        'schedule_change' => 'warning',
                                        'present' => 'success',
                                        'absent' => 'danger',
                                        'cancelled' => 'secondary',
                                        default => 'light',
                                    };

                                    $memberLabel = match (
                                        $member->member_status
                                    ) {
                                        'scheduled' => 'Dijadwalkan',
                                        'schedule_change' =>
                                            'Perubahan Jadwal',
                                        'present' => 'Hadir',
                                        'absent' => 'Tidak Hadir',
                                        'cancelled' => 'Dibatalkan',
                                        default =>
                                            $member->member_status,
                                    };
                                @endphp

                                <span
                                    class="badge text-bg-{{
                                        $memberBadge
                                    }}"
                                >
                                    {{ $memberLabel }}
                                </span>
                            </td>

                            <td>
                                {{ $member->change_reason ?: '-' }}
                            </td>

                            <td>
                                {{
                                    $member->checkin_deadline
                                        ? $member->checkin_deadline
                                            ->format('d-m-Y H:i')
                                        : '-'
                                }}
                            </td>

                            <td>
                                @if ($member->attendance?->checkin_at)
                                    <span class="badge text-bg-success">
                                        Sudah Check-in
                                    </span>
                                @else
                                    <span class="badge text-bg-secondary">
                                        Belum Check-in
                                    </span>
                                @endif
                            </td>

                            <td class="text-end px-4">
                                @if (
                                    in_array(
                                        $wfhSchedule->status,
                                        ['draft', 'active'],
                                        true
                                    )
                                    && $member->member_status
                                        !== 'cancelled'
                                    && ! $member->attendance?->checkin_at
                                )
                                    <form
                                        method="POST"
                                        action="{{
                                            route(
                                                'admin.wfh-schedules.members.cancel',
                                                [
                                                    $wfhSchedule,
                                                    $member,
                                                ]
                                            )
                                        }}"
                                        onsubmit="
                                            return confirm(
                                                'Batalkan keikutsertaan personel ini?'
                                            )
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
                                colspan="8"
                                class="text-center py-5
                                       text-secondary"
                            >
                                Jadwal belum memiliki anggota.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($members->hasPages())
        <div class="card-footer bg-white">
            {{ $members->links() }}
        </div>
    @endif
</div>
@endsection
