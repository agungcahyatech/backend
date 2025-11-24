<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;
use App\Traits\CloudinaryTrait;
use Illuminate\Support\Facades\Storage;

class MigrateSettingsImagesToCloudinary extends Command
{
    use CloudinaryTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'settings:migrate-images-to-cloudinary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing settings logo and favicon to Cloudinary';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration of settings images to Cloudinary...');

        $settings = Setting::whereIn('key', ['site_logo', 'site_favicon'])
            ->where('value', 'like', 'settings/%')
            ->get();
        
        $bar = $this->output->createProgressBar($settings->count());
        $bar->start();

        foreach ($settings as $setting) {
            try {
                // Check if file exists in storage
                if (Storage::disk('public')->exists($setting->value)) {
                    $filePath = Storage::disk('public')->path($setting->value);
                    
                    // Determine folder based on setting key
                    $folder = $setting->key === 'site_logo' ? 'settings/logo' : 'settings/favicon';
                    
                    // Upload to Cloudinary
                    $cloudinaryUrl = $this->uploadToCloudinary($filePath, $folder);
                    
                    // Update the setting with Cloudinary URL
                    $setting->update(['value' => $cloudinaryUrl]);
                    
                    $this->line("\nMigrated: {$setting->key} -> {$cloudinaryUrl}");
                } else {
                    $this->warn("\nFile not found: {$setting->value}");
                }
            } catch (\Exception $e) {
                $this->error("\nError migrating {$setting->key}: " . $e->getMessage());
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Migration completed!');
    }

    protected function uploadToCloudinary($filePath, $folder = 'uploads')
    {
        $cloudinary = new \Cloudinary\Cloudinary([
            'cloud' => [
                'cloud_name' => config('filesystems.disks.cloudinary.cloud_name'),
                'api_key' => config('filesystems.disks.cloudinary.api_key'),
                'api_secret' => config('filesystems.disks.cloudinary.api_secret'),
            ],
        ]);

        try {
            $result = $cloudinary->uploadApi()->upload($filePath, [
                'folder' => $folder,
                'resource_type' => 'auto',
                'public_id' => uniqid(),
            ]);

            return $result['secure_url'];
        } catch (\Exception $e) {
            throw new \Exception('Failed to upload to Cloudinary: ' . $e->getMessage());
        }
    }
} 