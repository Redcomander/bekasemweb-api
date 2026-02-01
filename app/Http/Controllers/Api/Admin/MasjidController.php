<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Masjid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class MasjidController extends BaseController
{
    /**
     * Display a listing of masjid
     */
    public function index(Request $request)
    {
        $query = Masjid::query();

        // Filter by tipe
        if ($request->has('tipe')) {
            $query->ofType($request->tipe);
        }

        // Filter active only
        if ($request->boolean('active_only', true)) {
            $query->active();
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%")
                    ->orWhere('kelurahan', 'like', "%{$search}%");
            });
        }

        $masjids = $query->orderBy('nama')->paginate($request->per_page ?? 50);

        return $this->paginatedResponse($masjids, 'Daftar masjid');
    }

    /**
     * Store a newly created masjid
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string',
            'kelurahan' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'no_telp' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'tipe' => 'in:masjid,musholla',
            'tahun_berdiri' => 'nullable|integer|min:1900|max:' . date('Y'),
            'kapasitas' => 'nullable|integer|min:0',
            'keterangan' => 'nullable|string',
            'jadwal_imam' => 'nullable|string',
            'jadwal_khotib' => 'nullable|string',
        ]);

        $masjid = Masjid::create($validated);

        // Handle file uploads
        $this->handleFileUpload($request, $masjid, 'jadwal_imam_file');
        $this->handleFileUpload($request, $masjid, 'jadwal_khotib_file');

        return $this->createdResponse($masjid->fresh(), 'Masjid berhasil ditambahkan');
    }

    /**
     * Display the specified masjid
     */
    public function show(Masjid $masjid)
    {
        return $this->successResponse($masjid, 'Detail masjid');
    }

    /**
     * Update the specified masjid
     */
    public function update(Request $request, Masjid $masjid)
    {
        $validated = $request->validate([
            'nama' => 'sometimes|required|string|max:255',
            'alamat' => 'sometimes|required|string',
            'kelurahan' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'no_telp' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'tipe' => 'in:masjid,musholla',
            'tahun_berdiri' => 'nullable|integer|min:1900|max:' . date('Y'),
            'kapasitas' => 'nullable|integer|min:0',
            'keterangan' => 'nullable|string',
            'jadwal_imam' => 'nullable|string',
            'jadwal_khotib' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $masjid->update($validated);

        // Handle file uploads
        $this->handleFileUpload($request, $masjid, 'jadwal_imam_file');
        $this->handleFileUpload($request, $masjid, 'jadwal_khotib_file');

        // Handle file deletions
        if ($request->boolean('delete_jadwal_imam_file')) {
            $this->deleteFile($masjid, 'jadwal_imam_file');
        }
        if ($request->boolean('delete_jadwal_khotib_file')) {
            $this->deleteFile($masjid, 'jadwal_khotib_file');
        }

        return $this->successResponse($masjid->fresh(), 'Masjid berhasil diupdate');
    }

    /**
     * Remove the specified masjid
     */
    public function destroy(Masjid $masjid)
    {
        // Delete associated files
        $this->deleteFile($masjid, 'jadwal_imam_file');
        $this->deleteFile($masjid, 'jadwal_khotib_file');

        $masjid->delete();
        return $this->successResponse(null, 'Masjid berhasil dihapus');
    }

    /**
     * Handle file upload with compression for images
     */
    private function handleFileUpload(Request $request, Masjid $masjid, string $field): void
    {
        if (!$request->hasFile($field)) {
            return;
        }

        $file = $request->file($field);
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = 'masjid_' . $masjid->id . '_' . $field . '_' . time() . '.' . $extension;
        $path = 'masjid/' . $filename;

        // Delete old file if exists
        if ($masjid->{$field}) {
            Storage::disk('public')->delete($masjid->{$field});
        }

        // Check if it's an image that can be compressed
        $imageExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($extension, $imageExtensions)) {
            // Compress image using Intervention Image
            $image = Image::make($file);

            // Resize if too large (max 1920px width)
            if ($image->width() > 1920) {
                $image->resize(1920, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            // Save with compression (quality 80%)
            $image->save(storage_path('app/public/' . $path), 80);
        } else {
            // For PDFs and other files, store directly
            $file->storeAs('masjid', $filename, 'public');
        }

        $masjid->update([$field => $path]);
    }

    /**
     * Delete a file from storage
     */
    private function deleteFile(Masjid $masjid, string $field): void
    {
        if ($masjid->{$field}) {
            Storage::disk('public')->delete($masjid->{$field});
            $masjid->update([$field => null]);
        }
    }
}
