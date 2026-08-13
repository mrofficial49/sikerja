<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">

    <title>
        Rekap Kinerja WFH
    </title>

    <style>
        /*
         * CSS PDF dibuat terpisah karena Bootstrap
         * tidak digunakan oleh DOMPDF.
         */

        @page {
            margin: 24px 26px 34px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #26332d;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            line-height: 1.4;
        }

        .header-table {
            width: 100%;
            margin-bottom: 8px;
            border-collapse: collapse;
        }

        .logo-column {
            width: 85px;
            text-align: center;
            vertical-align: middle;
        }

        .logo {
            width: 63px;
            height: 63px;
            object-fit: contain;
        }

        .title-column {
            padding: 0 10px;
            text-align: center;
            vertical-align: middle;
        }

        .title-column h1 {
            margin: 0 0 4px;
            color: #173b2b;
            font-size: 17px;
            letter-spacing: 0.3px;
        }

        .title-column h2 {
            margin: 0;
            font-size: 12px;
            font-weight: normal;
        }

        .header-line {
            height: 3px;
            margin-bottom: 12px;
            border-top: 2px solid #173b2b;
            border-bottom: 1px solid #d0aa55;
        }

        .information-table {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }

        .information-table td {
            padding: 2px 4px;
            vertical-align: top;
        }

        .information-label {
            width: 85px;
            color: #59675f;
        }

        .information-separator {
            width: 8px;
        }

        .summary-table {
            width: 100%;
            margin-bottom: 13px;
            border-spacing: 5px 0;
        }

        .summary-box {
            padding: 8px;
            border: 1px solid #dbe5df;
            border-radius: 5px;
            background: #f5f8f6;
            text-align: center;
        }

        .summary-number {
            display: block;
            color: #173b2b;
            font-size: 17px;
            font-weight: bold;
        }

        .summary-label {
            color: #65736b;
            font-size: 8px;
        }

        .section-title {
            margin: 0 0 7px;
            color: #173b2b;
            font-size: 11px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .data-table th {
            padding: 6px 4px;
            border: 1px solid #9eafa5;
            color: #ffffff;
            background: #204d38;
            font-size: 8px;
            text-align: center;
            vertical-align: middle;
        }

        .data-table td {
            padding: 5px 4px;
            border: 1px solid #cbd6cf;
            vertical-align: top;
            word-wrap: break-word;
        }

        .data-table tbody tr:nth-child(even) {
            background: #f7f9f8;
        }

        .column-number {
            width: 28px;
            text-align: center;
        }

        .column-personnel {
            width: 120px;
        }

        .column-unit {
            width: 100px;
        }

        .column-attendance {
            width: 85px;
            text-align: center;
        }

        .column-work {
            width: 290px;
        }

        .column-report {
            width: 105px;
            text-align: center;
        }

        .person-name {
            color: #173b2b;
            font-weight: bold;
        }

        .small-text {
            color: #68756e;
            font-size: 7.5px;
        }

        .work-item {
            margin-bottom: 5px;
            padding-bottom: 5px;
            border-bottom: 1px dotted #cbd6cf;
        }

        .work-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .work-title {
            font-weight: bold;
        }

        .status {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 8px;
            font-size: 7px;
            font-weight: bold;
        }

        .status-success {
            color: #185b36;
            background: #dff2e7;
        }

        .status-warning {
            color: #7a5a00;
            background: #fff0c2;
        }

        .status-danger {
            color: #8c2727;
            background: #f8dada;
        }

        .status-secondary {
            color: #535f58;
            background: #e8ece9;
        }

        .empty-data {
            padding: 16px !important;
            color: #6f7d75;
            text-align: center;
        }

/*
 * =========================================================
 * RINCIAN HASIL KINERJA
 * =========================================================
 */

.detail-section {
    page-break-before: always;
    margin-top: 5px;
}

.detail-section-title {
    margin: 0 0 12px;
    padding-bottom: 6px;
    border-bottom: 2px solid #173b2b;
    color: #173b2b;
    font-size: 14px;
    text-align: center;
}

.person-detail {
    margin-bottom: 18px;
}

.person-detail-header {
    padding: 7px 9px;
    color: #ffffff;
    background: #204d38;
    font-size: 11px;
    font-weight: bold;
}

.person-information {
    width: 100%;
    margin-bottom: 10px;
    border-collapse: collapse;
}

.person-information td {
    padding: 3px 5px;
    vertical-align: top;
}

.person-information-label {
    width: 110px;
    color: #59675f;
    font-weight: bold;
}

.person-information-separator {
    width: 8px;
}

.detail-work-item {
    margin-bottom: 12px;
    padding: 9px;
    border: 1px solid #cbd6cf;
    background: #ffffff;
    page-break-inside: avoid;
}

.detail-work-title {
    margin-bottom: 7px;
    color: #173b2b;
    font-size: 11px;
    font-weight: bold;
}

.detail-meta-table {
    width: 100%;
    margin-bottom: 8px;
    border-collapse: collapse;
}

.detail-meta-table td {
    padding: 2px 4px;
    vertical-align: top;
}

.detail-meta-label {
    width: 120px;
    font-weight: bold;
}

.detail-meta-separator {
    width: 8px;
}

.detail-field {
    margin-bottom: 8px;
}

.detail-field-label {
    margin-bottom: 2px;
    color: #59675f;
    font-weight: bold;
}

.offline-note {
    margin-top: 8px;
    padding: 7px;
    border: 1px solid #d0aa55;
    background: #fff9e8;
}

        .signature-table {
            width: 100%;
            margin-top: 22px;
            page-break-inside: avoid;
            border-collapse: collapse;
        }

        .signature-table td {
            width: 50%;
            padding: 0 40px;
            text-align: center;
            vertical-align: top;
        }

        .signature-space {
            height: 48px;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }

        .footer {
            position: fixed;
            right: 0;
            bottom: -23px;
            left: 0;
            color: #7a867f;
            font-size: 7px;
            text-align: left;
        }
    </style>
</head>

<body>
    {{-- ==================================================
         KOP LAPORAN
    =================================================== --}}
    <table class="header-table">
        <tr>
            <td class="logo-column">
                @if ($logoDataUri)
                    <img
                        src="{{ $logoDataUri }}"
                        alt="Logo SIKERJA"
                        class="logo"
                    >
                @endif
            </td>

            <td class="title-column">
                <h1>
                    REKAPITULASI PELAKSANAAN WFH
                </h1>

                <h2>
                    HASIL KINERJA PERSONEL
                </h2>
            </td>

            <td class="logo-column"></td>
        </tr>
    </table>

    <div class="header-line"></div>

    {{-- ==================================================
         INFORMASI JADWAL
    =================================================== --}}
    <table class="information-table">
        <tr>
            <td class="information-label">
                Tanggal WFH
            </td>

            <td class="information-separator">:</td>

            <td>
                {{
                    $selectedSchedule
                        ->wfh_date
                        ->translatedFormat('d F Y')
                }}
            </td>

            <td class="information-label">
                Dicetak
            </td>

            <td class="information-separator">:</td>

            <td>
                {{
                    $generatedAt
                        ->translatedFormat(
                            'd F Y, H:i'
                        )
                }}
            </td>
        </tr>

        <tr>
            <td class="information-label">
                Status Jadwal
            </td>

            <td class="information-separator">:</td>

            <td>
                {{ ucfirst($selectedSchedule->status) }}
            </td>

            <td class="information-label">
                Pembuat
            </td>

            <td class="information-separator">:</td>

            <td>
                {{ $generatedBy->name }}
                —
                {{ $generatedBy->role?->name ?? '-' }}
            </td>
        </tr>

        <tr>
            <td class="information-label">
                Unit Kerja
            </td>

            <td class="information-separator">:</td>

            <td>
                {{
                    $selectedUnit?->name
                    ?? 'Semua Unit'
                }}
            </td>

            <td class="information-label">
                Pencarian
            </td>

            <td class="information-separator">:</td>

            <td>
                {{ $search !== '' ? $search : '-' }}
            </td>
        </tr>
    </table>

    {{-- ==================================================
         RINGKASAN
    =================================================== --}}
    <table class="summary-table">
        <tr>
            <td class="summary-box">
                <span class="summary-number">
                    {{ $summary['total'] }}
                </span>

                <span class="summary-label">
                    Total Personel
                </span>
            </td>

            <td class="summary-box">
                <span class="summary-number">
                    {{ $summary['checked_in'] }}
                </span>

                <span class="summary-label">
                    Sudah Check-in
                </span>
            </td>

            <td class="summary-box">
                <span class="summary-number">
                    {{ $summary['checked_out'] }}
                </span>

                <span class="summary-label">
                    Sudah Check-out
                </span>
            </td>

            <td class="summary-box">
                <span class="summary-number">
                    {{ $summary['total_items'] }}
                </span>

                <span class="summary-label">
                    Total Pekerjaan
                </span>
            </td>

            <td class="summary-box">
                <span class="summary-number">
                    {{ $summary['completed_items'] }}
                </span>

                <span class="summary-label">
                    Pekerjaan Selesai
                </span>
            </td>

            <td class="summary-box">
                <span class="summary-number">
                    {{ $summary['approved'] }}
                </span>

                <span class="summary-label">
                    Laporan Disetujui
                </span>
            </td>
        </tr>
    </table>

    {{-- ==================================================
         TABEL REKAP
    =================================================== --}}
    <h3 class="section-title">
        Rincian Hasil Kinerja Personel
    </h3>

    <table class="data-table">
        <thead>
            <tr>
                <th class="column-number">No.</th>
                <th class="column-personnel">Personel</th>
                <th class="column-unit">Unit/Jabatan</th>
                <th class="column-attendance">Presensi</th>
                <th class="column-work">
    Ringkasan Kinerja
</th>
                <th class="column-report">Status Laporan</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($members as $member)
                @php
                    $person = $member->user;
                    $attendance = $member->attendance;
                    $report = $member->workReport;
                    $workItems = $report?->items ?? collect();

$activeWorkItems = $workItems->where(
    'status',
    '!=',
    'cancelled'
);

$completedCount = $activeWorkItems
    ->where('status', 'completed')
    ->count();

$offlineCount = $activeWorkItems
    ->filter(function ($item) {
        return $item->continue_offline
            && ! in_array(
                $item->status,
                ['completed', 'cancelled'],
                true
            );
    })
    ->count();

$inProgressCount = $activeWorkItems
    ->where('status', 'in_progress')
    ->where('continue_offline', false)
    ->count();

$blockedCount = $activeWorkItems
    ->where('status', 'blocked')
    ->where('continue_offline', false)
    ->count();

$notStartedCount = $activeWorkItems
    ->where('status', 'not_started')
    ->where('continue_offline', false)
    ->count();
                    $reportLabel = match (
                        $report?->status
                    ) {
                        'draft' =>
                            'Draft',

                        'waiting_verification' =>
                            'Menunggu Verifikasi',

                        'needs_revision' =>
                            'Perlu Revisi',

                        'approved' =>
                            'Disetujui',

                        'incomplete' =>
                            'Belum Lengkap',

                        'completed_offline' =>
                            'Selesai Offline',

                        default =>
                            'Belum Ada Laporan',
                    };

                    $reportClass = match (
                        $report?->status
                    ) {
                        'approved' =>
                            'success',

                        'waiting_verification' =>
                            'warning',

                        'needs_revision' =>
                            'danger',

                        default =>
                            'secondary',
                    };
                @endphp

                <tr>
                    <td class="column-number">
                        {{ $loop->iteration }}
                    </td>

                    <td>
                        <div class="person-name">
                            {{ $person?->name ?? '-' }}
                        </div>

                        <div class="small-text">
                            {{ $person?->login_id ?? '-' }}
                            <br>
                            {{ $person?->rank ?? '-' }}
                        </div>
                    </td>

                    <td>
                        <div>
                            {{ $person?->unit?->name ?? '-' }}
                        </div>

                        <div class="small-text">
                            {{ $person?->position ?? '-' }}
                        </div>
                    </td>

                    <td class="column-attendance">
                        <strong>Masuk:</strong>

                        <br>

                        {{
                            $attendance?->checkin_at
                                ?->format('H:i')
                            ?? 'Belum'
                        }}

                        <br><br>

                        <strong>Keluar:</strong>

                        <br>

                        {{
                            $attendance?->checkout_at
                                ?->format('H:i')
                            ?? 'Belum'
                        }}
                    </td>

                   <td>
    <div class="work-title">
        {{ $workItems->count() }}
        Pekerjaan
    </div>

    <div
        class="small-text"
        style="margin-top: 5px;"
    >
        @if ($completedCount > 0)
            <div>
                Selesai:
                <strong>
                    {{ $completedCount }}
                </strong>
            </div>
        @endif

        @if ($inProgressCount > 0)
            <div>
                Sedang Dikerjakan:
                <strong>
                    {{ $inProgressCount }}
                </strong>
            </div>
        @endif

        @if ($blockedCount > 0)
            <div>
                Terkendala:
                <strong>
                    {{ $blockedCount }}
                </strong>
            </div>
        @endif

        @if ($notStartedCount > 0)
            <div>
                Belum Dimulai:
                <strong>
                    {{ $notStartedCount }}
                </strong>
            </div>
        @endif

        @if ($offlineCount > 0)
            <div>
                Dilanjutkan Offline:
                <strong>
                    {{ $offlineCount }}
                </strong>
            </div>
        @endif

        @if ($workItems->isEmpty())
            Belum ada pekerjaan.
        @endif
    </div>
</td>


                    <td class="column-report">
                        <span
                            class="status
                                   status-{{ $reportClass }}"
                        >
                            {{ $reportLabel }}
                        </span>

                        @if ($report?->verification_note)
                            <div class="small-text"
                                 style="margin-top: 5px;">
                                {{
                                    \Illuminate\Support\Str::limit(
                                        $report->verification_note,
                                        100
                                    )
                                }}
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td
                        colspan="6"
                        class="empty-data"
                    >
                        Data Personel tidak tersedia.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    {{-- ==================================================
     RINCIAN HASIL KINERJA PERSONEL
=================================================== --}}

<div class="detail-section">

    <h2 class="detail-section-title">
        RINCIAN HASIL KINERJA PERSONEL
    </h2>

    @forelse ($members as $member)

        @php
            $person = $member->user;
            $attendance = $member->attendance;
            $report = $member->workReport;

            $reportLabel = match (
                $report?->status
            ) {
                'draft' =>
                    'Draft',

                'waiting_verification' =>
                    'Menunggu Verifikasi',

                'needs_revision' =>
                    'Perlu Revisi',

                'approved' =>
                    'Disetujui',

                'incomplete' =>
                    'Belum Lengkap',

                'completed_offline' =>
                    'Selesai Offline',

                default =>
                    'Belum Ada Laporan',
            };
        @endphp

        <div class="person-detail">

            {{-- ===========================================
                 IDENTITAS PERSONEL
            ============================================ --}}

            <div class="person-detail-header">
                PERSONEL {{ $loop->iteration }}
                —
                {{ strtoupper($person?->name ?? '-') }}
            </div>

            <table class="person-information">
                <tr>
                    <td class="person-information-label">
                        NRP/NIP
                    </td>

                    <td class="person-information-separator">
                        :
                    </td>

                    <td>
                        {{ $person?->login_id ?? '-' }}
                    </td>

                    <td class="person-information-label">
                        Pangkat
                    </td>

                    <td class="person-information-separator">
                        :
                    </td>

                    <td>
                        {{ $person?->rank ?? '-' }}
                    </td>
                </tr>

                <tr>
                    <td class="person-information-label">
                        Jabatan
                    </td>

                    <td class="person-information-separator">
                        :
                    </td>

                    <td>
                        {{ $person?->position ?? '-' }}
                    </td>

                    <td class="person-information-label">
                        Unit
                    </td>

                    <td class="person-information-separator">
                        :
                    </td>

                    <td>
                        {{ $person?->unit?->name ?? '-' }}
                    </td>
                </tr>

                <tr>
                    <td class="person-information-label">
                        Check-in
                    </td>

                    <td class="person-information-separator">
                        :
                    </td>

                    <td>
                        {{
                            $attendance?->checkin_at
                                ?->format('H:i')
                            ?? 'Belum'
                        }}
                        WIB
                    </td>

                    <td class="person-information-label">
                        Check-out
                    </td>

                    <td class="person-information-separator">
                        :
                    </td>

                    <td>
                        {{
                            $attendance?->checkout_at
                                ?->format('H:i')
                            ?? 'Belum'
                        }}
                        WIB
                    </td>
                </tr>

                <tr>
                    <td class="person-information-label">
                        Status Laporan
                    </td>

                    <td class="person-information-separator">
                        :
                    </td>

                    <td colspan="4">
                        {{ $reportLabel }}
                    </td>
                </tr>
            </table>


            {{-- ===========================================
                 PEKERJAAN
            ============================================ --}}

            @forelse (
                $report?->items ?? collect()
                as $item
            )

                @php
                    /*
                     * Label status pekerjaan.
                     */
                    $itemStatusLabel = match (
                        $item->status
                    ) {
                        'not_started' =>
                            'Belum Dimulai',

                        'in_progress' =>
                            'Sedang Dikerjakan',

                        'blocked' =>
                            'Terkendala',

                        'completed' =>
                            'Selesai',

                        'cancelled' =>
                            'Dibatalkan',

                        default =>
                            ucfirst(
                                str_replace(
                                    '_',
                                    ' ',
                                    $item->status
                                )
                            ),
                    };

                    /*
                     * Asal pekerjaan.
                     */
                    $sourceLabel = match (
                        $item->source_type
                    ) {
                        'personal_plan' =>
                            'Rencana Kerja Pribadi',

                        'leader_task' =>
                            'Tugas Pimpinan',

                        default =>
                            '-',
                    };

                    /*
                     * Cara penyelesaian pekerjaan.
                     */
                    $completionLabel =
                        $item->continue_offline
                            ? 'Dilanjutkan Secara Offline'
                            : (
                                $item->status === 'completed'
                                    ? 'Selesai'
                                    : (
                                        $item->status === 'cancelled'
                                            ? 'Dibatalkan'
                                            : 'Masih Dalam Proses'
                                    )
                            );
                @endphp

                <div class="detail-work-item">

                    <div class="detail-work-title">
                        PEKERJAAN {{ $loop->iteration }}
                        —
                        {{ $item->title }}
                    </div>

                    <table class="detail-meta-table">
                        <tr>
                            <td class="detail-meta-label">
                                Jenis Pekerjaan
                            </td>

                            <td class="detail-meta-separator">
                                :
                            </td>

                            <td>
                                {{ $sourceLabel }}
                            </td>
                        </tr>

                        <tr>
                            <td class="detail-meta-label">
                                Status Pekerjaan
                            </td>

                            <td class="detail-meta-separator">
                                :
                            </td>

                            <td>
                                {{ $itemStatusLabel }}
                            </td>
                        </tr>

                        <tr>
                            <td class="detail-meta-label">
                                Penyelesaian
                            </td>

                            <td class="detail-meta-separator">
                                :
                            </td>

                            <td>
                                {{ $completionLabel }}
                            </td>
                        </tr>
                    </table>


                    <div class="detail-field">
                        <div class="detail-field-label">
                            Uraian Pekerjaan
                        </div>

                        <div>
                            @if (filled($item->description))
                                {!! nl2br(e($item->description)) !!}
                            @else
                                -
                            @endif
                        </div>
                    </div>


                    <div class="detail-field">
                        <div class="detail-field-label">
                            Target Hasil
                        </div>

                        <div>
                            @if (filled($item->target_result))
                                {!! nl2br(e($item->target_result)) !!}
                            @else
                                -
                            @endif
                        </div>
                    </div>


                    <div class="detail-field">
                        <div class="detail-field-label">
                            Progres / Hasil Pelaksanaan
                        </div>

                        <div>
                            @if (filled($item->progress))
                                {!! nl2br(e($item->progress)) !!}
                            @else
                                Belum ada progres yang dilaporkan.
                            @endif
                        </div>
                    </div>


                    <div class="detail-field">
                        <div class="detail-field-label">
                            Kendala
                        </div>

                        <div>
                            @if (filled($item->obstacle))
                                {!! nl2br(e($item->obstacle)) !!}
                            @else
                                Tidak ada kendala yang dilaporkan.
                            @endif
                        </div>
                    </div>


                    <div class="detail-field">
                        <div class="detail-field-label">
                            Rencana Tindak Lanjut
                        </div>

                        <div>
                            @if (filled($item->follow_up_plan))

                                {!! nl2br(
                                    e($item->follow_up_plan)
                                ) !!}

                            @elseif (
                                $item->status === 'completed'
                            )

                                Pekerjaan telah selesai.

                            @elseif (
                                $item->status === 'cancelled'
                            )

                                Pekerjaan telah dibatalkan.

                            @else

                                Belum ada rencana tindak lanjut.

                            @endif
                        </div>
                    </div>


                    @if ($item->continue_offline)

                        <div class="offline-note">

                            <strong>
                                Keterangan Penyelesaian
                            </strong>

                            <br>

                            Pekerjaan belum selesai pada akhir
                            sesi WFH dan akan dilanjutkan secara
                            offline sesuai rencana tindak lanjut
                            yang telah dicatat.

                        </div>

                    @endif

                </div>

            @empty

                <div class="small-text">
                    Belum ada pekerjaan yang dilaporkan.
                </div>

            @endforelse


            {{-- ===========================================
                 CATATAN VERIFIKASI
            ============================================ --}}

            @if (filled($report?->verification_note))

                <div
                    class="detail-field"
                    style="
                        margin-top: 8px;
                        padding: 7px;
                        border: 1px solid #cbd6cf;
                    "
                >
                    <div class="detail-field-label">
                        Catatan Verifikasi Pimpinan
                    </div>

                    <div>
                        {!! nl2br(
                            e($report->verification_note)
                        ) !!}
                    </div>
                </div>

            @endif

        </div>

    @empty

        <div class="empty-data">
            Belum terdapat data hasil kinerja Personel.
        </div>

    @endforelse

</div>

    {{-- ==================================================
         KOLOM PENGESAHAN
    =================================================== --}}
    <table class="signature-table">
        <tr>
            <td>
                Mengetahui,
                <br>
                Pimpinan

                <div class="signature-space"></div>

                <div class="signature-name">
                    ........................................
                </div>
            </td>

            <td>
                Dibuat oleh,
                <br>
                {{ $generatedBy->role?->name ?? 'Petugas' }}

                <div class="signature-space"></div>

                <div class="signature-name">
                    {{ $generatedBy->name }}
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Dokumen dihasilkan melalui SIKERJA pada
        {{
            $generatedAt
                ->format('d-m-Y H:i:s')
        }}
    </div>

    {{-- Nomor halaman PDF --}}
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font(
                "DejaVu Sans",
                "normal"
            );

            $pdf->page_text(
                735,
                568,
                "Halaman {PAGE_NUM} dari {PAGE_COUNT}",
                $font,
                7,
                [0.42, 0.47, 0.44]
            );
        }
    </script>
</body>
</html>