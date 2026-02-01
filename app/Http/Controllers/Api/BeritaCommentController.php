<?php

namespace App\Http\Controllers\Api;

use App\Models\Berita;
use App\Models\BeritaComment;
use App\Services\SpamFilterService;
use Illuminate\Http\Request;

class BeritaCommentController extends BaseController
{
    protected SpamFilterService $spamFilter;

    public function __construct()
    {
        $this->spamFilter = new SpamFilterService();
    }

    /**
     * Get comments for a berita
     */
    public function index(Berita $berita)
    {
        $comments = $berita->comments()
            ->approved()
            ->latest()
            ->get();

        return $this->successResponse($comments, 'Daftar komentar');
    }

    /**
     * Store a comment (public, no auth required)
     */
    public function store(Request $request, Berita $berita)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'komentar' => 'required|string|min:3|max:1000',
            'website' => 'nullable|string', // Honeypot field
        ]);

        // Run spam filter validation
        $spamCheck = $this->spamFilter->validate($request, $berita->id);

        if (!$spamCheck['valid']) {
            // If needs moderation (spam/bad word detected), save but don't approve
            if ($spamCheck['needs_moderation'] ?? false) {
                $comment = $berita->comments()->create([
                    'nama' => $validated['nama'],
                    'email' => $validated['email'] ?? null,
                    'komentar' => $validated['komentar'],
                    'is_approved' => false, // Needs moderation
                    'is_spam' => true,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return $this->successResponse(null, 'Komentar Anda sedang ditinjau oleh moderator');
            }

            return $this->errorResponse($spamCheck['reason'], 429);
        }

        // Create approved comment
        $comment = $berita->comments()->create([
            'nama' => $validated['nama'],
            'email' => $validated['email'] ?? null,
            'komentar' => $validated['komentar'],
            'is_approved' => true,
            'is_spam' => false,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $this->createdResponse($comment, 'Komentar berhasil ditambahkan');
    }

    /**
     * Get comment count for a berita
     */
    public function count(Berita $berita)
    {
        $count = $berita->comments()->approved()->count();
        return $this->successResponse(['count' => $count], 'Jumlah komentar');
    }
}
