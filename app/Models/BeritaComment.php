<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BeritaComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'berita_id',
        'nama',
        'email',
        'komentar',
        'is_approved',
        'is_spam',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'is_spam' => 'boolean',
    ];

    /**
     * Get the berita
     */
    public function berita(): BelongsTo
    {
        return $this->belongsTo(Berita::class);
    }

    /**
     * Scope: approved only
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true)->where('is_spam', false);
    }

    /**
     * Scope: pending moderation
     */
    public function scopePendingModeration($query)
    {
        return $query->where('is_approved', false)->where('is_spam', false);
    }
}
