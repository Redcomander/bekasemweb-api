<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends BaseController
{
    /**
     * Get user notifications
     */
    public function index(Request $request)
    {
        $query = Notifikasi::forUser($request->user()->id)
            ->latest();

        // Filter unread only
        if ($request->boolean('unread_only')) {
            $query->unread();
        }

        $notifikasis = $query->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($notifikasis, 'Daftar notifikasi');
    }

    /**
     * Mark notification as read
     */
    public function markRead(Notifikasi $notifikasi)
    {
        // Ensure user owns this notification
        if ($notifikasi->user_id !== auth()->id()) {
            return $this->forbiddenResponse('Anda tidak memiliki akses ke notifikasi ini');
        }

        $notifikasi->markAsRead();

        return $this->successResponse($notifikasi, 'Notifikasi ditandai sudah dibaca');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllRead(Request $request)
    {
        Notifikasi::forUser($request->user()->id)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return $this->successResponse(null, 'Semua notifikasi ditandai sudah dibaca');
    }

    /**
     * Get unread count
     */
    public function unreadCount(Request $request)
    {
        $count = Notifikasi::forUser($request->user()->id)
            ->unread()
            ->count();

        return $this->successResponse(['count' => $count], 'Jumlah notifikasi belum dibaca');
    }
}
