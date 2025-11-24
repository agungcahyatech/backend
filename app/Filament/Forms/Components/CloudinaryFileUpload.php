<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\FileUpload;
use App\Traits\CloudinaryTrait;
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CloudinaryFileUpload extends FileUpload
{
    use CloudinaryTrait;

    protected function setUp(): void
    {
        parent::setUp();

        // Save uploaded file and handle Cloudinary upload
        $this->saveUploadedFileUsing(function ($file) {
            // Generate unique filename to prevent conflicts
            $filename = uniqid() . '_' . $file->getClientOriginalName();
            $directory = $this->getDirectory() ?: 'uploads';
            $localPath = $directory . '/' . $filename;
            
            // Save to local storage permanently
            $file->storeAs($directory, $filename, 'public');
            
            // Upload to Cloudinary in the background
            $this->uploadToCloudinaryAsync($file, $directory, $localPath);
            
            return $localPath;
        });

        // Disable automatic state updates to prevent file loss
        // We'll handle URL conversion only during save operation
    }

    public function getState(): mixed
    {
        $state = parent::getState();
        
        // Handle existing Cloudinary URLs that might be stored as strings
        if (is_string($state) && filter_var($state, FILTER_VALIDATE_URL)) {
            return [$state];
        }
        
        // If it's an array, return as is without modification
        if (is_array($state)) {
            return $state;
        }
        
        // If it's a single file path, wrap in array
        if (is_string($state)) {
            return [$state];
        }
        
        return $state;
    }

    protected function uploadToCloudinaryAsync($file, $folder, $localPath)
    {
        // Upload to Cloudinary in the background
        try {
            $cloudinaryUrl = $this->uploadToCloudinary($file, $folder);
            
            // Store the mapping of local path to Cloudinary URL
            $this->storeCloudinaryMapping($localPath, $cloudinaryUrl);
            
            return $cloudinaryUrl;
        } catch (\Exception $e) {
            Log::error('Failed to upload to Cloudinary: ' . $e->getMessage());
            return null;
        }
    }

    protected function storeCloudinaryMapping($localPath, $cloudinaryUrl)
    {
        // Store the mapping in cache for quick lookup
        $cacheKey = 'cloudinary_mapping_' . md5($localPath);
        Cache::put($cacheKey, $cloudinaryUrl, now()->addHours(24));
    }

    protected function getCloudinaryUrlForLocalFile($localPath)
    {
        // Check cache for Cloudinary URL mapping
        $cacheKey = 'cloudinary_mapping_' . md5($localPath);
        return Cache::get($cacheKey);
    }

    protected function getUploadedFileUrlUsing(): ?string
    {
        $state = $this->getState();
        
        if (is_array($state) && !empty($state)) {
            $file = $state[0];
            
            // If it's already a Cloudinary URL, return it directly
            if (is_string($file) && filter_var($file, FILTER_VALIDATE_URL) && str_contains($file, 'cloudinary.com')) {
                return $file;
            }
            
            // If it's a local file path, return local URL for preview
            if (is_string($file) && !filter_var($file, FILTER_VALIDATE_URL)) {
                // Always return local URL if file path exists, don't check storage
                return asset('storage/' . $file);
            }
            
            // If it's an UploadedFile object, return temporary URL
            if (is_object($file) && method_exists($file, 'getRealPath')) {
                return $file->getRealPath();
            }
        }
        
        return null;
    }

    protected function getUploadedFileUrlForPreviewUsing(): ?string
    {
        return $this->getUploadedFileUrlUsing();
    }

    protected function getUploadedFileUrls(): array
    {
        $state = $this->getState();
        
        if (is_array($state)) {
            return $state;
        }
        
        return is_string($state) ? [$state] : [];
    }

    protected function getUploadedFileUrl(): ?string
    {
        $state = $this->getState();
        
        if (is_array($state) && !empty($state)) {
            return $state[0];
        }
        
        return is_string($state) ? $state : null;
    }
} 