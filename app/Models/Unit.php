<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi melalui create() atau update().
     */
    protected $fillable = [
        'code',
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
     * Satu unit dapat mempunyai banyak pengguna.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
