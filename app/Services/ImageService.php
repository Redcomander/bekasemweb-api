<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Str;

class ImageService
{
    /**
     * Maximum width for images
     */
    protected int $maxWidth = 1920;

    /**
     * Quality for WebP compression (0-100)
     */
    protected int $quality = 80;

    /**
     * Compress and convert image to WebP format
     * 
     * @param UploadedFile $file
     * @param string $directory
     * @return string Path to saved image
     */
    public function processAndStore(UploadedFile $file, string $directory = 'images'): string
    {
        // Generate unique filename with .webp extension
        $filename = Str::random(40) . '.webp';
        $path = $directory . '/' . $filename;

        try {
            // Read the image
            $image = Image::read($file);

            // Resize if larger than max width (maintain aspect ratio)
            if ($image->width() > $this->maxWidth) {
                $image->scale(width: $this->maxWidth);
            }

            // Convert to WebP and get encoded data
            $encoded = $image->toWebp($this->quality);

            // Store the image
            Storage::disk('public')->put($path, $encoded);

            return $path;

        } catch (\Exception $e) {
            // Fallback: store original file if processing fails
            \Log::warning('Image processing failed, storing original: ' . $e->getMessage());
            return $file->store($directory, 'public');
        }
    }

    /**
     * Process image from URL (for gallery selection)
     * Already stored images don't need reprocessing
     */
    public function getPathFromUrl(string $url): string
    {
        // If it's a full URL, extract the path
        if (str_contains($url, '/storage/')) {
            return substr($url, strpos($url, '/storage/') + 9);
        }
        
        return $url;
    }

    /**
     * Set max width for resizing
     */
    public function setMaxWidth(int $width): self
    {
        $this->maxWidth = $width;
        return $this;
    }

    /**
     * Set quality for compression
     */
    public function setQuality(int $quality): self
    {
        $this->quality = max(0, min(100, $quality));
        return $this;
    }

    /**
     * Create thumbnail version
     */
    public function createThumbnail(UploadedFile $file, string $directory = 'thumbnails', int $width = 400): string
    {
        $filename = Str::random(40) . '.webp';
        $path = $directory . '/' . $filename;

        try {
            $image = Image::read($file);
            $image->scale(width: $width);
            $encoded = $image->toWebp($this->quality);
            
            Storage::disk('public')->put($path, $encoded);

            return $path;
        } catch (\Exception $e) {
            \Log::warning('Thumbnail creation failed: ' . $e->getMessage());
            return '';
        }
    }
}
