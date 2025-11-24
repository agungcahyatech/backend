<?php

namespace App\Traits;

use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Storage;

trait CloudinaryTrait
{
    protected function uploadToCloudinary($file, $folder = 'uploads', $customName = null)
    {
        $cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('filesystems.disks.cloudinary.cloud_name'),
                'api_key' => config('filesystems.disks.cloudinary.api_key'),
                'api_secret' => config('filesystems.disks.cloudinary.api_secret'),
            ],
        ]);

        try {
            // Handle both UploadedFile objects and file path strings
            $filePath = is_string($file) ? $file : $file->getRealPath();
            
            $result = $cloudinary->uploadApi()->upload($filePath, [
                'folder' => $folder,
                'resource_type' => 'auto',
                'public_id' => $customName ?: uniqid(),
            ]);

            return $result['secure_url'];
        } catch (\Exception $e) {
            throw new \Exception('Failed to upload to Cloudinary: ' . $e->getMessage());
        }
    }

    protected function deleteFromCloudinary($publicId)
    {
        $cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('filesystems.disks.cloudinary.cloud_name'),
                'api_key' => config('filesystems.disks.cloudinary.api_key'),
                'api_secret' => config('filesystems.disks.cloudinary.api_secret'),
            ],
        ]);

        try {
            $cloudinary->uploadApi()->destroy($publicId);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function getCloudinaryUrl($publicId)
    {
        $cloudName = config('filesystems.disks.cloudinary.cloud_name');
        return "https://res.cloudinary.com/{$cloudName}/image/upload/{$publicId}";
    }
} 