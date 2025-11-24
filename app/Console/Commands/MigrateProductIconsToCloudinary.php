<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Traits\CloudinaryTrait;
use Illuminate\Support\Facades\Storage;

class MigrateProductIconsToCloudinary extends Command
{
    use CloudinaryTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:migrate-icons-to-cloudinary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing product icons to Cloudinary';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration of product icons to Cloudinary...');

        $products = Product::where('icon_path', 'like', 'products/%')->get();
        
        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        foreach ($products as $product) {
            try {
                // Check if file exists in storage
                if (Storage::disk('public')->exists($product->icon_path)) {
                    $filePath = Storage::disk('public')->path($product->icon_path);
                    
                    // Upload to Cloudinary
                    $cloudinaryUrl = $this->uploadToCloudinary($filePath, 'products/icons');
                    
                    // Update the product with Cloudinary URL
                    $product->update(['icon_path' => $cloudinaryUrl]);
                    
                    $this->line("\nMigrated: {$product->icon_path} -> {$cloudinaryUrl}");
                } else {
                    $this->warn("\nFile not found: {$product->icon_path}");
                }
            } catch (\Exception $e) {
                $this->error("\nError migrating {$product->icon_path}: " . $e->getMessage());
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