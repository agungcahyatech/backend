<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GameConfiguration;

class GameConfigurationController extends Controller
{
    public function index()
    {
        $configs = GameConfiguration::where('is_active', true)
            ->orderBy('id', 'asc')
            ->with(['fields' => function($q) {
                $q->orderBy('display_order', 'asc');
            }])
            ->get();
        return response()->json([
            'success' => true,
            'data' => $configs
        ]);
    }
} 