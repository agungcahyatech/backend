<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Slider;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::where('is_active', true)
                        ->orderBy('display_order', 'asc')
                        ->get();
        
        return response()->json([
            'success' => true,
            'data' => $sliders
        ]);
    }
} 