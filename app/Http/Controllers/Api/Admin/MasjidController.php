<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Masjid;
use Illuminate\Http\Request;

class MasjidController extends BaseController
{
    /**
     * Display a listing of masjid
     */
    public function index(Request $request)
    {
        $query = Masjid::with(['imams', 'khotibs']);

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

        $masjids = $query->paginate($request->per_page ?? 15);

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
            'imam_ids' => 'nullable|array',
            'imam_ids.*' => 'exists:imams,id',
            'khotib_ids' => 'nullable|array',
            'khotib_ids.*' => 'exists:khotibs,id',
        ]);

        $masjid = Masjid::create($validated);

        // Sync imams
        if ($request->has('imam_ids')) {
            $masjid->imams()->sync($request->imam_ids);
        }

        // Sync khotibs
        if ($request->has('khotib_ids')) {
            $masjid->khotibs()->sync($request->khotib_ids);
        }

        return $this->createdResponse(
            $masjid->load(['imams', 'khotibs']),
            'Masjid berhasil ditambahkan'
        );
    }

    /**
     * Display the specified masjid
     */
    public function show(Masjid $masjid)
    {
        $masjid->load(['imams', 'khotibs']);
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
            'is_active' => 'boolean',
            'imam_ids' => 'nullable|array',
            'imam_ids.*' => 'exists:imams,id',
            'khotib_ids' => 'nullable|array',
            'khotib_ids.*' => 'exists:khotibs,id',
        ]);

        $masjid->update($validated);

        // Sync imams if provided
        if ($request->has('imam_ids')) {
            $masjid->imams()->sync($request->imam_ids);
        }

        // Sync khotibs if provided
        if ($request->has('khotib_ids')) {
            $masjid->khotibs()->sync($request->khotib_ids);
        }

        return $this->successResponse(
            $masjid->fresh()->load(['imams', 'khotibs']),
            'Masjid berhasil diupdate'
        );
    }

    /**
     * Remove the specified masjid
     */
    public function destroy(Masjid $masjid)
    {
        $masjid->delete();
        return $this->successResponse(null, 'Masjid berhasil dihapus');
    }
}
