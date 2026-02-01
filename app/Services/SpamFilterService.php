<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class SpamFilterService
{
    /**
     * Indonesian gambling spam keywords
     */
    protected array $gamblingKeywords = [
        // Gambling sites
        'slot gacor', 'slot online', 'slot hari ini', 'slot maxwin', 'slot pragmatic',
        'togel', 'togel online', 'togel hari ini', 'togel singapore', 'togel hongkong',
        'judi online', 'judi slot', 'judi bola', 'agen judi', 'bandar judi',
        'casino online', 'live casino', 'poker online', 'domino online',
        'deposit pulsa', 'withdraw cepat', 'bonus new member', 'bonus deposit',
        'rtp live', 'rtp slot', 'bocoran slot', 'pola slot', 'jam gacor',
        'maxwin', 'scatter hitam', 'scatter x500', 'gacor hari ini',
        'situs slot', 'link slot', 'daftar slot', 'login slot',
        'pragmatic play', 'pg soft', 'habanero', 'spadegaming', 'gacor',
        
        // Common spam patterns
        'klik di sini', 'hubungi kami', 'wa.me/', 'bit.ly/', 't.me/',
        'link alternatif', 'link resmi', 'link terbaru',
        'jp paus', 'jackpot', 'menang besar', 'cuan',
        'zeus', 'olympus', 'gates of', 'starlight princess', 'sweet bonanza',
        
        // Adult content
        'film bokep', '18+', 'indo xxx', 'video viral',
    ];

    /**
     * Indonesian bad words (profanity filter)
     */
    protected array $badWords = [
        // Common Indonesian profanity
        'anjing', 'bangsat', 'babi', 'bajingan', 'kampret', 'kontol', 'memek',
        'ngentot', 'pepek', 'jancok', 'jancuk', 'asu', 'goblog', 'goblok',
        'tolol', 'bodoh', 'idiot', 'bego', 'brengsek', 'keparat', 'sialan',
        'tai', 'fuck', 'shit', 'bitch', 'asshole', 'bastard',
        
        // Variations
        'a*u', 'b*bi', 'k*ntol', 'mem*k', 'j*ncok',
    ];

    /**
     * Check if content is spam or contains bad words
     * 
     * @param string $content
     * @return array ['is_spam' => bool, 'is_bad_word' => bool, 'reason' => string|null]
     */
    public function check(string $content): array
    {
        $contentLower = strtolower($content);
        
        // Check for gambling spam
        foreach ($this->gamblingKeywords as $keyword) {
            if (str_contains($contentLower, strtolower($keyword))) {
                return [
                    'is_spam' => true,
                    'is_bad_word' => false,
                    'reason' => 'Terdeteksi sebagai spam judi/gambling',
                    'matched' => $keyword,
                ];
            }
        }

        // Check for bad words
        foreach ($this->badWords as $word) {
            if (str_contains($contentLower, strtolower($word))) {
                return [
                    'is_spam' => false,
                    'is_bad_word' => true,
                    'reason' => 'Terdeteksi kata-kata tidak pantas',
                    'matched' => $word,
                ];
            }
        }

        return [
            'is_spam' => false,
            'is_bad_word' => false,
            'reason' => null,
            'matched' => null,
        ];
    }

    /**
     * Check rate limit for comments (max 3 per minute per IP)
     */
    public function checkRateLimit(string $ipAddress, int $maxPerMinute = 3): bool
    {
        $key = 'comment_rate_' . md5($ipAddress);
        $count = Cache::get($key, 0);

        if ($count >= $maxPerMinute) {
            return false; // Rate limit exceeded
        }

        Cache::put($key, $count + 1, 60); // Expire in 60 seconds
        return true;
    }

    /**
     * Check if honeypot field is filled (spam bot detection)
     */
    public function checkHoneypot(Request $request): bool
    {
        // If honeypot field (website) is filled, it's likely a bot
        return empty($request->input('website'));
    }

    /**
     * Check for duplicate comments
     */
    public function isDuplicate(int $beritaId, string $content, string $ipAddress): bool
    {
        $key = 'comment_hash_' . md5($beritaId . $content . $ipAddress);
        
        if (Cache::has($key)) {
            return true;
        }

        Cache::put($key, true, 300); // 5 minutes
        return false;
    }

    /**
     * Full validation
     */
    public function validate(Request $request, int $beritaId): array
    {
        $ipAddress = $request->ip();
        $content = $request->input('komentar', '') . ' ' . $request->input('nama', '');

        // Check honeypot
        if (!$this->checkHoneypot($request)) {
            return ['valid' => false, 'reason' => 'Terdeteksi sebagai bot'];
        }

        // Check rate limit
        if (!$this->checkRateLimit($ipAddress)) {
            return ['valid' => false, 'reason' => 'Terlalu banyak komentar. Coba lagi nanti.'];
        }

        // Check duplicate
        if ($this->isDuplicate($beritaId, $content, $ipAddress)) {
            return ['valid' => false, 'reason' => 'Komentar duplikat terdeteksi'];
        }

        // Check spam/bad words
        $check = $this->check($content);
        if ($check['is_spam'] || $check['is_bad_word']) {
            return [
                'valid' => false,
                'reason' => $check['reason'],
                'needs_moderation' => true, // Flag for moderation instead of rejection
            ];
        }

        return ['valid' => true, 'reason' => null];
    }
}
