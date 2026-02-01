<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LokasiController;
use App\Http\Controllers\Api\SocialController;
use App\Http\Controllers\Api\BeritaController;
use App\Http\Controllers\Api\MasjidController;
use App\Http\Controllers\Api\GaleriController;
use App\Http\Controllers\Api\MajelisTaklimController;
use App\Http\Controllers\Api\JadwalNikahController;
use App\Http\Controllers\Api\PendaftaranNikahController;
use App\Http\Controllers\Api\AntrianController;
use App\Http\Controllers\Api\Admin\BeritaController as AdminBeritaController;
use App\Http\Controllers\Api\Admin\MasjidController as AdminMasjidController;
use App\Http\Controllers\Api\Admin\GaleriController as AdminGaleriController;
use App\Http\Controllers\Api\Admin\NotifikasiController;
use App\Http\Controllers\Api\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - BEKASEMWEB
|--------------------------------------------------------------------------
|
| Semua route API untuk website KUA Sembawa.
| Prefix: /api
|
*/

// =============================================
// PUBLIC ROUTES (Tanpa Autentikasi)
// =============================================

// Lokasi & Informasi KUA
Route::get('/lokasi', [LokasiController::class, 'index']);
Route::get('/social', [SocialController::class, 'index']);

// Berita (Public - Read Only)
Route::prefix('berita')->group(function () {
    Route::get('/', [BeritaController::class, 'index']);
    Route::get('/featured', [BeritaController::class, 'featured']);
    Route::get('/latest', [BeritaController::class, 'latest']);
    Route::get('/{slug}', [BeritaController::class, 'show']);
    Route::post('/{slug}/like', [BeritaController::class, 'toggleLike']);

    // Comments
    Route::get('/{berita}/comments', [\App\Http\Controllers\Api\BeritaCommentController::class, 'index'])->whereNumber('berita');
    Route::post('/{berita}/comments', [\App\Http\Controllers\Api\BeritaCommentController::class, 'store'])->whereNumber('berita');
});

// Masjid (Public - Read Only)
Route::prefix('masjid')->group(function () {
    Route::get('/', [MasjidController::class, 'index']);
    Route::get('/stats', [MasjidController::class, 'stats']);
    Route::get('/{masjid}', [MasjidController::class, 'show']);
});

// Galeri (Public - Read Only)
Route::prefix('galeri')->group(function () {
    Route::get('/', [GaleriController::class, 'index']);
    Route::get('/featured', [GaleriController::class, 'featured']);
    Route::get('/{galeri}', [GaleriController::class, 'show']);
});

// Majelis Taklim (Public - Read Only)
Route::prefix('majelis-taklim')->group(function () {
    Route::get('/', [MajelisTaklimController::class, 'index']);
    Route::get('/upcoming', [MajelisTaklimController::class, 'upcoming']);
    Route::get('/arsip', [MajelisTaklimController::class, 'arsip']);
    Route::get('/{majelisTaklim}', [MajelisTaklimController::class, 'show']);
});

// Jadwal Nikah (Public - Read Only)
Route::prefix('jadwal-nikah')->group(function () {
    Route::get('/', [JadwalNikahController::class, 'index']);
    Route::get('/available', [JadwalNikahController::class, 'available']);
    Route::get('/calendar', [JadwalNikahController::class, 'calendar']);
});

// Pendaftaran Nikah (Public - Submit & Track)
Route::prefix('pendaftaran-nikah')->group(function () {
    Route::post('/', [PendaftaranNikahController::class, 'store']);
    Route::get('/status/{kode}', [PendaftaranNikahController::class, 'status']);
    Route::post('/upload/{kode}', [PendaftaranNikahController::class, 'uploadDokumen']);
});

// Antrian (Public - Take & Track)
Route::prefix('antrian')->group(function () {
    Route::post('/ambil', [AntrianController::class, 'ambil']);
    Route::get('/status/{kode}', [AntrianController::class, 'status']);
    Route::get('/display', [AntrianController::class, 'display']);
});

