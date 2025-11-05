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
     * Validate image file
     */
    public function validateImage(UploadedFile $file, $maxSize = 5120)
    {
        $rules = [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:' . $maxSize,
        ];

        $validator = \Illuminate\Support\Facades\Validator::make(['image' => $file], $rules);
        
        return $validator->passes();
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
