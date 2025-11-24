<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Popup;

class PopupController extends Controller
{
    public function index()
    {
        $popups = Popup::where('is_active', true)
                    ->orderByDesc('start_date')
                    ->get();
        return response()->json([
            'success' => true,
            'data' => $popups
        ]);
    }
} 