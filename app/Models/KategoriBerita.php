<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KategoriBerita extends Model
{
    use HasFactory;

    protected $table = 'kategori_beritas';

    protected $fillable = [
        'nama',
        'slug',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Boot method untuk auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($kategori) {
            if (empty($kategori->slug)) {
                $kategori->slug = Str::slug($kategori->nama);
            }
        });
    }

    /**
     * Scope: active only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
