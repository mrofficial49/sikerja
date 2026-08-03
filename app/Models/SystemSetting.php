<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi melalui create() atau update().
     */
    protected $fillable = [
        'setting_key',
        'setting_value',
        'data_type',
        'description',
        'is_public',
    ];

    /**
     * Mengubah nilai database menjadi tipe data PHP.
     */
    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    /**
     * Membatasi query hanya untuk pengaturan publik.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Mengubah setting_value sesuai data_type.
     *
     * Contoh:
     * integer akan menjadi angka,
     * boolean akan menjadi true/false,
     * JSON akan menjadi array.
     */
    public function typedValue(): mixed
    {
        if ($this->setting_value === null) {
            return null;
        }

        return match ($this->data_type) {
            'integer' => (int) $this->setting_value,

            'boolean' => filter_var(
                $this->setting_value,
                FILTER_VALIDATE_BOOLEAN
            ),

            'json' => json_decode(
                $this->setting_value,
                true
            ),

            // string, text, time, dan date tetap berupa teks.
            default => $this->setting_value,
        };
    }

    /**
     * Mengambil nilai pengaturan berdasarkan setting_key.
     *
     * Contoh:
     * SystemSetting::getValue('app_name', 'SIKERJA');
     */
    public static function getValue(
        string $key,
        mixed $default = null
    ): mixed {
        $setting = static::query()
            ->where('setting_key', $key)
            ->first();

        return $setting?->typedValue() ?? $default;
    }
}
