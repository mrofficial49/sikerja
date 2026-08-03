@extends('layouts.app')

@section('title', 'Verifikasi Laporan WFH')

@section('content')
<div class="container-fluid py-4">

    {{-- Judul halaman --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">
                Verifikasi Laporan WFH
            </h1>

            <p class="text-secondary mb-0">
                Periksa laporan kerja yang telah dikirim oleh Personel.
            </p>
        </div>
    </div>

    {{-- Menampilkan pesan berhasil --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Tutup"
            ></button>
        </div>
    @endif

    {{-- Menampilkan pesan kesalahan --}}
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Tutup"
            ></button>
        </div>
    @endif

    {{-- Form filter laporan --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form
                method="GET"
                action="{{ route($reviewRoutePrefix . '.index') }}"
                class="row g-3"
            >
                {{-- Pencarian Personel --}}
                <div class="col-lg-3">
                    <label
                        for="search"
                        class="form-label fw-semibold"
                    >
                        Cari Personel
                    </label>

                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="Nama, NRP/NIP, pangkat..."
                    >
                </div>

                {{-- Filter status --}}
                <div class="col-md-4 col-lg-3">
                    <label
                        for="status"
                        class="form-label fw-semibold"
                    >
                        Status Laporan
                    </label>

                    <select
                        id="status"
                        name="status"
                        class="form-select"
                    >
                        <option value="">
                            Semua Status
                        </option>

                        <option
                            value="waiting_verification"
                            @selected(
                                $status === 'waiting_verification'
                            )
                        >
                            Menunggu Verifikasi
                        </option>

                        <option
                            value="needs_revision"
                            @selected(
                                $status === 'needs_revision'
                            )
                        >
                            Perlu Revisi
                        </option>

                        <option
                            value="approved"
                            @selected(
                                $status === 'approved'
                            )
                        >
                            Disetujui
                        </option>
                    </select>
                </div>

                {{-- Filter tanggal WFH --}}
                <div class="col-md-4 col-lg-2">
                    <label
                        for="date"
                        class="form-label fw-semibold"
                    >
                        Tanggal WFH
                    </label>

                    <input
                        type="date"
                        id="date"
                        name="date"
                        value="{{ $date }}"
                        class="form-control"
                    >
                </div>

                {{-- Filter unit kerja --}}
                <div class="col-md-4 col-lg-2">
                    <label
                        for="unit_id"
                        class="form-label fw-semibold"
                    >
                        Unit Kerja
                    </label>

                    <select
                        id="unit_id"
                        name="unit_id"
                        class="form-select"
                    >
                        <option value="">
                            Semua Unit
                        </option>

                        @foreach ($units as $unit)
                            <option
                                value="{{ $unit->id }}"
                                @selected(
                                    (string) $unitId
                                    === (string) $unit->id
                                )
                            >
                                {{ $unit->code ?? $unit->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tombol filter --}}
                <div class="col-lg-2 d-flex align-items-end gap-2">
                    <button
                        type="submit"
                        class="btn btn-primary flex-grow-1"
                    >
                        Filter
                    </button>

                    <a
                        href="{{ route($reviewRoutePrefix . '.index') }}"
                        class="btn btn-outline-secondary"
                    >
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel daftar laporan --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h5 fw-bold mb-0">
                    Daftar Laporan
                </h2>

                <span class="badge text-bg-secondary">
                    {{ $reports->total() }} laporan
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4 py-3">
                                No.
                            </th>

                            <th class="py-3">
                                Personel
                            </th>

                            <th class="py-3">
                                Tanggal WFH
                            </th>

                            <th class="py-3">
                                Jumlah Pekerjaan
                            </th>

                            <th class="py-3">
                                Check-out
                            </th>

                            <th class="py-3">
                                Status
                            </th>

                            <th class="text-end px-4 py-3">
                                Tindakan
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($reports as $report)
                            @php
                                /*
                                 * Mengambil data terkait agar kode
                                 * pada tabel lebih mudah dibaca.
                                 */
                                $member = $report->scheduleMember;
                                $person = $member?->user;
                                $schedule = $member?->schedule;
                                $attendance = $member?->attendance;

                                /*
                                 * Menentukan warna dan tulisan badge
                                 * berdasarkan status laporan.
                                 */
                                $statusClass = match ($report->status) {
                                    'waiting_verification' => 'warning',
                                    'needs_revision' => 'danger',
                                    'approved' => 'success',
                                    default => 'secondary',
                                };

                                $statusLabel = match ($report->status) {
                                    'waiting_verification' =>
                                        'Menunggu Verifikasi',

                                    'needs_revision' =>
                                        'Perlu Revisi',

                                    'approved' =>
                                        'Disetujui',

                                    default =>
                                        ucfirst($report->status),
                                };
                            @endphp

                            <tr>
                                {{-- Nomor urut mengikuti pagination --}}
                                <td class="px-4">
                                    {{
                                        $reports->firstItem()
                                        + $loop->index
                                    }}
                                </td>

                                {{-- Identitas Personel --}}
                                <td>
                                    <div class="fw-semibold">
                                        {{ $person?->name ?? '-' }}
                                    </div>

                                    <div class="small text-secondary">
                                        {{ $person?->login_id ?? '-' }}
                                    </div>

                                    <div class="small text-secondary">
                                        {{ $person?->rank ?? '-' }}

                                        @if ($person?->position)
                                            · {{ $person->position }}
                                        @endif
                                    </div>

                                    <div class="small text-secondary">
                                        {{ $person?->unit?->code ?? '-' }}
                                    </div>
                                </td>

                                {{-- Tanggal pelaksanaan WFH --}}
                                <td>
                                    @if ($schedule?->wfh_date)
                                        {{
                                            $schedule
                                                ->wfh_date
                                                ->translatedFormat(
                                                    'd F Y'
                                                )
                                        }}
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- Jumlah pekerjaan dalam laporan --}}
                                <td>
                                    <span class="badge text-bg-light">
                                        {{ $report->items_count }}
                                        pekerjaan
                                    </span>
                                </td>

                                {{-- Status check-out --}}
                                <td>
                                    @if ($attendance?->checkout_at)
                                        <span class="badge text-bg-success">
                                            Sudah Check-out
                                        </span>

                                        <div class="small text-secondary mt-1">
                                            {{
                                                $attendance
                                                    ->checkout_at
                                                    ->format('H:i')
                                            }}
                                            WIB
                                        </div>
                                    @else
                                        <span class="badge text-bg-secondary">
                                            Belum Check-out
                                        </span>
                                    @endif
                                </td>

                                {{-- Status verifikasi laporan --}}
                                <td>
                                    <span
                                        class="badge text-bg-{{ $statusClass }}"
                                    >
                                        {{ $statusLabel }}
                                    </span>

                                    @if ($report->submitted_at)
                                        <div class="small text-secondary mt-1">
                                            Dikirim
                                            {{
                                                $report
                                                    ->submitted_at
                                                    ->format(
                                                        'd-m-Y H:i'
                                                    )
                                            }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Tombol membuka detail laporan --}}
                                <td class="text-end px-4">
                                    <a
                                        href="{{
                                            route(
                                                $reviewRoutePrefix
                                                    . '.show',
                                                $report
                                            )
                                        }}"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        Periksa
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="7"
                                    class="text-center py-5"
                                >
                                    <div class="text-secondary">
                                        Belum ada laporan yang sesuai
                                        dengan filter.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Navigasi halaman --}}
        @if ($reports->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
