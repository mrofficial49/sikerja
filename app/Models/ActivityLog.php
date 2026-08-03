<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    /**
     * Tabel activity_logs tidak memiliki updated_at
     * karena catatan aktivitas tidak boleh diedit.
     */
    public const UPDATED_AT = null;

    /**
     * Kolom yang boleh diisi saat membuat activity log.
     */
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'ip_address',
        'user_agent',
    ];

    /**
     * Mengubah nilai database menjadi tipe data PHP.
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'subject_id' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Pengguna yang melakukan aktivitas.
     *
     * Relasi boleh kosong jika aktivitas dilakukan
     * otomatis oleh sistem.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Membatasi query berdasarkan jenis tindakan.
     *
     * Contoh:
     * ActivityLog::action('login_success')->get();
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Membatasi query berdasarkan pengguna.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
