<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MajelisTaklim extends Model
{
    use HasFactory;

    protected $table = 'majelis_taklims';

    protected $fillable = [
        'judul',
        'penceramah',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'tempat',
        'deskripsi',
        'youtube_url',
        'status',
        'poster',
        'is_active',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_mulai' => 'datetime:H:i',
        'jam_selesai' => 'datetime:H:i',
        'is_active' => 'boolean',
    ];

    /**
     * Scope: hanya yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: yang akan datang
     */
    public function scopeUpcoming($query)
    {
        return $query->where('tanggal', '>=', now()->toDateString())
            ->where('status', 'upcoming')
            ->orderBy('tanggal');
    }

    /**
     * Scope: yang sedang live
     */
    public function scopeLive($query)
    {
        return $query->where('status', 'live');
    }

    /**
     * Scope: arsip
     */
    public function scopeArsip($query)
    {
        return $query->where('status', 'arsip')
            ->orderByDesc('tanggal');
    }
}
