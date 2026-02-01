<?php

namespace App\Http\Controllers\Api;

use App\Models\Berita;
use App\Models\BeritaLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BeritaController extends BaseController
{
    /**
     * Get published berita list (public)
     */
    public function index(Request $request)
    {
        $query = Berita::published()
            ->with('author:id,name')
            ->select(['id', 'judul', 'slug', 'ringkasan', 'gambar', 'kategori', 'published_at', 'views', 'author_id'])
            ->latest('published_at');

        // Filter by kategori
        if ($request->has('kategori')) {
            $query->ofKategori($request->kategori);
        }

        // Featured only
        if ($request->boolean('featured')) {
            $query->featured();
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('ringkasan', 'like', "%{$search}%");
            });
        }

        $beritas = $query->paginate($request->per_page ?? 10);

        return $this->paginatedResponse($beritas, 'Daftar berita');
    }

    /**
     * Get berita detail by slug (public)
     */
    public function show(Request $request, string $slug)
    {
        $berita = Berita::published()
            ->with('author:id,name')
            ->where('slug', $slug)
            ->firstOrFail();

        // Rate-limited view increment (1 per IP per hour)
        $viewKey = 'berita_view_' . $berita->id . '_' . md5($request->ip());
        if (!Cache::has($viewKey)) {
            $berita->incrementViews();
            Cache::put($viewKey, true, 3600); // 1 hour
        }

        // Check if current IP has liked
        $hasLiked = BeritaLike::where('berita_id', $berita->id)
            ->where('ip_address', $request->ip())
            ->exists();

        return $this->successResponse([
            'id' => $berita->id,
            'judul' => $berita->judul,
            'slug' => $berita->slug,
            'ringkasan' => $berita->ringkasan,
            'isi' => $berita->isi,
            'gambar' => $berita->gambar,
            'kategori' => $berita->kategori,
            'published_at' => $berita->published_at,
            'views' => $berita->views,
            'author' => $berita->author?->name,
            'like_count' => $berita->like_count,
            'comment_count' => $berita->comment_count,
            'has_liked' => $hasLiked,
        ], 'Detail berita');
    }

    /**
     * Toggle like for a berita
     */
    public function toggleLike(Request $request, string $slug)
    {
        $berita = Berita::published()
            ->where('slug', $slug)
            ->firstOrFail();

        $ipAddress = $request->ip();
        $fingerprint = $request->input('fingerprint');

        $existingLike = BeritaLike::where('berita_id', $berita->id)
            ->where('ip_address', $ipAddress)
            ->first();

        if ($existingLike) {
            // Unlike
            $existingLike->delete();
            return $this->successResponse([
                'liked' => false,
                'like_count' => $berita->fresh()->like_count,
            ], 'Like dihapus');
        }

        // Like
        BeritaLike::create([
            'berita_id' => $berita->id,
            'ip_address' => $ipAddress,
            'fingerprint' => $fingerprint,
        ]);

        return $this->successResponse([
            'liked' => true,
            'like_count' => $berita->fresh()->like_count,
        ], 'Berita disukai');
    }

    /**
     * Get featured berita for homepage
     */
    public function featured()
    {
        $beritas = Berita::published()
            ->featured()
            ->with('author:id,name')
            ->select(['id', 'judul', 'slug', 'ringkasan', 'gambar', 'published_at', 'author_id'])
            ->latest('published_at')
            ->take(5)
            ->get();

        return $this->successResponse($beritas, 'Berita unggulan');
    }

    /**
     * Get latest berita
     */
    public function latest()
    {
        $beritas = Berita::published()
            ->with('author:id,name')
            ->select(['id', 'judul', 'slug', 'ringkasan', 'gambar', 'kategori', 'published_at', 'author_id'])
            ->latest('published_at')
            ->take(6)
            ->get();

        return $this->successResponse($beritas, 'Berita terbaru');
    }
}
