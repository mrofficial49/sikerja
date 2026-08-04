@php
    /*
     * Mengambil role pengguna yang sedang login.
     */
    $currentRole = auth()->user()->role?->name;

    /*
     * Menu dasar yang tersedia untuk semua role.
     */
    $navigationItems = [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'pattern' => '*.dashboard',
        ],
    ];

    /*
     * Menu khusus Admin.
     */
    if ($currentRole === 'Admin') {
        $navigationItems = array_merge(
            $navigationItems,
            [
                [
                    'label' => 'Unit Kerja',
                    'route' => 'admin.units.index',
                    'pattern' => 'admin.units.*',
                ],
                [
                    'label' => 'Pengguna',
                    'route' => 'admin.users.index',
                    'pattern' => 'admin.users.*',
                ],
                [
                    'label' => 'Jadwal WFH',
                    'route' => 'admin.wfh-schedules.index',
                    'pattern' => 'admin.wfh-schedules.*',
                ],
                [
                    'label' => 'Monitoring & Rekap',
                    'route' => 'admin.monitoring.index',
                    'pattern' => 'admin.monitoring.*',
                ],
                [
                    'label' => 'Verifikasi Laporan',
                    'route' => 'admin.reports.index',
                    'pattern' => 'admin.reports.*',
                ],
            ]
        );
    }

    /*
     * Menu khusus Pimpinan.
     */
    if ($currentRole === 'Pimpinan') {
        $navigationItems = array_merge(
            $navigationItems,
            [
                [
                    'label' => 'Tugas Personel',
                    'route' => 'leader.tasks.index',
                    'pattern' => 'leader.tasks.*',
                ],
                [
                    'label' => 'Monitoring & Rekap',
                    'route' => 'leader.monitoring.index',
                    'pattern' => 'leader.monitoring.*',
                ],
                [
                    'label' => 'Verifikasi Laporan',
                    'route' => 'leader.reports.index',
                    'pattern' => 'leader.reports.*',
                ],
            ]
        );
    }

    /*
     * Menu khusus Personel.
     */
    if ($currentRole === 'Personel') {
        $navigationItems = array_merge(
            $navigationItems,
            [
                [
                    'label' => 'Presensi WFH',
                    'route' => 'personnel.attendance.show',
                    'pattern' => 'personnel.attendance.*',
                ],
                [
                    'label' => 'Pekerjaan',
                    'route' => 'personnel.work-items.index',
                    'pattern' => 'personnel.work-items.*',
                ],
                [
                    'label' => 'Laporan & Check-out',
                    'route' => 'personnel.report.show',
                    'pattern' => 'personnel.report.*',
                ],
            ]
        );
    }

    /*
     * Notifikasi tersedia untuk seluruh role.
     */
    $navigationItems[] = [
        'label' => 'Notifikasi',
        'route' => 'notifications.index',
        'pattern' => 'notifications.*',
        'notification' => true,
    ];
@endphp

<nav class="sikerja-nav-list">
    @foreach ($navigationItems as $navigationItem)
        <a
            href="{{ route($navigationItem['route']) }}"
            class="sikerja-nav-link
                {{
                    request()->routeIs(
                        $navigationItem['pattern']
                    )
                        ? 'active'
                        : ''
                }}"
        >
            <span class="sikerja-nav-indicator"></span>

            <span class="sikerja-nav-label">
                {{ $navigationItem['label'] }}
            </span>

            @if (
                ($navigationItem['notification'] ?? false)
                && $navbarUnreadNotificationCount > 0
            )
                <span class="sikerja-nav-badge">
                    {{
                        $navbarUnreadNotificationCount > 99
                            ? '99+'
                            : $navbarUnreadNotificationCount
                    }}
                </span>
            @endif
        </a>
    @endforeach
</nav>
