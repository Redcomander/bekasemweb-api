<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BeritaLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'berita_id',
        'ip_address',
        'fingerprint',
    ];

    /**
     * Get the berita
     */
    public function berita(): BelongsTo
    {
        return $this->belongsTo(Berita::class);
    }
}
