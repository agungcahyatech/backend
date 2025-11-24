<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameConfigurationField extends Model
{
    protected $fillable = ['game_configuration_id', 'input_name', 'label', 'placeholder', 'options', 'type', 'validation_rules', 'is_required', 'display_order'];
    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'display_order' => 'integer',
    ];

    public function gameConfiguration(): BelongsTo
    {
        return $this->belongsTo(GameConfiguration::class);
    }
}