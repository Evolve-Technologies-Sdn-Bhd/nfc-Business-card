<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Upload profile image
     */
    public function uploadProfileImage(UploadedFile $file, $userId)
    {
        $filename = 'profile_' . $userId . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = 'profile-images/' . $filename;

        // Ensure directory exists
        Storage::disk('public')->makeDirectory('profile-images');
        Storage::disk('public')->makeDirectory('profile-images/thumbnails');

        // Store original image
        Storage::disk('public')->put($path, file_get_contents($file));

        // Create thumbnail
        $thumbnailPath = 'profile-images/thumbnails/' . $filename;
        $this->createThumbnail($file, $thumbnailPath, 150, 150);

        return [
            'path' => $path,
            'url' => asset('storage/' . $path),
            'thumbnail_url' => asset('storage/' . $thumbnailPath),
            'size' => $file->getSize(),
        ];
    }

    /**
     * Upload company logo
     */
    public function uploadCompanyLogo(UploadedFile $file, $userId)
    {
        $filename = 'company_' . $userId . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = 'company-logos/' . $filename;

        // Ensure directory exists
        Storage::disk('public')->makeDirectory('company-logos');

        // Store logo
        Storage::disk('public')->put($path, file_get_contents($file));

        return [
            'path' => $path,
            'url' => asset('storage/' . $path),
            'size' => $file->getSize(),
        ];
    }

    /**
     * Upload card design
     */
    public function uploadCardDesign(UploadedFile $file, $userId, $side = 'front')
    {
        $filename = 'card_' . $side . '_' . $userId . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = 'card-designs/' . $filename;

        // Ensure directory exists
        Storage::disk('public')->makeDirectory('card-designs');

        // Store card design
        Storage::disk('public')->put($path, file_get_contents($file));

        return [
            'path' => $path,
            'url' => asset('storage/' . $path),
            'size' => $file->getSize(),
        ];
    }

    /**
     * Create thumbnail from uploaded file
     */
    private function createThumbnail(UploadedFile $file, $path, $width, $height)
    {
        // For now, just copy the original file as thumbnail
        // TODO: Implement proper image resizing when Intervention Image is properly configured
        Storage::disk('public')->put($path, file_get_contents($file));
    }

    /**
     * Delete file from storage
     */
    public function deleteFile($path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            return true;
        }
        return false;
    }

    /**
     * Validate image file.
     *
     * In addition to the extension/mimes rule, the file's REAL content type is
     * verified with PHP's finfo so a spoofed extension (a script, or an SVG
     * renamed to .jpg) cannot slip through. SVG is rejected explicitly because
     * it can carry inline JavaScript.
     */
    public function validateImage(UploadedFile $file, $maxSize = 5120)
    {
        $rules = [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:' . $maxSize,
        ];

        $validator = \Illuminate\Support\Facades\Validator::make(['image' => $file], $rules);

        if (!$validator->passes()) {
            return false;
        }

        // Server-side content sniffing — never trust the client-supplied name.
        return $this->hasAllowedImageMimeType($file);
    }

    /**
     * Read the actual MIME type of the uploaded file (via finfo) and confirm it
     * is an allowed raster image. Anything else — including image/svg+xml — is
     * rejected.
     */
    private function hasAllowedImageMimeType(UploadedFile $file): bool
    {
        $path = $file->getRealPath();
        if (!$path || !is_readable($path)) {
            return false;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($path);

        return in_array($mimeType, [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ], true);
    }

    /**
     * Get file size in human readable format
     */
    public function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
