<?php

namespace App\Http\Controllers\Api;

class SocialController extends BaseController
{
    /**
     * Get social media links
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $allSocials = config('social');
        
        // Filter out null URLs
        $socials = collect($allSocials)
            ->filter(fn($item) => !empty($item['url']))
            ->values()
            ->toArray();

        return $this->successResponse($socials, 'Link media sosial');
    }
}
