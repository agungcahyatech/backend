<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Article;
use App\Traits\CloudinaryTrait;
use Illuminate\Support\Facades\Storage;

class MigrateArticleImagesToCloudinary extends Command
{
    use CloudinaryTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:migrate-images-to-cloudinary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing article images to Cloudinary';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration of article images to Cloudinary...');

        $articles = Article::whereNotNull('image_path')
            ->where('image_path', 'not like', 'https://res.cloudinary.com%')
            ->where('image_path', 'not like', 'http%')
            ->get();
        
        $bar = $this->output->createProgressBar($articles->count());
        $bar->start();

        foreach ($articles as $article) {
            try {
                // Check if file exists in storage
                if (Storage::disk('public')->exists($article->image_path)) {
                    $filePath = Storage::disk('public')->path($article->image_path);
                    
                    // Upload to Cloudinary
                    $cloudinaryUrl = $this->uploadToCloudinary($filePath, 'articles');
                    
                    // Update the article with Cloudinary URL
                    $article->update(['image_path' => $cloudinaryUrl]);
                    
                    $this->line("\nMigrated: Article ID {$article->id} -> {$cloudinaryUrl}");
                } else {
                    $this->warn("\nFile not found: {$article->image_path}");
                }
            } catch (\Exception $e) {
                $this->error("\nError migrating Article ID {$article->id}: " . $e->getMessage());
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