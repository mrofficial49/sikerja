@extends('layouts.app')

@section('title', 'Bukti Presensi - SIKERJA')

@section('content')
@php
    /*
     * Menyiapkan data yang berkaitan
     * dengan presensi.
     */
    $member = $attendance->scheduleMember;
    $person = $member?->user;
    $schedule = $member?->schedule;

    /*
     * Mengecek ketersediaan data check-in.
     */
    $hasCheckinPhoto =
        filled($attendance->checkin_photo_path)
        && ! $attendance->checkin_photo_deleted_at;

    $hasCheckinLocation =
        filled($attendance->checkin_latitude)
        && filled($attendance->checkin_longitude);

    /*
     * Mengecek ketersediaan data check-out.
     */
    $hasCheckoutPhoto =
        filled($attendance->checkout_photo_path)
        && ! $attendance->checkout_photo_deleted_at;

    $hasCheckoutLocation =
        filled($attendance->checkout_latitude)
        && filled($attendance->checkout_longitude);
@endphp

<div class="mb-4">
    <div
        class="d-flex flex-column flex-md-row
               justify-content-between align-items-md-center
               gap-3"
    >
        <div>
            <h1 class="h3 fw-bold mb-1">
                Bukti Presensi WFH
            </h1>

            <p class="text-secondary mb-0">
                Foto, waktu, dan koordinat GPS
                check-in serta check-out.
            </p>
        </div>

        <button
            type="button"
            class="btn btn-outline-secondary"
            onclick="history.back()"
        >
            Kembali
        </button>
    </div>
</div>

