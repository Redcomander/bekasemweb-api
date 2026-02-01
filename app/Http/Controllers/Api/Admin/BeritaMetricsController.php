<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use App\Models\BeritaComment;
use App\Models\BeritaLike;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BeritaMetricsController extends Controller
{
    /**
     * Get overview metrics for dashboard (optimized)
     */
    public function overview()
    {
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // Single query for all article stats
        $articleStats = Berita::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
            SUM(view_count) as total_views,
            SUM(CASE WHEN updated_at >= ? THEN view_count ELSE 0 END) as views_this_month,
            SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as articles_this_month,
            SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as articles_last_month
        ", [$thisMonth, $thisMonth, $lastMonth, $lastMonthEnd])
            ->first();

        // Single query for all like stats
        $likeStats = BeritaLike::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as this_month
        ", [$thisMonth])
            ->first();

        // Single query for all comment stats
        $commentStats = BeritaComment::selectRaw("
            SUM(CASE WHEN is_approved = 1 THEN 1 ELSE 0 END) as total_approved,
            SUM(CASE WHEN is_approved = 1 AND created_at >= ? THEN 1 ELSE 0 END) as approved_this_month,
            SUM(CASE WHEN is_approved = 0 AND is_spam = 0 THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN is_spam = 1 THEN 1 ELSE 0 END) as spam
        ", [$thisMonth])
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'articles' => [
                    'total' => (int) $articleStats->total,
                    'published' => (int) $articleStats->published,
                    'this_month' => (int) $articleStats->articles_this_month,
                    'last_month' => (int) $articleStats->articles_last_month,
                ],
                'views' => [
                    'total' => (int) $articleStats->total_views,
                    'this_month' => (int) $articleStats->views_this_month,
                ],
                'likes' => [
                    'total' => (int) $likeStats->total,
                    'this_month' => (int) $likeStats->this_month,
                ],
                'comments' => [
                    'total' => (int) $commentStats->total_approved,
                    'this_month' => (int) $commentStats->approved_this_month,
                    'pending' => (int) $commentStats->pending,
                    'spam' => (int) $commentStats->spam,
                ],
            ]
        ]);
    }

    /**
     * Get top performing articles
     */
    public function topArticles(Request $request)
    {
        $limit = $request->get('limit', 10);
        $sortBy = $request->get('sort_by', 'views'); // views, likes, comments

        $query = Berita::with('author')
            ->withCount(['likes', 'comments' => function ($q) {
                $q->where('is_approved', true);
            }]);

        switch ($sortBy) {
            case 'likes':
                $query->orderByDesc('likes_count');
                break;
            case 'comments':
                $query->orderByDesc('comments_count');
                break;
            default:
                $query->orderByDesc('view_count');
        }

        $articles = $query->limit($limit)->get();

        return response()->json([
            'success' => true,
            'data' => $articles->map(function ($article) {
                return [
                    'id' => $article->id,
                    'judul' => $article->judul,
                    'slug' => $article->slug,
                    'kategori' => $article->kategori,
                    'status' => $article->status,
                    'gambar' => $article->gambar,
                    'author' => $article->author?->name,
                    'view_count' => $article->view_count,
                    'likes_count' => $article->likes_count,
                    'comments_count' => $article->comments_count,
                    'created_at' => $article->created_at,
                ];
            })
        ]);
    }

    /**
     * Get views trend over time
     */
    public function trends(Request $request)
    {
        $period = $request->get('period', '30'); // 7, 30, 90 days
        $startDate = Carbon::now()->subDays((int)$period);

        // Daily views trend
        $viewsTrend = Berita::select(
            DB::raw('DATE(updated_at) as date'),
            DB::raw('SUM(view_count) as total_views')
        )
            ->where('updated_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Daily likes trend
        $likesTrend = BeritaLike::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Daily comments trend
        $commentsTrend = BeritaComment::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('is_approved', true)
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Category distribution
        $categoryDistribution = Berita::select('kategori', DB::raw('COUNT(*) as count'))
            ->groupBy('kategori')
            ->orderByDesc('count')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'views_trend' => $viewsTrend,
                'likes_trend' => $likesTrend,
                'comments_trend' => $commentsTrend,
                'category_distribution' => $categoryDistribution,
            ]
        ]);
    }

    /**
     * Get all comments for moderation
     */
    public function comments(Request $request)
    {
        $status = $request->get('status', 'pending'); // pending, approved, spam
        $perPage = $request->get('per_page', 20);

        $query = BeritaComment::with(['berita:id,judul,slug'])
            ->orderByDesc('created_at');

        switch ($status) {
            case 'approved':
                $query->where('is_approved', true)->where('is_spam', false);
                break;
            case 'spam':
                $query->where('is_spam', true);
                break;
            default: // pending
                $query->where('is_approved', false)->where('is_spam', false);
        }

        $comments = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $comments
        ]);
    }

    /**
     * Approve a comment
     */
    public function approveComment($id)
    {
        $comment = BeritaComment::findOrFail($id);
        $comment->update([
            'is_approved' => true,
            'is_spam' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Komentar berhasil disetujui'
        ]);
    }

    /**
     * Mark a comment as spam
     */
    public function markSpam($id)
    {
        $comment = BeritaComment::findOrFail($id);
        $comment->update([
            'is_spam' => true,
            'is_approved' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Komentar ditandai sebagai spam'
        ]);
    }

    /**
     * Delete a comment
     */
    public function deleteComment($id)
    {
        $comment = BeritaComment::findOrFail($id);
        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Komentar berhasil dihapus'
        ]);
    }
}
