<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Masjid extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'alamat',
        'kelurahan',
        'kecamatan',
        'no_telp',
        'latitude',
        'longitude',
        'tipe',
        'tahun_berdiri',
        'kapasitas',
        'foto',
        'keterangan',
        'jadwal_imam',
        'jadwal_imam_file',
        'jadwal_khotib',
        'jadwal_khotib_file',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
    ];

    /**
     * Imam yang bertugas di masjid ini
     */
    public function imams(): BelongsToMany
    {
        return $this->belongsToMany(Imam::class, 'masjid_imam')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /**
     * Khotib yang bertugas di masjid ini
     */
    public function khotibs(): BelongsToMany
    {
        return $this->belongsToMany(Khotib::class, 'masjid_khotib')
            ->withTimestamps();
    }

    /**
     * Imam tetap masjid
     */
    public function primaryImam()
    {
        return $this->imams()->wherePivot('is_primary', true)->first();
    }

    /**
     * Scope: hanya yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: berdasarkan tipe
     */
    public function scopeOfType($query, string $tipe)
    {
        return $query->where('tipe', $tipe);
    }
}
