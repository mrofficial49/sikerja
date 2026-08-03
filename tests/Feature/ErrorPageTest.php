<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\View;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    /**
     * Memastikan seluruh halaman error khusus tersedia
     * dan dapat dirender tanpa kesalahan Blade.
     */
    public function test_custom_error_views_can_be_rendered(): void
    {
        $errorPages = [
            '403' => 'Akses Ditolak',
            '404' => 'Halaman Tidak Ditemukan',
            '419' => 'Sesi Telah Berakhir',
            '422' => 'Permintaan Tidak Dapat Diproses',
            '500' => 'Terjadi Gangguan Sistem',
        ];

        foreach ($errorPages as $code => $title) {
            /*
             * Memastikan file view benar-benar tersedia.
             */
            $this->assertTrue(
                View::exists('errors.'.$code),
                'View error '.$code.' tidak ditemukan.'
            );

            /*
             * Merender view untuk memastikan tidak ada
             * kesalahan sintaks atau variabel Blade.
             */
            $html = view('errors.'.$code)->render();

            $this->assertStringContainsString(
                $code,
                $html
            );

            $this->assertStringContainsString(
                $title,
                $html
            );

            $this->assertStringContainsString(
                'SIKERJA',
                $html
            );
        }
    }

    /**
     * Memastikan alamat yang tidak tersedia tetap
     * menghasilkan status HTTP 404.
     */
    public function test_unknown_url_returns_not_found(): void
    {
        $this
            ->get('/alamat-yang-tidak-tersedia')
            ->assertNotFound();
    }
}
