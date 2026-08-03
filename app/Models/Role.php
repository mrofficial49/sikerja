<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi melalui create() atau update().
     */
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    /**
     * Mengubah nilai database menjadi tipe data PHP.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Satu role dapat dimiliki oleh banyak pengguna.
     *
     * Contoh:
     * Role Personel dapat digunakan oleh banyak akun personel.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
