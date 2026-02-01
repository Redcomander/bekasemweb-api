<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Galeri extends Model
{
    use HasFactory;

    protected $table = 'galeries';

    protected $fillable = [
        'judul',
        'tipe',
        'file_path',
        'thumbnail',
        'keterangan',
        'kategori',
        'tanggal_kegiatan',
        'is_featured',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'tanggal_kegiatan' => 'date',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * User yang mengupload
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: hanya yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: hanya featured
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: berdasarkan tipe
     */
    public function scopeOfType($query, string $tipe)
    {
        return $query->where('tipe', $tipe);
    }
}
