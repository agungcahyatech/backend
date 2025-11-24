<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::where('is_published', true)
                        ->orderByDesc('publish_date')
                        ->get();
        return response()->json([
            'success' => true,
            'data' => $articles
        ]);
    }
} 