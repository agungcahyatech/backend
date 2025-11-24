<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FlashSale;
use Carbon\Carbon;

class FlashSaleController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        
        $flashSales = FlashSale::where('is_active', true)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->orderBy('end_date', 'asc')
            ->with(['products' => function($q) {
                $q->where('is_active', true)
                  ->orderBy('display_order', 'asc')
                  ->with(['game', 'productCategory'])
                  ->withPivot('discounted_price', 'stock');
            }])
            ->get()
            ->map(function ($flashSale) use ($now) {
                // Hitung sisa waktu
                $endDate = Carbon::parse($flashSale->end_date);
                $remainingTime = $now->diffInSeconds($endDate, false);
                
                // Hitung persentase diskon untuk setiap produk
                $products = $flashSale->products->map(function ($product) {
                    $discountPercentage = 0;
                    if ($product->base_price > 0) {
                        $discountPercentage = round((($product->base_price - $product->pivot->discounted_price) / $product->base_price) * 100);
                    }
                    
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'base_price' => $product->base_price,
                        'discounted_price' => $product->pivot->discounted_price,
                        'discount_percentage' => $discountPercentage,
                        'stock' => $product->pivot->stock,
                        'icon_url' => $product->icon_url,
                        'provider' => $product->provider,
                        'provider_sku' => $product->provider_sku,
                        'game' => $product->game ? [
                            'id' => $product->game->id,
                            'name' => $product->game->name,
                            'slug' => $product->game->slug,
                        ] : null,
                        'product_category' => $product->productCategory ? [
                            'id' => $product->productCategory->id,
                            'name' => $product->productCategory->name,
                            'slug' => $product->productCategory->slug,
                        ] : null,
                    ];
                });
                
                return [
                    'id' => $flashSale->id,
                    'name' => $flashSale->name,
                    'start_date' => $flashSale->start_date->toISOString(),
                    'end_date' => $flashSale->end_date->toISOString(),
                    'remaining_seconds' => $remainingTime > 0 ? $remainingTime : 0,
                    'is_active' => $flashSale->is_active,
                    'products' => $products,
                    'total_products' => $products->count(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $flashSales,
            'meta' => [
                'current_time' => $now->toISOString(),
                'total_active_flashsales' => $flashSales->count(),
            ]
        ]);
    }

    public function show($id)
    {
        $now = Carbon::now();
        
        $flashSale = FlashSale::where('is_active', true)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->with(['products' => function($q) {
                $q->where('is_active', true)
                  ->orderBy('display_order', 'asc')
                  ->with(['game', 'productCategory'])
                  ->withPivot('discounted_price', 'stock');
            }])
            ->findOrFail($id);

        // Hitung sisa waktu
        $endDate = Carbon::parse($flashSale->end_date);
        $remainingTime = $now->diffInSeconds($endDate, false);
        
        // Hitung persentase diskon untuk setiap produk
        $products = $flashSale->products->map(function ($product) {
            $discountPercentage = 0;
            if ($product->base_price > 0) {
                $discountPercentage = round((($product->base_price - $product->pivot->discounted_price) / $product->base_price) * 100);
            }
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'base_price' => $product->base_price,
                'discounted_price' => $product->pivot->discounted_price,
                'discount_percentage' => $discountPercentage,
                'stock' => $product->pivot->stock,
                'icon_url' => $product->icon_url,
                'provider' => $product->provider,
                'provider_sku' => $product->provider_sku,
                'game' => $product->game ? [
                    'id' => $product->game->id,
                    'name' => $product->game->name,
                    'slug' => $product->game->slug,
                ] : null,
                'product_category' => $product->productCategory ? [
                    'id' => $product->productCategory->id,
                    'name' => $product->productCategory->name,
                    'slug' => $product->productCategory->slug,
                ] : null,
            ];
        });

        $flashSaleData = [
            'id' => $flashSale->id,
            'name' => $flashSale->name,
            'start_date' => $flashSale->start_date->toISOString(),
            'end_date' => $flashSale->end_date->toISOString(),
            'remaining_seconds' => $remainingTime > 0 ? $remainingTime : 0,
            'is_active' => $flashSale->is_active,
            'products' => $products,
            'total_products' => $products->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $flashSaleData,
            'meta' => [
                'current_time' => $now->toISOString(),
            ]
        ]);
    }

    public function upcoming()
    {
        $now = Carbon::now();
        
        $upcomingFlashSales = FlashSale::where('is_active', true)
            ->where('start_date', '>', $now)
            ->orderBy('start_date', 'asc')
            ->with(['products' => function($q) {
                $q->where('is_active', true)
                  ->orderBy('display_order', 'asc')
                  ->with(['game', 'productCategory'])
                  ->withPivot('discounted_price', 'stock');
            }])
            ->get()
            ->map(function ($flashSale) use ($now) {
                // Hitung waktu sampai mulai
                $startDate = Carbon::parse($flashSale->start_date);
                $timeUntilStart = $now->diffInSeconds($startDate, false);
                
                // Hitung persentase diskon untuk setiap produk
                $products = $flashSale->products->map(function ($product) {
                    $discountPercentage = 0;
                    if ($product->base_price > 0) {
                        $discountPercentage = round((($product->base_price - $product->pivot->discounted_price) / $product->base_price) * 100);
                    }
                    
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'base_price' => $product->base_price,
                        'discounted_price' => $product->pivot->discounted_price,
                        'discount_percentage' => $discountPercentage,
                        'stock' => $product->pivot->stock,
                        'icon_url' => $product->icon_url,
                        'provider' => $product->provider,
                        'provider_sku' => $product->provider_sku,
                        'game' => $product->game ? [
                            'id' => $product->game->id,
                            'name' => $product->game->name,
                            'slug' => $product->game->slug,
                        ] : null,
                        'product_category' => $product->productCategory ? [
                            'id' => $product->productCategory->id,
                            'name' => $product->productCategory->name,
                            'slug' => $product->productCategory->slug,
                        ] : null,
                    ];
                });
                
                return [
                    'id' => $flashSale->id,
                    'name' => $flashSale->name,
                    'start_date' => $flashSale->start_date->toISOString(),
                    'end_date' => $flashSale->end_date->toISOString(),
                    'time_until_start' => $timeUntilStart > 0 ? $timeUntilStart : 0,
                    'is_active' => $flashSale->is_active,
                    'products' => $products,
                    'total_products' => $products->count(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $upcomingFlashSales,
            'meta' => [
                'current_time' => $now->toISOString(),
                'total_upcoming_flashsales' => $upcomingFlashSales->count(),
            ]
        ]);
    }
} 