// =============================================
// AUTH ROUTES
// =============================================
Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// =============================================
// ADMIN ROUTES (Butuh Autentikasi)
// =============================================
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:view_dashboard');

    // Notifikasi
    Route::get('/notifikasi', [NotifikasiController::class, 'index']);
    Route::get('/notifikasi/unread-count', [NotifikasiController::class, 'unreadCount']);
    Route::put('/notifikasi/{notifikasi}/read', [NotifikasiController::class, 'markRead']);
    Route::put('/notifikasi/read-all', [NotifikasiController::class, 'markAllRead']);

    // CRUD Berita
    Route::apiResource('berita', AdminBeritaController::class)
        ->parameters(['berita' => 'berita']); // Force singular 'berita' instead of 'beritum'

    // Berita Metrics & Analytics
    Route::prefix('berita-metrics')->group(function () {
        Route::get('/overview', [\App\Http\Controllers\Api\Admin\BeritaMetricsController::class, 'overview']);
        Route::get('/top-articles', [\App\Http\Controllers\Api\Admin\BeritaMetricsController::class, 'topArticles']);
        Route::get('/trends', [\App\Http\Controllers\Api\Admin\BeritaMetricsController::class, 'trends']);
        Route::get('/comments', [\App\Http\Controllers\Api\Admin\BeritaMetricsController::class, 'comments']);
        Route::put('/comments/{id}/approve', [\App\Http\Controllers\Api\Admin\BeritaMetricsController::class, 'approveComment']);
        Route::put('/comments/{id}/spam', [\App\Http\Controllers\Api\Admin\BeritaMetricsController::class, 'markSpam']);
        Route::delete('/comments/{id}', [\App\Http\Controllers\Api\Admin\BeritaMetricsController::class, 'deleteComment']);
    });

    // CRUD Kategori Berita
    Route::apiResource('kategori-berita', \App\Http\Controllers\Api\Admin\KategoriBeritaController::class)
        ->parameters(['kategori-berita' => 'kategoriBerita']);

    // CRUD Masjid
    Route::apiResource('masjid', AdminMasjidController::class)
        ->middleware('permission:manage_masjid');

    // CRUD Galeri
    Route::apiResource('galeri', AdminGaleriController::class)
        ->middleware('permission:manage_galeri');

    // TODO: More admin resources
    // Route::apiResource('imam', ImamController::class)->middleware('permission:manage_imam');
    // Route::apiResource('khotib', KhotibController::class)->middleware('permission:manage_khotib');
    // Route::apiResource('majelis-taklim', MajelisTaklimController::class)->middleware('permission:manage_majelis');
    // Route::apiResource('pendaftaran', PendaftaranController::class)->middleware('permission:manage_pendaftaran');
    // Route::apiResource('jadwal', JadwalController::class)->middleware('permission:manage_jadwal');
    // Route::apiResource('antrian', AntrianController::class)->middleware('permission:manage_antrian');
    // Route::apiResource('roles', RoleController::class)->middleware('permission:manage_roles');

    // CRUD Imam
    Route::apiResource('imam', \App\Http\Controllers\Api\Admin\ImamController::class);

    // CRUD Khotib
    Route::apiResource('khotib', \App\Http\Controllers\Api\Admin\KhotibController::class);

    // CRUD Users
    Route::apiResource('users', \App\Http\Controllers\Api\Admin\UserController::class);
    Route::get('users-roles', [\App\Http\Controllers\Api\Admin\UserController::class, 'roles']);

    // Pendaftaran Nikah Management
    Route::prefix('pendaftaran-nikah')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\Admin\PendaftaranNikahController::class, 'index']);
        Route::get('/penghulus', [\App\Http\Controllers\Api\Admin\PendaftaranNikahController::class, 'penghulus']);
        Route::get('/{id}', [\App\Http\Controllers\Api\Admin\PendaftaranNikahController::class, 'show']);
        Route::put('/{id}/status', [\App\Http\Controllers\Api\Admin\PendaftaranNikahController::class, 'updateStatus']);
        Route::put('/{id}/penghulu', [\App\Http\Controllers\Api\Admin\PendaftaranNikahController::class, 'assignPenghulu']);
        Route::post('/{id}/jadwal', [\App\Http\Controllers\Api\Admin\PendaftaranNikahController::class, 'createJadwal']);
        Route::put('/{id}/dokumen/{dokumenId}', [\App\Http\Controllers\Api\Admin\PendaftaranNikahController::class, 'updateDokumenStatus']);
    });
});
