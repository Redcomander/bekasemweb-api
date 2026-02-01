<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Galeri;
use App\Services\ImageService;
use Illuminate\Http\Request;

class GaleriController extends BaseController
{
    /**
     * Display a listing of galeri
     */
    public function index(Request $request)
    {
        $query = Galeri::with('creator');

        // Filter by tipe
        if ($request->has('tipe')) {
            $query->ofType($request->tipe);
        }

        // Filter by kategori
        if ($request->has('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Filter featured
        if ($request->boolean('featured')) {
            $query->featured();
        }

        // Filter active only
        if ($request->boolean('active_only', true)) {
            $query->active();
        }

        $galeris = $query->latest()->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($galeris, 'Daftar galeri');
    }

    /**
     * Store a newly created galeri
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'tipe' => 'required|in:foto,video',
            'file' => 'required_if:tipe,foto|image|max:5120',
            'video_url' => 'required_if:tipe,video|nullable|url',
            'keterangan' => 'nullable|string',
            'kategori' => 'nullable|string|max:50',
            'tanggal_kegiatan' => 'nullable|date',
            'is_featured' => 'boolean',
        ]);

        // Handle file upload for foto - use ImageService for WebP compression
        if ($request->hasFile('file')) {
            $imageService = new ImageService();
            $validated['file_path'] = $imageService->processAndStore($request->file('file'), 'galeri');
        } elseif ($request->tipe === 'video') {
            $validated['file_path'] = $request->video_url;
        }

        $validated['created_by'] = $request->user()->id;
        unset($validated['file'], $validated['video_url']);

        $galeri = Galeri::create($validated);

        return $this->createdResponse($galeri, 'Galeri berhasil ditambahkan');
    }

    /**
     * Display the specified galeri
     */
    public function show(Galeri $galeri)
    {
        $galeri->load('creator');
        return $this->successResponse($galeri, 'Detail galeri');
    }

    /**
     * Update the specified galeri
     */
    public function update(Request $request, Galeri $galeri)
    {
        $validated = $request->validate([
            'judul' => 'sometimes|required|string|max:255',
            'tipe' => 'in:foto,video',
            'file' => 'nullable|image|max:5120',
            'video_url' => 'nullable|url',
            'keterangan' => 'nullable|string',
            'kategori' => 'nullable|string|max:50',
            'tanggal_kegiatan' => 'nullable|date',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Handle file upload
        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('galeri', 'public');
        } elseif ($request->has('video_url')) {
            $validated['file_path'] = $request->video_url;
        }

        unset($validated['file'], $validated['video_url']);

        $galeri->update($validated);

        return $this->successResponse($galeri->fresh(), 'Galeri berhasil diupdate');
    }

    /**
     * Remove the specified galeri
     */
    public function destroy(Galeri $galeri)
    {
        $galeri->delete();
        return $this->successResponse(null, 'Galeri berhasil dihapus');
    }
}
