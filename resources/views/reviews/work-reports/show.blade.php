@extends('layouts.app')

@section('title', 'Detail Verifikasi Laporan')

@section('content')
@php
    /*
     * Menyimpan data relasi dalam variabel pendek
     * supaya kode halaman lebih mudah dibaca.
     */
    $member = $workReport->scheduleMember;
    $person = $member?->user;
    $schedule = $member?->schedule;
    $attendance = $member?->attendance;

    /*
     * Menentukan warna dan tulisan status laporan.
     */
    $reportStatusClass = match ($workReport->status) {
        'waiting_verification' => 'warning',
        'needs_revision' => 'danger',
        'approved' => 'success',
        default => 'secondary',
    };

    $reportStatusLabel = match ($workReport->status) {
        'waiting_verification' => 'Menunggu Verifikasi',
        'needs_revision' => 'Perlu Revisi',
        'approved' => 'Disetujui',
        default => ucfirst($workReport->status),
    };
@endphp

<div class="container-fluid py-4">

    {{-- Judul halaman dan tombol kembali --}}
    <div
        class="d-flex flex-column flex-md-row
               justify-content-between align-items-md-center
               gap-3 mb-4"
    >
        <div>
            <h1 class="h3 fw-bold mb-1">
                Detail Laporan WFH
            </h1>

            <p class="text-secondary mb-0">
                Periksa hasil pekerjaan dan bukti pendukung Personel.
            </p>
        </div>

        <a
            href="{{ route($reviewRoutePrefix . '.index') }}"
            class="btn btn-outline-secondary"
        >
            Kembali ke Daftar
        </a>
    </div>

    {{-- Pesan berhasil --}}
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

    {{-- Pesan kesalahan --}}
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

    {{-- Kesalahan validasi form --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-bold mb-2">
                Periksa kembali data berikut:
            </div>

            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Ringkasan identitas dan status --}}
    <div class="row g-4 mb-4">

        {{-- Identitas Personel --}}
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h2 class="h5 fw-bold mb-0">
                        Identitas Personel
                    </h2>
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <div class="small text-secondary">
                            Nama
                        </div>

                        <div class="fw-semibold">
                            {{ $person?->name ?? '-' }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-secondary">
                            NRP/NIP atau ID Login
                        </div>

                        <div class="fw-semibold">
                            {{ $person?->login_id ?? '-' }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-secondary">
                            Pangkat
                        </div>

                        <div class="fw-semibold">
                            {{ $person?->rank ?? '-' }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-secondary">
                            Jabatan
                        </div>

                        <div class="fw-semibold">
                            {{ $person?->position ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="small text-secondary">
                            Unit Kerja
                        </div>

                        <div class="fw-semibold">
                            {{ $person?->unit?->name ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Informasi jadwal --}}
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h2 class="h5 fw-bold mb-0">
                        Informasi WFH
                    </h2>
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <div class="small text-secondary">
                            Tanggal WFH
                        </div>

                        <div class="fw-semibold">
                            @if ($schedule?->wfh_date)
                                {{
                                    $schedule
                                        ->wfh_date
                                        ->translatedFormat('d F Y')
                                }}
                            @else
                                -
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-secondary">
                            Status Laporan
                        </div>

                        <span
                            class="badge text-bg-{{ $reportStatusClass }}"
                        >
                            {{ $reportStatusLabel }}
                        </span>
                    </div>

                    <div class="mb-3">
                        <div class="small text-secondary">
                            Waktu Pengiriman
                        </div>

                        <div class="fw-semibold">
                            {{
                                $workReport->submitted_at
                                    ?->format('d-m-Y H:i:s')
                                ?? '-'
                            }}
                        </div>
                    </div>

                    <div>
                        <div class="small text-secondary">
                            Jumlah Pekerjaan
                        </div>

                        <div class="fw-semibold">
                            {{ $workReport->items->count() }}
                            pekerjaan
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Informasi presensi --}}
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h2 class="h5 fw-bold mb-0">
                        Presensi Personel
                    </h2>
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <div class="small text-secondary">
                            Check-in
                        </div>

                        <div class="fw-semibold">
                            {{
                                $attendance?->checkin_at
                                    ?->format('d-m-Y H:i:s')
                                ?? 'Belum check-in'
                            }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-secondary">
                            Check-out
                        </div>

                        <div class="fw-semibold">
                            {{
                                $attendance?->checkout_at
                                    ?->format('d-m-Y H:i:s')
                                ?? 'Belum check-out'
                            }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-secondary">
                            Status Presensi
                        </div>

                        @if ($attendance?->attendance_status === 'present')
                            <span class="badge text-bg-success">
                                Hadir
                            </span>
                        @elseif ($attendance?->attendance_status)
                            <span class="badge text-bg-secondary">
                                {{ ucfirst($attendance->attendance_status) }}
                            </span>
                        @else
                            <span class="badge text-bg-secondary">
                                Belum Tersedia
                            </span>
                        @endif
                    </div>

                    <div>
                        <div class="small text-secondary">
                            Kelengkapan Check-out
                        </div>

                        @if ($attendance?->checkout_at)
                            <span class="badge text-bg-success">
                                Selesai
                            </span>
                        @else
                            <span class="badge text-bg-warning">
                                Belum Selesai
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Menampilkan catatan verifikasi sebelumnya --}}
    @if ($workReport->verification_note)
        <div
            class="alert
                {{
                    $workReport->status === 'needs_revision'
                        ? 'alert-danger'
                        : 'alert-light border'
                }}"
        >
            <div class="fw-bold">
                Catatan Verifikasi
            </div>

            <div class="mt-1">
                {{ $workReport->verification_note }}
            </div>

            @if ($workReport->verified_at)
                <div class="small mt-2">
                    Dicatat pada
                    {{
                        $workReport
                            ->verified_at
                            ->format('d-m-Y H:i:s')
                    }}
                </div>
            @endif
        </div>
    @endif

    {{-- Daftar pekerjaan Personel --}}
    <div class="mb-4">
        <h2 class="h4 fw-bold mb-3">
            Rincian Pekerjaan
        </h2>

        @forelse ($workReport->items as $item)
            @php
                /*
                 * Warna status pekerjaan.
                 */
                $itemStatusClass = match ($item->status) {
                    'completed' => 'success',
                    'in_progress' => 'primary',
                    'blocked' => 'danger',
                    'cancelled' => 'secondary',
                    'not_started' => 'warning',
                    default => 'secondary',
                };

                /*
                 * Tulisan status pekerjaan.
                 */
                $itemStatusLabel = match ($item->status) {
                    'completed' => 'Selesai',
                    'in_progress' => 'Sedang Dikerjakan',
                    'blocked' => 'Terkendala',
                    'cancelled' => 'Dibatalkan',
                    'not_started' => 'Belum Dimulai',
                    default => ucfirst($item->status),
                };

                /*
                 * Sumber pekerjaan.
                 */
                $sourceLabel = match ($item->source_type) {
                    'leader_task' => 'Tugas Pimpinan',
                    'personal_plan' => 'Rencana Pribadi',
                    default => 'Pekerjaan',
                };
            @endphp

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div
                        class="d-flex flex-column flex-md-row
                               justify-content-between
                               align-items-md-start gap-3"
                    >
                        <div>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <span class="badge text-bg-light">
                                    {{ $sourceLabel }}
                                </span>

                                <span
                                    class="badge
                                           text-bg-{{ $itemStatusClass }}"
                                >
                                    {{ $itemStatusLabel }}
                                </span>
                            </div>

                            <h3 class="h5 fw-bold mb-0">
                                {{ $item->title }}
                            </h3>
                        </div>

                        <div class="text-md-end">
                            <div class="small text-secondary">
                                Pekerjaan ke-
                                {{ $loop->iteration }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-4">

                        {{-- Uraian dan target --}}
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <div class="small text-secondary">
                                    Uraian Pekerjaan
                                </div>

                                <div class="mt-1">
                                    {{ $item->description ?: '-' }}
                                </div>
                            </div>

                            <div>
                                <div class="small text-secondary">
                                    Target Hasil
                                </div>

                                <div class="mt-1">
                                    {{ $item->target_result ?: '-' }}
                                </div>
                            </div>
                        </div>

                        {{-- Progres, kendala, tindak lanjut --}}
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <div class="small text-secondary">
                                    Progres atau Hasil Pekerjaan
                                </div>

                                <div class="mt-1">
                                    {{ $item->progress ?: '-' }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="small text-secondary">
                                    Kendala
                                </div>

                                <div class="mt-1">
                                    {{ $item->obstacle ?: '-' }}
                                </div>
                            </div>

                            <div>
                                <div class="small text-secondary">
                                    Rencana Tindak Lanjut
                                </div>

                                <div class="mt-1">
                                    {{ $item->follow_up_plan ?: '-' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Bukti PDF pekerjaan --}}
                    <div>
                        <div class="fw-semibold mb-2">
                            Bukti Pekerjaan
                        </div>

                        @forelse ($item->files as $file)
                            <div
                                class="d-flex flex-column flex-md-row
                                       justify-content-between
                                       align-items-md-center
                                       border rounded p-3 mb-2 gap-3"
                            >
                                <div>
                                    <div class="fw-semibold">
                                        {{ $file->original_name }}
                                    </div>

                                    <div class="small text-secondary">
                                        @if ($file->file_size)
                                            {{
                                                number_format(
                                                    $file->file_size / 1024,
                                                    1,
                                                    ',',
                                                    '.'
                                                )
                                            }}
                                            KB
                                        @endif

                                        @if ($file->uploaded_at)
                                            · Diunggah
                                            {{
                                                $file
                                                    ->uploaded_at
                                                    ->format(
                                                        'd-m-Y H:i'
                                                    )
                                            }}
                                        @endif
                                    </div>

                                    @if ($file->description)
                                        <div class="small mt-1">
                                            {{ $file->description }}
                                        </div>
                                    @endif
                                </div>

                                <a
                                    href="{{
                                        route(
                                            $reviewRoutePrefix
                                                . '.files.download',
                                            [
                                                'workReport' =>
                                                    $workReport->id,

                                                'workItemFile' =>
                                                    $file->id,
                                            ]
                                        )
                                    }}"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    Unduh PDF
                                </a>
                            </div>
                        @empty
                            <div
                                class="alert alert-light border mb-0"
                            >
                                Personel tidak mengunggah bukti PDF
                                untuk pekerjaan ini.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-warning">
                Laporan ini belum memiliki rincian pekerjaan.
            </div>
        @endforelse
    </div>

    {{-- Bagian tindakan hanya tampil saat menunggu verifikasi --}}
    @if ($workReport->status === 'waiting_verification')

        {{-- Peringatan jika belum check-out --}}
        @if (! $attendance?->checkout_at)
            <div class="alert alert-warning">
                <strong>Personel belum melakukan check-out.</strong>

                Laporan dapat diperiksa, tetapi belum dapat
                disetujui sampai proses check-out selesai.
            </div>
        @endif

        <div class="row g-4">

            {{-- Form persetujuan --}}
            <div class="col-lg-6">
                <div class="card border-success shadow-sm h-100">
                    <div class="card-header bg-success text-white py-3">
                        <h2 class="h5 mb-0">
                            Setujui Laporan
                        </h2>
                    </div>

                    <div class="card-body">
                        <p class="text-secondary">
                            Gunakan bagian ini apabila seluruh
                            pekerjaan dan bukti dinilai sudah sesuai.
                        </p>

                        <form
                            method="POST"
                            action="{{
                                route(
                                    $reviewRoutePrefix . '.approve',
                                    $workReport
                                )
                            }}"
                            onsubmit="
                                return confirm(
                                    'Apakah Anda yakin ingin menyetujui laporan ini?'
                                );
                            "
                        >
                            @csrf
                            @method('PATCH')

                            <div class="mb-3">
                                <label
                                    for="approval_note"
                                    class="form-label fw-semibold"
                                >
                                    Catatan Persetujuan
                                </label>

                                <textarea
                                    id="approval_note"
                                    name="verification_note"
                                    rows="5"
                                    maxlength="2000"
                                    class="form-control"
                                    placeholder="Catatan bersifat opsional"
                                >{{ old('verification_note') }}</textarea>

                                <div class="form-text">
                                    Maksimal 2.000 karakter.
                                </div>
                            </div>

                            <button
                                type="submit"
                                class="btn btn-success"
                                @disabled(! $attendance?->checkout_at)
                            >
                                Setujui Laporan
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Form permintaan revisi --}}
            <div class="col-lg-6">
                <div class="card border-danger shadow-sm h-100">
                    <div class="card-header bg-danger text-white py-3">
                        <h2 class="h5 mb-0">
                            Minta Revisi
                        </h2>
                    </div>

                    <div class="card-body">
                        <p class="text-secondary">
                            Jelaskan bagian laporan yang harus
                            diperbaiki oleh Personel.
                        </p>

                        <form
                            method="POST"
                            action="{{
                                route(
                                    $reviewRoutePrefix . '.revision',
                                    $workReport
                                )
                            }}"
                            onsubmit="
                                return confirm(
                                    'Kembalikan laporan ini untuk diperbaiki?'
                                );
                            "
                        >
                            @csrf
                            @method('PATCH')

                            <div class="mb-3">
                                <label
                                    for="revision_note"
                                    class="form-label fw-semibold"
                                >
                                    Catatan Revisi
                                    <span class="text-danger">*</span>
                                </label>

                                <textarea
                                    id="revision_note"
                                    name="verification_note"
                                    rows="5"
                                    maxlength="2000"
                                    class="form-control"
                                    placeholder="Contoh: Mohon lengkapi hasil pekerjaan dan unggah bukti PDF yang sesuai."
                                    required
                                >{{ old('verification_note') }}</textarea>

                                <div class="form-text">
                                    Catatan revisi wajib diisi.
                                </div>
                            </div>

                            <button
                                type="submit"
                                class="btn btn-danger"
                            >
                                Kembalikan untuk Revisi
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    @elseif ($workReport->status === 'approved')

        {{-- Informasi laporan sudah disetujui --}}
        <div class="alert alert-success">
            <div class="fw-bold">
                Laporan ini sudah disetujui.
            </div>

            @if ($workReport->verified_at)
                <div class="mt-1">
                    Waktu verifikasi:
                    {{
                        $workReport
                            ->verified_at
                            ->format('d-m-Y H:i:s')
                    }}
                </div>
            @endif
        </div>

    @elseif ($workReport->status === 'needs_revision')

        {{-- Informasi laporan sedang direvisi --}}
        <div class="alert alert-danger">
            <div class="fw-bold">
                Laporan telah dikembalikan untuk revisi.
            </div>

            <div class="mt-1">
                Personel perlu memperbaiki laporan dan
                mengirimkannya kembali.
            </div>
        </div>

    @endif
</div>
@endsection
