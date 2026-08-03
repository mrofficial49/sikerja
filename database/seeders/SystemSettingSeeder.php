<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemSettingSeeder extends Seeder
{
    /**
     * Mengisi pengaturan awal aplikasi SIKERJA.
     */
    public function run(): void
    {
        $settings = [
            [
                'setting_key' => 'app_name',
                'setting_value' => 'SIKERJA',
                'data_type' => 'string',
                'description' => 'Nama aplikasi.',
                'is_public' => true,
            ],
            [
                'setting_key' => 'app_subtitle',
                'setting_value' => 'Sistem Informasi Kinerja dan Aktivitas Personel',
                'data_type' => 'string',
                'description' => 'Subjudul aplikasi.',
                'is_public' => true,
            ],
            [
                'setting_key' => 'timezone',
                'setting_value' => 'Asia/Jakarta',
                'data_type' => 'string',
                'description' => 'Zona waktu aplikasi.',
                'is_public' => false,
            ],
            [
                'setting_key' => 'checkin_start_time',
                'setting_value' => '07:00',
                'data_type' => 'time',
                'description' => 'Waktu mulai check-in WFH.',
                'is_public' => false,
            ],
            [
                'setting_key' => 'checkin_on_time_end',
                'setting_value' => '07:10',
                'data_type' => 'time',
                'description' => 'Batas check-in tepat waktu.',
                'is_public' => false,
            ],
            [
                'setting_key' => 'checkin_deadline',
                'setting_value' => '08:00',
                'data_type' => 'time',
                'description' => 'Batas akhir check-in.',
                'is_public' => false,
            ],
            [
                'setting_key' => 'checkout_start_time',
                'setting_value' => '15:00',
                'data_type' => 'time',
                'description' => 'Waktu mulai check-out.',
                'is_public' => false,
            ],
            [
                'setting_key' => 'checkout_on_time_end',
                'setting_value' => '15:30',
                'data_type' => 'time',
                'description' => 'Batas check-out tepat waktu.',
                'is_public' => false,
            ],
            [
                'setting_key' => 'checkout_deadline',
                'setting_value' => '16:00',
                'data_type' => 'time',
                'description' => 'Batas akhir check-out.',
                'is_public' => false,
            ],
            [
                'setting_key' => 'file_retention_days',
                'setting_value' => '30',
                'data_type' => 'integer',
                'description' => 'Masa penyimpanan foto dan file PDF.',
                'is_public' => false,
            ],
            [
                'setting_key' => 'admin_contact_name',
                'setting_value' => 'Admin SIKERJA',
                'data_type' => 'string',
                'description' => 'Nama kontak Admin pada halaman login.',
                'is_public' => true,
            ],
            [
                'setting_key' => 'admin_contact_phone',
                'setting_value' => '08xxxxxxxxxx',
                'data_type' => 'string',
                'description' => 'Nomor kontak Admin pada halaman login.',
                'is_public' => true,
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('system_settings')->updateOrInsert(
                ['setting_key' => $setting['setting_key']],
                [
                    'setting_value' => $setting['setting_value'],
                    'data_type' => $setting['data_type'],
                    'description' => $setting['description'],
                    'is_public' => $setting['is_public'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
