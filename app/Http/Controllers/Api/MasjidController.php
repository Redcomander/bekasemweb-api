<?php

namespace App\Http\Controllers\Api;

use App\Models\Masjid;
use Illuminate\Http\Request;

class MasjidController extends BaseController
{
    /**
     * Get masjid list (public)
     */
    public function index(Request $request)
    {
        $query = Masjid::active()
            ->select(['id', 'nama', 'alamat', 'kelurahan', 'tipe', 'latitude', 'longitude', 'kapasitas', 'foto']);

        // Filter by tipe
        if ($request->has('tipe')) {
            $query->ofType($request->tipe);
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

        $masjids = $query->orderBy('nama')->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($masjids, 'Daftar masjid');
    }

    /**
     * Get masjid detail (public)
     */
    public function show(Masjid $masjid)
    {
        if (!$masjid->is_active) {
            return $this->notFoundResponse('Masjid tidak ditemukan');
        }

        $masjid->load(['imams' => function ($q) {
            $q->active()->select(['imams.id', 'nama', 'foto']);
        }, 'khotibs' => function ($q) {
            $q->active()->select(['khotibs.id', 'nama', 'foto']);
        }]);

        return $this->successResponse($masjid, 'Detail masjid');
    }

    /**
     * Get masjid statistics
     */
    public function stats()
    {
        $stats = [
            'total_masjid' => Masjid::active()->ofType('masjid')->count(),
            'total_musholla' => Masjid::active()->ofType('musholla')->count(),
        ];

        return $this->successResponse($stats, 'Statistik masjid');
    }
}
