<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Berita;
use App\Models\Masjid;
use App\Models\PendaftaranNikah;
use App\Models\Antrian;
use Illuminate\Http\Request;

class DashboardController extends BaseController
{
    /**
     * Get dashboard statistics
     */
    public function index(Request $request)
    {
        $stats = [
            'total_masjid' => Masjid::active()->count(),
            'total_musholla' => Masjid::active()->ofType('musholla')->count(),
            'total_berita' => Berita::published()->count(),
            'pendaftaran_pending' => PendaftaranNikah::pending()->count(),
            'antrian_hari_ini' => Antrian::today()->waiting()->count(),
        ];

        // This month stats
        $thisMonth = [
            'berita_baru' => Berita::whereMonth('created_at', now()->month)->count(),
            'pendaftaran_baru' => PendaftaranNikah::whereMonth('created_at', now()->month)->count(),
            'nikah_selesai' => PendaftaranNikah::whereMonth('created_at', now()->month)
                ->where('status', 'selesai')->count(),
        ];

        // Recent activities
        $recentPendaftaran = PendaftaranNikah::latest()
            ->take(5)
            ->get(['id', 'kode_pendaftaran', 'nama_pria', 'nama_wanita', 'status', 'created_at']);

        $recentBerita = Berita::latest()
            ->take(5)
            ->get(['id', 'judul', 'status', 'created_at']);

        return $this->successResponse([
            'stats' => $stats,
            'this_month' => $thisMonth,
            'recent_pendaftaran' => $recentPendaftaran,
            'recent_berita' => $recentBerita,
        ], 'Dashboard statistics');
    }
}
