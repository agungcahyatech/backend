<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all payment methods with local image paths
        $paymentMethods = DB::table('payment_methods')
            ->whereNotNull('image_path')
            ->where('image_path', '!=', '')
            ->where('image_path', 'not like', 'https://res.cloudinary.com%')
            ->get();

        foreach ($paymentMethods as $paymentMethod) {
            try {
                $oldPath = $paymentMethod->image_path;
                
                // Check if file exists in storage
                if (Storage::disk('public')->exists($oldPath)) {
                    // Get file content
                    $fileContent = Storage::disk('public')->get($oldPath);
                    $fileName = basename($oldPath);
                    
                    // Upload to Cloudinary
                    $cloudinaryUrl = $this->uploadToCloudinary($fileContent, $fileName, 'payment-methods');
                    
                    if ($cloudinaryUrl) {
                        // Update database with Cloudinary URL
                        DB::table('payment_methods')
                            ->where('id', $paymentMethod->id)
                            ->update(['image_path' => $cloudinaryUrl]);
                        
                        // Delete local file
                        Storage::disk('public')->delete($oldPath);
                        
                        Log::info("Migrated payment method image: {$oldPath} -> {$cloudinaryUrl}");
                    }
                }
            } catch (\Exception $e) {
                Log::error("Failed to migrate payment method image {$paymentMethod->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be safely reversed
        // as we don't have the original local files
        Log::warning('Payment method image migration cannot be reversed');
    }

    /**
     * Upload file to Cloudinary
     */
    private function uploadToCloudinary($fileContent, $fileName, $folder = 'payment-methods'): ?string
    {
        try {
            $cloudinary = new \Cloudinary\Cloudinary([
                'cloud' => [
                    'cloud_name' => config('services.cloudinary.cloud_name'),
                    'api_key' => config('services.cloudinary.api_key'),
                    'api_secret' => config('services.cloudinary.api_secret'),
                ],
            ]);

            // Upload to Cloudinary
            $result = $cloudinary->uploadApi()->upload(
                $fileContent,
                [
                    'public_id' => $folder . '/' . pathinfo($fileName, PATHINFO_FILENAME),
                    'resource_type' => 'image',
                    'overwrite' => true,
                ]
            );

            return $result['secure_url'] ?? null;
        } catch (\Exception $e) {
            Log::error("Cloudinary upload failed: " . $e->getMessage());
            return null;
        }
    }
};
