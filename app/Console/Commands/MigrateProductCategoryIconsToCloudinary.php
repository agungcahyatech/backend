<?php

namespace App\Console\Commands;

use App\Models\ProductCategory;
use App\Traits\CloudinaryTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateProductCategoryIconsToCloudinary extends Command
{
    use CloudinaryTrait;

    protected $signature = 'product-categories:migrate-icons-to-cloudinary';
    protected $description = 'Migrate existing product category icons from local storage to Cloudinary';

    public function handle()
    {
        $this->info('Starting migration of product category icons to Cloudinary...');

        $productCategories = ProductCategory::whereNotNull('icon_path')
            ->where('icon_path', '!=', '')
            ->get();

        if ($productCategories->isEmpty()) {
            $this->info('No product category icons found to migrate.');
            return;
        }

        $this->info("Found {$productCategories->count()} product category(ies) with icons to migrate.");

        $successCount = 0;
        $errorCount = 0;

        foreach ($productCategories as $productCategory) {
            try {
                $this->info("Processing: {$productCategory->name}");

                // Skip if already a Cloudinary URL
                if (filter_var($productCategory->icon_path, FILTER_VALIDATE_URL) ||
                    str_starts_with($productCategory->icon_path, 'https://res.cloudinary.com')) {
                    $this->warn("  Skipped: Already a Cloudinary URL");
                    continue;
                }

                // Check if local file exists
                if (!Storage::disk('public')->exists($productCategory->icon_path)) {
                    $this->error("  Error: Local file not found: {$productCategory->icon_path}");
                    $errorCount++;
                    continue;
                }

                // Get local file path
                $localPath = Storage::disk('public')->path($productCategory->icon_path);
                
                // Upload to Cloudinary
                $cloudinaryUrl = $this->uploadToCloudinary(
                    $localPath,
                    'product-categories/icons',
                    $productCategory->name
                );

                if ($cloudinaryUrl) {
                    // Update database
                    $productCategory->update(['icon_path' => $cloudinaryUrl]);
                    
                    // Delete local file
                    Storage::disk('public')->delete($productCategory->icon_path);
                    
                    $this->info("  ✅ Successfully migrated to Cloudinary");
                    $successCount++;
                } else {
                    $this->error("  ❌ Failed to upload to Cloudinary");
                    $errorCount++;
                }

            } catch (\Exception $e) {
                $this->error("  ❌ Error processing {$productCategory->name}: " . $e->getMessage());
                $errorCount++;
            }
        }

        $this->newLine();
        $this->info("Migration completed!");
        $this->info("✅ Successfully migrated: {$successCount}");
        $this->info("❌ Errors: {$errorCount}");
        
        if ($successCount > 0) {
            $this->info("🎉 Product category icons have been successfully migrated to Cloudinary!");
        }
    }
} 