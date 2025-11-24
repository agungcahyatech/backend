<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Game;
use App\Traits\CloudinaryTrait;
use Illuminate\Support\Facades\Storage;

class MigrateGameImagesToCloudinary extends Command
{
    use CloudinaryTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'games:migrate-images-to-cloudinary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing game thumbnail and banner images to Cloudinary';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration of game images to Cloudinary...');

        $games = Game::where(function($query) {
            $query->where('image_thumbnail_path', 'like', 'games/%')
                  ->orWhere('image_banner_path', 'like', 'games/%');
        })->get();
        
        $bar = $this->output->createProgressBar($games->count());
        $bar->start();

        foreach ($games as $game) {
            try {
                $updated = false;

                // Migrate thumbnail image
                if ($game->image_thumbnail_path && !filter_var($game->image_thumbnail_path, FILTER_VALIDATE_URL)) {
                    if (Storage::disk('public')->exists($game->image_thumbnail_path)) {
                        $filePath = Storage::disk('public')->path($game->image_thumbnail_path);
                        
                        // Upload to Cloudinary
                        $cloudinaryUrl = $this->uploadToCloudinary($filePath, 'games/thumbnails');
                        
                        // Update the game with Cloudinary URL
                        $game->image_thumbnail_path = $cloudinaryUrl;
                        $updated = true;
                        
                        $this->line("\nMigrated thumbnail: {$game->name} -> {$cloudinaryUrl}");
                    } else {
                        $this->warn("\nThumbnail file not found: {$game->image_thumbnail_path}");
                    }
                }

                // Migrate banner image
                if ($game->image_banner_path && !filter_var($game->image_banner_path, FILTER_VALIDATE_URL)) {
                    if (Storage::disk('public')->exists($game->image_banner_path)) {
                        $filePath = Storage::disk('public')->path($game->image_banner_path);
                        
                        // Upload to Cloudinary
                        $cloudinaryUrl = $this->uploadToCloudinary($filePath, 'games/banners');
                        
                        // Update the game with Cloudinary URL
                        $game->image_banner_path = $cloudinaryUrl;
                        $updated = true;
                        
                        $this->line("\nMigrated banner: {$game->name} -> {$cloudinaryUrl}");
                    } else {
                        $this->warn("\nBanner file not found: {$game->image_banner_path}");
                    }
                }

                // Save the game if any images were updated
                if ($updated) {
                    $game->save();
                }

            } catch (\Exception $e) {
                $this->error("\nError migrating {$game->name}: " . $e->getMessage());
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