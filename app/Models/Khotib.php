<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Khotib extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'no_hp',
        'alamat',
        'foto',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Masjid tempat khotib bertugas
     */
    public function masjids(): BelongsToMany
    {
        return $this->belongsToMany(Masjid::class, 'masjid_khotib')
            ->withTimestamps();
    }

    /**
     * Scope: hanya yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
