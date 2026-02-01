<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Berita extends Model
{
    use HasFactory;

    protected $fillable = [
        'judul',
        'slug',
        'ringkasan',
        'isi',
        'gambar',
        'kategori',
        'status',
        'published_at',
        'view_count',
        'is_featured',
        'author_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
    ];

    /**
     * Boot method untuk auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($berita) {
            if (empty($berita->slug)) {
                $berita->slug = Str::slug($berita->judul);
            }
        });
    }

    /**
     * Author berita
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Comments for this berita
     */
    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BeritaComment::class);
    }

    /**
     * Likes for this berita
     */
    public function likes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BeritaLike::class);
    }

    /**
     * Get like count attribute
     */
    public function getLikeCountAttribute(): int
    {
        return $this->likes()->count();
    }

    /**
     * Get comment count attribute
     */
    public function getCommentCountAttribute(): int
    {
        return $this->comments()->approved()->count();
    }

    /**
     * Increment views
     */
    public function incrementViews(): void
    {
        $this->increment('view_count');
    }

    /**
     * Scope: published
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope: featured
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: by kategori
     */
    public function scopeOfKategori($query, string $kategori)
    {
        return $query->where('kategori', $kategori);
    }
}
