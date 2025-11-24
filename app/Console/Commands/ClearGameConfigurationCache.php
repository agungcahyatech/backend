<?php

namespace App\Console\Commands;

use App\Models\Game;
use Illuminate\Console\Command;

class ClearGameConfigurationCache extends Command
{
    protected $signature = 'game-configurations:clear-cache {--slug= : Clear cache for specific game slug}';
    protected $description = 'Clear cached game configuration data';

    public function handle()
    {
        $slug = $this->option('slug');

        if ($slug) {
            // Clear cache for specific game
            $this->clearGameCache($slug);
            $this->info("✅ Cache cleared for game: {$slug}");
        } else {
            // Clear cache for all games
            $games = Game::where('is_active', true)->get();
            
            $this->info("Clearing cache for {$games->count()} games...");
            
            foreach ($games as $game) {
                $this->clearGameCache($game->slug);
            }
            
            $this->info("✅ Cache cleared for all games");
        }
    }

    private function clearGameCache($slug)
    {
        cache()->forget("game_config_info_{$slug}");
        cache()->forget("game_config_fields_{$slug}");
    }
} 