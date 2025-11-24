<?php

namespace App\Console\Commands;

use App\Models\GameConfiguration;
use App\Traits\CloudinaryTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateGameConfigurationImagesToCloudinary extends Command
{
    use CloudinaryTrait;

    protected $signature = 'game-configurations:migrate-images-to-cloudinary';
    protected $description = 'Migrate existing game configuration guide images from local storage to Cloudinary';

    public function handle()
    {
        $this->info('Starting migration of game configuration images to Cloudinary...');

        $configurations = GameConfiguration::whereNotNull('guide_image_path')
            ->where('guide_image_path', '!=', '')
            ->get();

        if ($configurations->isEmpty()) {
            $this->info('No game configuration images found to migrate.');
            return;
        }

        $this->info("Found {$configurations->count()} game configuration(s) with images to migrate.");

        $successCount = 0;
        $errorCount = 0;

        foreach ($configurations as $configuration) {
            try {
                $this->info("Processing: {$configuration->name}");

                // Skip if already a Cloudinary URL
                if (filter_var($configuration->guide_image_path, FILTER_VALIDATE_URL) ||
                    str_starts_with($configuration->guide_image_path, 'https://res.cloudinary.com')) {
                    $this->warn("  Skipped: Already a Cloudinary URL");
                    continue;
                }

                // Check if local file exists
                if (!Storage::disk('public')->exists($configuration->guide_image_path)) {
                    $this->error("  Error: Local file not found: {$configuration->guide_image_path}");
                    $errorCount++;
                    continue;
                }

                // Get local file path
                $localPath = Storage::disk('public')->path($configuration->guide_image_path);
                
                // Upload to Cloudinary
                $cloudinaryUrl = $this->uploadToCloudinary(
                    $localPath,
                    'game-configurations/guides',
                    $configuration->name
                );

                if ($cloudinaryUrl) {
                    // Update database
                    $configuration->update(['guide_image_path' => $cloudinaryUrl]);
                    
                    // Delete local file
                    Storage::disk('public')->delete($configuration->guide_image_path);
                    
                    $this->info("  ✅ Successfully migrated to Cloudinary");
                    $successCount++;
                } else {
                    $this->error("  ❌ Failed to upload to Cloudinary");
                    $errorCount++;
                }

            } catch (\Exception $e) {
                $this->error("  ❌ Error processing {$configuration->name}: " . $e->getMessage());
                $errorCount++;
            }
        }

        $this->newLine();
        $this->info("Migration completed!");
        $this->info("✅ Successfully migrated: {$successCount}");
        $this->info("❌ Errors: {$errorCount}");
        
        if ($successCount > 0) {
            $this->info("🎉 Game configuration images have been successfully migrated to Cloudinary!");
        }
    }
} 