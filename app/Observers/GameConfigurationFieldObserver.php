<?php

namespace App\Observers;

use App\Models\GameConfigurationField;
use App\Models\Game;

class GameConfigurationFieldObserver
{
    /**
     * Handle the GameConfigurationField "created" event.
     */
    public function created(GameConfigurationField $gameConfigurationField): void
    {
        $this->clearRelatedGameCache($gameConfigurationField);
    }

    /**
     * Handle the GameConfigurationField "updated" event.
     */
    public function updated(GameConfigurationField $gameConfigurationField): void
    {
        $this->clearRelatedGameCache($gameConfigurationField);
    }

    /**
     * Handle the GameConfigurationField "deleted" event.
     */
    public function deleted(GameConfigurationField $gameConfigurationField): void
    {
        $this->clearRelatedGameCache($gameConfigurationField);
    }

    /**
     * Clear cache for games related to this configuration field
     */
    private function clearRelatedGameCache(GameConfigurationField $gameConfigurationField): void
    {
        $games = Game::where('game_configuration_id', $gameConfigurationField->game_configuration_id)
            ->where('is_active', true)
            ->get();

        foreach ($games as $game) {
            cache()->forget("game_config_info_{$game->slug}");
            cache()->forget("game_config_fields_{$game->slug}");
        }
    }
} 