{{-- Identitas Personel --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h2 class="h5 fw-bold mb-0">
            Identitas Personel
        </h2>
    </div>

    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6 col-xl-3">
                <small class="text-secondary">
                    Nama
                </small>

                <div class="fw-semibold">
                    {{ $person?->name ?? '-' }}
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <small class="text-secondary">
                    Pangkat
                </small>

                <div class="fw-semibold">
                    {{ $person?->rank ?? '-' }}
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <small class="text-secondary">
                    Jabatan
                </small>

                <div class="fw-semibold">
                    {{ $person?->position ?? '-' }}
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <small class="text-secondary">
                    Unit
                </small>

                <div class="fw-semibold">
                    {{ $person?->unit?->name ?? '-' }}
                </div>
            </div>

            <div class="col-md-6">
                <small class="text-secondary">
                    Tanggal WFH
                </small>

                <div class="fw-semibold">
                    {{
                        $schedule?->wfh_date
                            ?->translatedFormat(
                                'l, d F Y'
                            )
                        ?? '-'
                    }}
                </div>
            </div>

            <div class="col-md-6">
                <small class="text-secondary">
                    Status Presensi
                </small>

                <div>
                    @if (
                        $attendance->attendance_status
                        === 'present'
                    )
                        <span class="badge text-bg-success">
                            Hadir
                        </span>
                    @elseif (
                        $attendance->attendance_status
                        === 'absent'
                    )
                        <span class="badge text-bg-danger">
                            Tidak Hadir
                        </span>
                    @else
                        <span class="badge text-bg-warning">
                            Belum Lengkap
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- ==================================================
         BUKTI CHECK-IN
    =================================================== --}}
    <div class="col-xl-6">
        <div class="card border-0 shadow-sm h-100">
            <div
                class="card-header bg-white py-3
                       d-flex justify-content-between
                       align-items-center"
            >
                <h2 class="h5 fw-bold mb-0">
                    Bukti Check-in
                </h2>

                @if ($attendance->checkin_at)
                    <span class="badge text-bg-success">
                        Tersimpan
                    </span>
                @endif
            </div>

            <div class="card-body">
                @if ($hasCheckinPhoto)
                    <div class="text-center mb-3">
                        <img
                            src="{{
                                route(
                                    'attendance.evidence.photo',
                                    [
                                        'attendance' =>
                                            $attendance->id,

                                        'type' => 'checkin',
                                    ]
                                )
                            }}"
                            alt="Foto check-in {{ $person?->name }}"
                            class="img-fluid rounded border"
                            style="
                                width: 100%;
                                max-height: 420px;
                                object-fit: contain;
                                background: #f8f9fa;
                            "
                        >
                    </div>
                @else
                    <div
                        class="alert alert-secondary
                               text-center"
                    >
                        Foto check-in tidak tersedia.
                    </div>
                @endif

                <div class="row g-3">
                    <div class="col-sm-6">
                        <small class="text-secondary">
                            Waktu Check-in
                        </small>

                        <div class="fw-semibold">
                            {{
                                $attendance->checkin_at
                                    ?->format(
                                        'd-m-Y H:i:s'
                                    )
                                ?? '-'
                            }}
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <small class="text-secondary">
                            Status Check-in
                        </small>

                        <div class="fw-semibold">
                            {{
                                match (
                                    $attendance
                                        ->checkin_status
                                ) {
                                    'on_time' =>
                                        'Tepat Waktu',

                                    'late' =>
                                        'Terlambat',

                                    'missed' =>
                                        'Tidak Check-in',

                                    default => '-',
                                }
                            }}
                        </div>
                    </div>

                    <div class="col-12">
                        <small class="text-secondary">
                            Koordinat GPS
                        </small>

                        <div class="fw-semibold">
                            @if ($hasCheckinLocation)
                                {{
                                    $attendance
                                        ->checkin_latitude
                                }},
                                {{
                                    $attendance
                                        ->checkin_longitude
                                }}
                            @else
                                -
                            @endif
                        </div>
                    </div>

                    @if ($hasCheckinLocation)
                        <div class="col-12">
                            <a
                                href="https://www.google.com/maps?q={{
                                    $attendance->checkin_latitude
                                }},{{
                                    $attendance->checkin_longitude
                                }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn btn-outline-success"
                            >
                                Buka Lokasi Check-in
                            </a>
                        </div>
                    @endif

                    @if ($attendance->checkin_reason)
                        <div class="col-12">
                            <small class="text-secondary">
                                Alasan Keterlambatan
                            </small>

                            <div class="border rounded p-3">
                                {{
                                    $attendance
                                        ->checkin_reason
                                }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ==================================================
         BUKTI CHECK-OUT
    =================================================== --}}
    <div class="col-xl-6">
        <div class="card border-0 shadow-sm h-100">
            <div
                class="card-header bg-white py-3
                       d-flex justify-content-between
                       align-items-center"
            >
                <h2 class="h5 fw-bold mb-0">
                    Bukti Check-out
                </h2>

                @if ($attendance->checkout_at)
                    <span class="badge text-bg-primary">
                        Tersimpan
                    </span>
                @endif
            </div>

            <div class="card-body">
                @if ($hasCheckoutPhoto)
                    <div class="text-center mb-3">
                        <img
                            src="{{
                                route(
                                    'attendance.evidence.photo',
                                    [
                                        'attendance' =>
                                            $attendance->id,

                                        'type' => 'checkout',
                                    ]
                                )
                            }}"
                            alt="Foto check-out {{ $person?->name }}"
                            class="img-fluid rounded border"
                            style="
                                width: 100%;
                                max-height: 420px;
                                object-fit: contain;
                                background: #f8f9fa;
                            "
                        >
                    </div>
                @else
                    <div
                        class="alert alert-secondary
                               text-center"
                    >
                        Foto check-out belum tersedia.
                    </div>
                @endif

                <div class="row g-3">
                    <div class="col-sm-6">
                        <small class="text-secondary">
                            Waktu Check-out
                        </small>

                        <div class="fw-semibold">
                            {{
                                $attendance->checkout_at
                                    ?->format(
                                        'd-m-Y H:i:s'
                                    )
                                ?? '-'
                            }}
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <small class="text-secondary">
                            Status Check-out
                        </small>

                        <div class="fw-semibold">
                            {{
                                $attendance
                                    ->checkout_at
                                    ? 'Selesai'
                                    : 'Belum Check-out'
                            }}
                        </div>
                    </div>

                    <div class="col-12">
                        <small class="text-secondary">
                            Koordinat GPS
                        </small>

                        <div class="fw-semibold">
                            @if ($hasCheckoutLocation)
                                {{
                                    $attendance
                                        ->checkout_latitude
                                }},
                                {{
                                    $attendance
                                        ->checkout_longitude
                                }}
                            @else
                                -
                            @endif
                        </div>
                    </div>

                    @if ($hasCheckoutLocation)
                        <div class="col-12">
                            <a
                                href="https://www.google.com/maps?q={{
                                    $attendance->checkout_latitude
                                }},{{
                                    $attendance->checkout_longitude
                                }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn btn-outline-primary"
                            >
                                Buka Lokasi Check-out
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Informasi masa simpan --}}
<div class="card border-0 shadow-sm mt-4">
    <div class="card-body">
        <small class="text-secondary">
            Masa Simpan Foto
        </small>

        <div class="row g-2 mt-1">
            <div class="col-md-6">
                Foto check-in:
                <strong>
                    {{
                        $attendance
                            ->checkin_photo_expires_at
                            ?->format('d-m-Y H:i')
                        ?? '-'
                    }}
                </strong>
            </div>

            <div class="col-md-6">
                Foto check-out:
                <strong>
                    {{
                        $attendance
                            ->checkout_photo_expires_at
                            ?->format('d-m-Y H:i')
                        ?? '-'
                    }}
                </strong>
            </div>
        </div>
    </div>
</div>
@endsection
