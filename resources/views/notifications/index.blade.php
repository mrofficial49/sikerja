@extends('layouts.app')

@section('title', 'Notifikasi - SIKERJA')

@section('content')
<div class="container-fluid py-4">

    {{-- Judul halaman --}}
    <div
        class="d-flex flex-column flex-md-row
               justify-content-between align-items-md-center
               gap-3 mb-4"
    >
        <div>
            <h1 class="h3 fw-bold mb-1">
                Pusat Notifikasi
            </h1>

            <p class="text-secondary mb-0">
                Informasi terbaru mengenai jadwal, tugas,
                laporan, dan hasil verifikasi.
            </p>
        </div>

        @if ($unreadCount > 0)
            <form
                method="POST"
                action="{{ route('notifications.read-all') }}"
                onsubmit="
                    return confirm(
                        'Tandai seluruh notifikasi sebagai dibaca?'
                    );
                "
            >
                @csrf
                @method('PATCH')

                <button
                    type="submit"
                    class="btn btn-outline-primary"
                >
                    Tandai Semua Dibaca
                </button>
            </form>
        @endif
    </div>

    {{-- Pesan berhasil --}}
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Ringkasan dan filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div
                class="d-flex flex-column flex-md-row
                       justify-content-between align-items-md-center
                       gap-3"
            >
                <div>
                    <span class="badge text-bg-danger">
                        {{ $unreadCount }}
                        belum dibaca
                    </span>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a
                        href="{{ route('notifications.index') }}"
                        class="btn btn-sm
                            {{
                                ! $status
                                    ? 'btn-primary'
                                    : 'btn-outline-primary'
                            }}"
                    >
                        Semua
                    </a>

                    <a
                        href="{{
                            route(
                                'notifications.index',
                                ['status' => 'unread']
                            )
                        }}"
                        class="btn btn-sm
                            {{
                                $status === 'unread'
                                    ? 'btn-primary'
                                    : 'btn-outline-primary'
                            }}"
                    >
                        Belum Dibaca
                    </a>

                    <a
                        href="{{
                            route(
                                'notifications.index',
                                ['status' => 'read']
                            )
                        }}"
                        class="btn btn-sm
                            {{
                                $status === 'read'
                                    ? 'btn-primary'
                                    : 'btn-outline-primary'
                            }}"
                    >
                        Sudah Dibaca
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar notifikasi --}}
    <div class="card border-0 shadow-sm">
        <div class="list-group list-group-flush">
            @forelse ($notifications as $notification)
                @php
                    /*
                     * Menentukan warna berdasarkan
                     * jenis notifikasi.
                     */
                    $typeClass = match ($notification->type) {
                        'leader_task' => 'primary',

                        'work_report_revision' =>
                            'danger',

                        'work_report_approved' =>
                            'success',

                        'work_report_submitted' =>
                            'warning',

                        'schedule_created',
                        'schedule_changed' =>
                            'info',

                        default =>
                            'secondary',
                    };
                @endphp

                <a
                    href="{{
                        route(
                            'notifications.open',
                            $notification
                        )
                    }}"
                    class="list-group-item
                           list-group-item-action
                           py-3
                           {{
                               ! $notification->is_read
                                   ? 'bg-light'
                                   : ''
                           }}"
                >
                    <div
                        class="d-flex flex-column flex-md-row
                               justify-content-between gap-3"
                    >
                        <div class="d-flex gap-3">
                            {{-- Penanda belum dibaca --}}
                            <div>
                                <span
                                    class="badge
                                           text-bg-{{ $typeClass }}"
                                >
                                    {{
                                        $notification->is_read
                                            ? 'Dibaca'
                                            : 'Baru'
                                    }}
                                </span>
                            </div>

                            <div>
                                <div
                                    class="
                                        {{
                                            ! $notification->is_read
                                                ? 'fw-bold'
                                                : 'fw-semibold'
                                        }}"
                                >
                                    {{ $notification->title }}
                                </div>

                                <div class="text-secondary mt-1">
                                    {{ $notification->message }}
                                </div>
                            </div>
                        </div>

                        <div
                            class="small text-secondary
                                   text-md-end flex-shrink-0"
                        >
                            {{
                                $notification
                                    ->created_at
                                    ->diffForHumans()
                            }}

                            <div>
                                {{
                                    $notification
                                        ->created_at
                                        ->format('d-m-Y H:i')
                                }}
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="text-center py-5 text-secondary">
                    Tidak ada notifikasi.
                </div>
            @endforelse
        </div>

        @if ($notifications->hasPages())
            <div class="card-footer bg-white">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
