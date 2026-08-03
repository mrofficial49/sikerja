<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkItemFile extends Model
{
    /**
     * Nama tabel database.
     */
    protected $table = 'work_item_files';

    /**
     * Kolom yang boleh diisi.
     */
    protected $fillable = [
        'item_id',
        'original_name',
        'stored_name',
        'file_path',
        'description',
        'file_size',
        'mime_type',
        'uploaded_by',
        'uploaded_at',
        'expires_at',
        'deleted_at',
        'is_available',
    ];

    /**
     * Konversi tipe data kolom.
     */
    protected function casts(): array
    {
        return [
            'item_id' => 'integer',
            'uploaded_by' => 'integer',
            'file_size' => 'integer',
            'uploaded_at' => 'datetime',
            'expires_at' => 'datetime',
            'deleted_at' => 'datetime',
            'is_available' => 'boolean',
        ];
    }

    /**
     * File bukti dimiliki oleh satu pekerjaan.
     *
     * Parameter:
     * 1. Model tujuan.
     * 2. Foreign key pada work_item_files.
     * 3. Primary key pada work_items.
     */
    public function workItem(): BelongsTo
    {
        return $this->belongsTo(
            WorkItem::class,
            'item_id',
            'id'
        );
    }

    /**
     * Pengguna yang mengunggah file.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by',
            'id'
        );
    }
}