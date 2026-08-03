<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Mendaftarkan service aplikasi.
     */
    public function register(): void
    {
        //
    }

    /**
     * Menjalankan pengaturan awal aplikasi.
     */
    public function boot(): void
    {
        /*
         * Menggunakan tampilan pagination Bootstrap 5
         * agar sesuai dengan desain SIKERJA.
         */
        Paginator::useBootstrapFive();
    }
}
