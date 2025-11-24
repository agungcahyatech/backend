<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Page;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::where('is_published', true)
                    ->orderBy('id', 'asc')
                    ->get();
        return response()->json([
            'success' => true,
            'data' => $pages
        ]);
    }
} 