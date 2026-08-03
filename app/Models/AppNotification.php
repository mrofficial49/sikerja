<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppNotification extends Model
{
    use HasFactory;

    /**
     * Nama tabel ditulis karena nama model AppNotification
     * tidak mengikuti nama tabel standar Laravel.
     */
    protected $table = 'notifications';

    /**
     * Kolom yang boleh diisi melalui create() atau update().
     */
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'related_type',
        'related_id',
        'is_read',
        'read_at',
    ];

    /**
     * Mengubah nilai database menjadi tipe data PHP.
     */
    protected function casts(): array
    {
        return [
            'related_id' => 'integer',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    /**
     * Pengguna yang menerima notifikasi.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Menandai notifikasi sebagai sudah dibaca.
     */
    public function markAsRead(): void
    {
        if ($this->is_read) {
            return;
        }

        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Memeriksa apakah notifikasi belum dibaca.
     */
    public function isUnread(): bool
    {
        return ! $this->is_read;
    }

    /**
     * Membatasi query hanya untuk notifikasi belum dibaca.
     *
     * Contoh:
     * AppNotification::unread()->get();
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
}
