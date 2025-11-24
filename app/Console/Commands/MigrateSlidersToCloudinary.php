<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Slider;
use App\Traits\CloudinaryTrait;
use Illuminate\Support\Facades\Storage;

class MigrateSlidersToCloudinary extends Command
{
    use CloudinaryTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sliders:migrate-to-cloudinary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing slider images to Cloudinary';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration of slider images to Cloudinary...');

        $sliders = Slider::where('image_path', 'like', 'sliders/%')->get();
        
        $bar = $this->output->createProgressBar($sliders->count());
        $bar->start();

        foreach ($sliders as $slider) {
            try {
                // Check if file exists in storage
                if (Storage::disk('public')->exists($slider->image_path)) {
                    $filePath = Storage::disk('public')->path($slider->image_path);
                    
                    // Upload to Cloudinary
                    $cloudinaryUrl = $this->uploadToCloudinary($filePath, 'sliders');
                    
                    // Update the slider with Cloudinary URL
                    $slider->update(['image_path' => $cloudinaryUrl]);
                    
                    $this->line("\nMigrated: {$slider->image_path} -> {$cloudinaryUrl}");
                } else {
                    $this->warn("\nFile not found: {$slider->image_path}");
                }
            } catch (\Exception $e) {
                $this->error("\nError migrating {$slider->image_path}: " . $e->getMessage());
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