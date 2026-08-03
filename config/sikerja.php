<?php

return [
    /*
     * Mode pengujian presensi.
     *
     * true:
     * - Hanya berlaku ketika APP_ENV=local.
     * - Jadwal aktif dapat diuji di luar tanggal dan jam WFH.
     *
     * false:
     * - Aturan hari dan jam WFH berlaku penuh.
     */
    'attendance_test_mode' => env(
        'ATTENDANCE_TEST_MODE',
        false
    ),
];
