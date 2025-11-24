<?php

namespace App\Observers;

use App\Models\GameConfiguration;
use App\Models\Game;

class GameConfigurationObserver
{
    /**
     * Handle the GameConfiguration "created" event.
     */
    public function created(GameConfiguration $gameConfiguration): void
    {
        $this->clearRelatedGameCache($gameConfiguration);
    }

    /**
     * Handle the GameConfiguration "updated" event.
     */
    public function updated(GameConfiguration $gameConfiguration): void
    {
        $this->clearRelatedGameCache($gameConfiguration);
    }

    /**
     * Handle the GameConfiguration "deleted" event.
     */
    public function deleted(GameConfiguration $gameConfiguration): void
    {
        $this->clearRelatedGameCache($gameConfiguration);
    }

    /**
     * Clear cache for games related to this configuration
     */
    private function clearRelatedGameCache(GameConfiguration $gameConfiguration): void
    {
        $games = Game::where('game_configuration_id', $gameConfiguration->id)
            ->where('is_active', true)
            ->get();

        foreach ($games as $game) {
            cache()->forget("game_config_info_{$game->slug}");
            cache()->forget("game_config_fields_{$game->slug}");
        }
    }
} 