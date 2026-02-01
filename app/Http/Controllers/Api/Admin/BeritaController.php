<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Berita;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BeritaController extends BaseController
{
    /**
     * Display a listing of berita
     * 
     * GET /api/admin/berita
     */
    public function index(Request $request)
    {
        $query = Berita::with('author')
            ->latest();

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by kategori
        if ($request->has('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('isi', 'like', "%{$search}%");
            });
        }

        $beritas = $query->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($beritas, 'Daftar berita');
    }

    /**
     * Store a newly created berita
     * 
     * POST /api/admin/berita
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'ringkasan' => 'nullable|string',
            'isi' => 'required|string',
            'gambar' => 'nullable', // Can be file upload or string path
            'kategori' => 'nullable|string|max:50',
            'status' => 'in:draft,published,archived',
            'is_featured' => 'boolean',
        ]);

        // Handle image - can be file upload or string path from gallery
        if ($request->hasFile('gambar')) {
            // Use ImageService for WebP compression
            $imageService = new ImageService();
            $validated['gambar'] = $imageService->processAndStore($request->file('gambar'), 'berita');
        } elseif (is_string($request->gambar) && !empty($request->gambar)) {
            // Extract path from full URL if needed
            $imageService = new ImageService();
            $validated['gambar'] = $imageService->getPathFromUrl($request->gambar);
        }


        // Generate slug
        $validated['slug'] = Str::slug($validated['judul']);
        $validated['author_id'] = $request->user()->id;

        // Set published_at if publishing
        if (($validated['status'] ?? 'draft') === 'published') {
            $validated['published_at'] = now();
        }

        // Debug logging
        \Log::info('Creating berita with validated data:', $validated);

        $berita = Berita::create($validated);

        \Log::info('Created berita:', $berita->toArray());

        return $this->createdResponse($berita, 'Berita berhasil dibuat');
    }

    /**
     * Display the specified berita
     * 
     * GET /api/admin/berita/{id}
     */
    public function show(Berita $berita)
    {
        $berita->load('author');
        return $this->successResponse($berita, 'Detail berita');
    }

    /**
     * Update the specified berita
     * 
     * PUT /api/admin/berita/{id}
     */
    public function update(Request $request, Berita $berita)
    {
        // Check if route model binding worked
        \Log::info('Update method called', [
            'berita_is_null' => is_null($berita),
            'berita_exists' => !is_null($berita) && $berita->exists,
            'berita_id' => $berita?->id,
        ]);

        // If berita doesn't have an ID, route model binding failed
        if (!$berita || !$berita->id) {
            \Log::error('Route model binding failed - berita has no ID');
            return $this->errorResponse('Invalid berita ID', 404);
        }

        $validated = $request->validate([
            'judul' => 'sometimes|required|string|max:255',
            'ringkasan' => 'nullable|string',
            'isi' => 'sometimes|required|string',
            'gambar' => 'nullable', // Can be file upload or string path
            'kategori' => 'nullable|string|max:50',
            'status' => 'in:draft,published,archived',
            'is_featured' => 'boolean',
        ]);

        // Handle image - can be file upload or string path from gallery
        if ($request->hasFile('gambar')) {
            // Use ImageService for WebP compression
            $imageService = new ImageService();
            $validated['gambar'] = $imageService->processAndStore($request->file('gambar'), 'berita');
        } elseif (is_string($request->gambar) && !empty($request->gambar)) {
            // Extract path from full URL if needed
            $imageService = new ImageService();
            $validated['gambar'] = $imageService->getPathFromUrl($request->gambar);
        }

        // Update slug if judul changed
        if (isset($validated['judul'])) {
            $validated['slug'] = Str::slug($validated['judul']);
        }

        // Set published_at if publishing for first time
        if (($validated['status'] ?? null) === 'published' && !$berita->published_at) {
            $validated['published_at'] = now();
        }

        \Log::info('Updating berita', [
            'id' => $berita->id,
            'validated' => $validated,
        ]);

        $berita->update($validated);

        // Reload from database
        $updated = Berita::find($berita->id);
        
        if (!$updated) {
            \Log::error('Failed to find berita after update', ['id' => $berita->id]);
            return $this->errorResponse('Failed to reload berita after update', 500);
        }

        return $this->successResponse($updated, 'Berita berhasil diupdate');
    }

    /**
     * Remove the specified berita
     * 
     * DELETE /api/admin/berita/{id}
     */
    public function destroy(Berita $berita)
    {
        $berita->delete();
        return $this->successResponse(null, 'Berita berhasil dihapus');
    }
}
