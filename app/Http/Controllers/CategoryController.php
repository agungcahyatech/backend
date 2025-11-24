<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('display_order', 'asc')
            ->with(['games' => function($query) {
                $query->where('is_active', true)
                      ->orderBy('display_order', 'asc')
                      ->select('id', 'name', 'slug', 'developer', 'image_thumbnail_path', 'image_banner_path', 'description', 'is_popular', 'category_id', 'display_order');
            }])
            ->get()
            ->map(function ($category) {
                $games = $category->games->map(function ($game) {
                    return [
                        'id' => $game->id,
                        'name' => $game->name,
                        'slug' => $game->slug,
                        'developer' => $game->developer,
                        'image_thumbnail_url' => $game->image_thumbnail_url,
                        'image_banner_url' => $game->image_banner_url,
                        'description' => $game->description,
                        'is_popular' => $game->is_popular,
                        'display_order' => $game->display_order,
                    ];
                });

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'display_order' => $category->display_order,
                    'is_active' => $category->is_active,
                    'games' => $games,
                    'total_games' => $games->count(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $categories,
            'meta' => [
                'total_categories' => $categories->count(),
                'total_games' => $categories->sum('total_games'),
            ]
        ]);
    }

    public function show($slug)
    {
        $category = Category::where('is_active', true)
            ->where('slug', $slug)
            ->with(['games' => function($query) {
                $query->where('is_active', true)
                      ->orderBy('display_order', 'asc')
                      ->select('id', 'name', 'slug', 'developer', 'image_thumbnail_path', 'image_banner_path', 'description', 'is_popular', 'category_id', 'display_order');
            }])
            ->firstOrFail();

        $games = $category->games->map(function ($game) {
            return [
                'id' => $game->id,
                'name' => $game->name,
                'slug' => $game->slug,
                'developer' => $game->developer,
                'image_thumbnail_url' => $game->image_thumbnail_url,
                'image_banner_url' => $game->image_banner_url,
                'description' => $game->description,
                'is_popular' => $game->is_popular,
                'display_order' => $game->display_order,
            ];
        });

        $categoryData = [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'display_order' => $category->display_order,
            'is_active' => $category->is_active,
            'games' => $games,
            'total_games' => $games->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $categoryData,
            'meta' => [
                'total_games' => $games->count(),
            ]
        ]);
    }
} 