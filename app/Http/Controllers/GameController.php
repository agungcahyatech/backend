<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Game;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class GameController extends Controller
{
    public function index()
    {
        $games = Game::with(['productCategories' => function($query) {
                $query->where('is_active', true)
                      ->orderBy('display_order', 'asc');
            }, 'productCategories.products' => function($query) {
                $query->where('is_active', true)
                      ->orderBy('display_order', 'asc');
            }])
            ->where('is_active', true)
            ->orderBy('display_order', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $games
        ]);
    }

    // New method: Games list without products (faster loading)
    public function list(Request $request)
    {
        $request->validate([
            'with_products' => 'nullable|boolean',
            'limit' => 'nullable|integer|min:1|max:50',
            'category' => 'nullable|string|max:255',
            'is_popular' => 'nullable|boolean',
            'sort' => 'nullable|in:name,developer,display_order,created_at',
            'order' => 'nullable|in:asc,desc',
        ]);

        $query = Game::where('is_active', true);

        // Only load products if explicitly requested
        if ($request->boolean('with_products')) {
            $query->with(['productCategories' => function($query) {
                    $query->where('is_active', true)
                          ->orderBy('display_order', 'asc');
                }, 'productCategories.products' => function($query) {
                    $query->where('is_active', true)
                          ->orderBy('display_order', 'asc');
                }]);
        } else {
            // Just load basic game info
            $query->with(['category']);
        }

        // Filter by category
        if ($request->filled('category')) {
            $categorySlug = $request->get('category');
            $query->whereHas('category', function($q) use ($categorySlug) {
                $q->where('slug', $categorySlug)
                  ->where('is_active', true);
            });
        }

        // Filter by popularity
        if ($request->filled('is_popular')) {
            $query->where('is_popular', $request->get('is_popular'));
        }

        // Sorting
        $sortBy = $request->get('sort', 'display_order');
        $sortOrder = $request->get('order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $limit = $request->get('limit', 20);
        $games = $query->paginate($limit);

        // Transform data based on with_products flag
        $games->getCollection()->transform(function ($game) use ($request) {
            $gameData = [
                'id' => $game->id,
                'name' => $game->name,
                'slug' => $game->slug,
                'developer' => $game->developer,
                'description' => $game->description,
                'image_thumbnail_url' => $game->image_thumbnail_url,
                'image_banner_url' => $game->image_banner_url,
                'is_popular' => $game->is_popular,
                'display_order' => $game->display_order,
                'category' => $game->category ? [
                    'id' => $game->category->id,
                    'name' => $game->category->name,
                    'slug' => $game->category->slug,
                ] : null,
                'created_at' => $game->created_at,
                'updated_at' => $game->updated_at,
            ];

            // Only include products if requested
            if ($request->boolean('with_products')) {
                $gameData['product_categories'] = $game->productCategories->map(function ($productCategory) {
                    return [
                        'id' => $productCategory->id,
                        'name' => $productCategory->name,
                        'slug' => $productCategory->slug,
                        'display_order' => $productCategory->display_order,
                        'products' => $productCategory->products->map(function ($product) {
                            return [
                                'id' => $product->id,
                                'name' => $product->name,
                                'description' => $product->description,
                                'base_price' => $product->base_price,
                                'icon_url' => $product->icon_url,
                                'provider' => $product->provider,
                                'provider_sku' => $product->provider_sku,
                                'display_order' => $product->display_order,
                            ];
                        }),
                    ];
                });
            } else {
                // Just include product categories count for reference
                $gameData['product_categories_count'] = $game->productCategories->count();
            }

            return $gameData;
        });

        return response()->json([
            'success' => true,
            'data' => $games->items(),
            'meta' => [
                'current_page' => $games->currentPage(),
                'last_page' => $games->lastPage(),
                'per_page' => $games->perPage(),
                'total' => $games->total(),
                'from' => $games->firstItem(),
                'to' => $games->lastItem(),
                'with_products' => $request->boolean('with_products'),
            ],
            'links' => [
                'first' => $games->url(1),
                'last' => $games->url($games->lastPage()),
                'prev' => $games->previousPageUrl(),
                'next' => $games->nextPageUrl(),
            ],
        ]);
    }

    public function show($slug)
    {
        $game = Game::select([
                'id', 'name', 'slug', 'developer', 'brand', 'allowed_region',
                'image_thumbnail_path', 'image_banner_path',
                'description', 'long_description', 'faq',
                'is_popular', 'is_active', 'display_order', 'category_id', 'game_configuration_id',
                'created_at', 'updated_at'
            ])
            ->with(['category:id,name,slug', 'gameConfiguration:id,name,guide_text'])
            ->where('is_active', true)
            ->where('slug', $slug)
            ->firstOrFail();

        // Transform data untuk response yang lebih clean
        $gameData = [
            'id' => $game->id,
            'name' => $game->name,
            'slug' => $game->slug,
            'developer' => $game->developer,
            'brand' => $game->brand,
            'allowed_region' => $game->allowed_region,
            'image_thumbnail_url' => $game->image_thumbnail_url,
            'image_banner_url' => $game->image_banner_url,
            'description' => $game->description,
            'long_description' => $game->long_description,
            'faq' => $game->faq,
            'is_popular' => $game->is_popular,
            'display_order' => $game->display_order,
            'category' => $game->category ? [
                'id' => $game->category->id,
                'name' => $game->category->name,
                'slug' => $game->category->slug,
            ] : null,
            'game_configuration' => $game->gameConfiguration ? [
                'id' => $game->gameConfiguration->id,
                'name' => $game->gameConfiguration->name,
                'guide_text' => $game->gameConfiguration->guide_text,
            ] : null,
            'created_at' => $game->created_at,
            'updated_at' => $game->updated_at,
        ];

        return response()->json([
            'success' => true,
            'data' => $gameData
        ]);
    }

    // New method: Get basic game configuration info (Ultra Fast)
    public function configurationInfo($slug)
    {
        // Use caching for better performance
        $cacheKey = "game_config_info_{$slug}";
        $cachedData = cache()->get($cacheKey);
        
        if ($cachedData) {
            return response()->json([
                'success' => true,
                'data' => $cachedData,
                'cached' => true
            ]);
        }

        // Ultra optimized query - only basic info
        $game = Game::select(['id', 'name', 'slug', 'game_configuration_id'])
            ->with(['gameConfiguration:id,name,guide_text,guide_image_path'])
            ->where('is_active', true)
            ->where('slug', $slug)
            ->firstOrFail();

        if (!$game->gameConfiguration) {
            $responseData = [
                'game' => [
                    'id' => $game->id,
                    'name' => $game->name,
                    'slug' => $game->slug,
                ],
                'has_configuration' => false,
                'configuration' => null
            ];
            
            // Cache for 2 hours (basic info changes less frequently)
            cache()->put($cacheKey, $responseData, now()->addHours(2));
            
            return response()->json([
                'success' => true,
                'data' => $responseData
            ]);
        }

        $responseData = [
            'game' => [
                'id' => $game->id,
                'name' => $game->name,
                'slug' => $game->slug,
            ],
            'has_configuration' => true,
            'configuration' => [
                'id' => $game->gameConfiguration->id,
                'name' => $game->gameConfiguration->name,
                'guide_text' => $game->gameConfiguration->guide_text,
                'guide_image_url' => $game->gameConfiguration->guide_image_url,
            ]
        ];

        // Cache for 2 hours
        cache()->put($cacheKey, $responseData, now()->addHours(2));

        return response()->json([
            'success' => true,
            'data' => $responseData
        ]);
    }

    // New method: Get game configuration fields separately (Optimized)
    public function configurationFields($slug)
    {
        // Use caching for better performance
        $cacheKey = "game_config_fields_{$slug}";
        $cachedData = cache()->get($cacheKey);
        
        if ($cachedData) {
            return response()->json([
                'success' => true,
                'data' => $cachedData,
                'cached' => true
            ]);
        }

        // Optimized query with minimal fields
        $game = Game::select(['id', 'name', 'slug', 'game_configuration_id'])
            ->with([
                'gameConfiguration:id,name,guide_text,guide_image_path',
                'gameConfiguration.fields' => function($query) {
                    $query->select([
                        'id', 'game_configuration_id', 'input_name', 'label', 
                        'placeholder', 'options', 'type', 'validation_rules', 
                        'is_required', 'display_order'
                    ])
                    ->orderBy('display_order', 'asc'); // Order at database level
                }
            ])
            ->where('is_active', true)
            ->where('slug', $slug)
            ->firstOrFail();

        if (!$game->gameConfiguration) {
            $responseData = [
                'game' => [
                    'id' => $game->id,
                    'name' => $game->name,
                    'slug' => $game->slug,
                ],
                'configuration' => null,
                'fields' => [],
                'total_fields' => 0
            ];
            
            // Cache for 1 hour
            cache()->put($cacheKey, $responseData, now()->addHour());
            
            return response()->json([
                'success' => true,
                'data' => $responseData
            ]);
        }

        // Minimal data transformation
        $responseData = [
            'game' => [
                'id' => $game->id,
                'name' => $game->name,
                'slug' => $game->slug,
            ],
            'configuration' => [
                'id' => $game->gameConfiguration->id,
                'name' => $game->gameConfiguration->name,
                'guide_text' => $game->gameConfiguration->guide_text,
                'guide_image_url' => $game->gameConfiguration->guide_image_url,
            ],
            'fields' => $game->gameConfiguration->fields->map(function ($field) {
                return [
                    'id' => $field->id,
                    'input_name' => $field->input_name,
                    'label' => $field->label,
                    'placeholder' => $field->placeholder,
                    'options' => $field->options,
                    'type' => $field->type,
                    'validation_rules' => $field->validation_rules,
                    'is_required' => $field->is_required,
                    'display_order' => $field->display_order,
                ];
            }),
            'total_fields' => $game->gameConfiguration->fields->count()
        ];

        // Cache for 1 hour
        cache()->put($cacheKey, $responseData, now()->addHour());

        return response()->json([
            'success' => true,
            'data' => $responseData
        ]);
    }

    // New method: Get game products separately
    public function products($slug, Request $request)
    {
        $game = Game::with(['productCategories' => function($query) {
                $query->where('is_active', true)
                      ->orderBy('display_order', 'asc');
            }, 'productCategories.products' => function($query) {
                $query->where('is_active', true)
                      ->orderBy('display_order', 'asc');
            }, 'productCategories.products.flashSales' => function($query) {
                $query->where('is_active', true)
                      ->where('start_date', '<=', now())
                      ->where('end_date', '>=', now());
            }])
            ->where('is_active', true)
            ->where('slug', $slug)
            ->firstOrFail();

        // Get user role for pricing calculation (if authenticated)
        $userRole = null;
        $userProfitPercentage = 0;
        
        if (Auth::check()) {
            $user = Auth::user();
            $userRole = $user->role;
            $userProfitPercentage = $user->getProfitPercentage();
        }
        
        // If user is not authenticated or has no role, use guest role as default
        if (!$userRole) {
            $guestRole = \App\Models\Role::where('name', 'guest')->first();
            if ($guestRole) {
                $userRole = $guestRole;
                $userProfitPercentage = $guestRole->profit_percentage;
            }
        }

        $productCategories = $game->productCategories->map(function ($productCategory) use ($userRole, $userProfitPercentage) {
            return [
                'id' => $productCategory->id,
                'name' => $productCategory->name,
                'slug' => $productCategory->slug,
                'icon_path' => $productCategory->icon_path,
                'icon_url' => $productCategory->icon_url,
                'display_order' => $productCategory->display_order,
                'products' => $productCategory->products->map(function ($product) use ($userRole, $userProfitPercentage) {
                    // Calculate role-based price
                    $productPrice = $product->base_price;
                    if ($userRole && $userProfitPercentage > 0) {
                        $productPrice = $product->base_price * (1 + ($userProfitPercentage / 100));
                    }

                    // Check for active flash sale
                    $flashSaleInfo = null;
                    $activeFlashSale = $product->flashSales->first();
                    
                    if ($activeFlashSale) {
                        $flashSalePrice = $activeFlashSale->pivot->discounted_price ?? $productPrice;
                        $flashSaleStock = $activeFlashSale->pivot->stock ?? 0;
                        
                        $flashSaleInfo = [
                            'id' => $activeFlashSale->id,
                            'name' => $activeFlashSale->name,
                            'discounted_price' => round($flashSalePrice),
                            'original_price' => round($productPrice),
                            'discount_percentage' => $productPrice > 0 ? round((($productPrice - $flashSalePrice) / $productPrice) * 100, 2) : 0,
                            'stock' => $flashSaleStock,
                            'start_date' => $activeFlashSale->start_date,
                            'end_date' => $activeFlashSale->end_date,
                            'is_active' => true,
                        ];
                    }

                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'base_price' => $product->base_price, // Harga modal
                        'product_price' => round($productPrice), // Harga yang ditampilkan ke frontend (sudah dihitung berdasarkan role + profit margin)
                        'user_role' => $userRole ? [
                            'id' => $userRole->id,
                            'name' => $userRole->name,
                            'profit_percentage' => $userProfitPercentage,
                        ] : null,
                        'flash_sale' => $flashSaleInfo,
                        'final_price' => $flashSaleInfo ? round($flashSaleInfo['discounted_price']) : round($productPrice),
                        'icon_url' => $product->icon_url,
                        'provider' => $product->provider,
                        'provider_sku' => $product->provider_sku,
                        'display_order' => $product->display_order,
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'game' => [
                    'id' => $game->id,
                    'name' => $game->name,
                    'slug' => $game->slug,
                    'developer' => $game->developer,
                ],
                'product_categories' => $productCategories,
                'total_products' => $productCategories->sum(function($category) {
                    return $category['products']->count();
                }),
                'pricing_info' => [
                    'user_authenticated' => Auth::check(),
                    'user_role' => $userRole ? $userRole->name : 'guest',
                    'profit_percentage' => $userProfitPercentage,
                    'price_calculation' => $userProfitPercentage > 0 ? 
                        "Base price + {$userProfitPercentage}% profit margin" : 
                        "Base price (no profit margin)",
                    'is_guest_default' => !Auth::check() || (Auth::check() && !Auth::user()->role),
                ],
            ],
        ]);
    }

    public function search(Request $request)
    {
        $request->validate([
            'q' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'developer' => 'nullable|string|max:255',
            'is_popular' => 'nullable|boolean',
            'with_products' => 'nullable|boolean',
            'limit' => 'nullable|integer|min:1|max:50',
            'sort' => 'nullable|in:name,developer,display_order,created_at',
            'order' => 'nullable|in:asc,desc',
        ]);

        $query = Game::where('is_active', true);

        // Only load products if explicitly requested
        if ($request->boolean('with_products')) {
            $query->with(['productCategories' => function($query) {
                    $query->where('is_active', true)
                          ->orderBy('display_order', 'asc');
                }, 'productCategories.products' => function($query) {
                    $query->where('is_active', true)
                          ->orderBy('display_order', 'asc');
                }, 'category']);
        } else {
            $query->with(['category']);
        }

        // Search by game name or description
        if ($request->filled('q')) {
            $searchTerm = $request->get('q');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('developer', 'like', "%{$searchTerm}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $categorySlug = $request->get('category');
            $query->whereHas('category', function($q) use ($categorySlug) {
                $q->where('slug', $categorySlug)
                  ->where('is_active', true);
            });
        }

        // Filter by developer
        if ($request->filled('developer')) {
            $developer = $request->get('developer');
            $query->where('developer', 'like', "%{$developer}%");
        }

        // Filter by popularity
        if ($request->filled('is_popular')) {
            $query->where('is_popular', $request->get('is_popular'));
        }

        // Sorting
        $sortBy = $request->get('sort', 'display_order');
        $sortOrder = $request->get('order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination/Limit
        $limit = $request->get('limit', 20);
        $games = $query->paginate($limit);

        // Transform data for response
        $games->getCollection()->transform(function ($game) use ($request) {
            $gameData = [
                'id' => $game->id,
                'name' => $game->name,
                'slug' => $game->slug,
                'developer' => $game->developer,
                'description' => $game->description,
                'image_thumbnail_url' => $game->image_thumbnail_url,
                'image_banner_url' => $game->image_banner_url,
                'is_popular' => $game->is_popular,
                'display_order' => $game->display_order,
                'category' => $game->category ? [
                    'id' => $game->category->id,
                    'name' => $game->category->name,
                    'slug' => $game->category->slug,
                ] : null,
                'created_at' => $game->created_at,
                'updated_at' => $game->updated_at,
            ];

            // Only include products if requested
            if ($request->boolean('with_products')) {
                $gameData['product_categories'] = $game->productCategories->map(function ($productCategory) {
                    return [
                        'id' => $productCategory->id,
                        'name' => $productCategory->name,
                        'slug' => $productCategory->slug,
                        'display_order' => $productCategory->display_order,
                        'products' => $productCategory->products->map(function ($product) {
                            return [
                                'id' => $product->id,
                                'name' => $product->name,
                                'description' => $product->description,
                                'base_price' => $product->base_price,
                                'icon_url' => $product->icon_url,
                                'provider' => $product->provider,
                                'provider_sku' => $product->provider_sku,
                                'display_order' => $product->display_order,
                            ];
                        }),
                    ];
                });
            } else {
                // Just include product categories count for reference
                $gameData['product_categories_count'] = $game->productCategories->count();
            }

            return $gameData;
        });

        return response()->json([
            'success' => true,
            'data' => $games->items(),
            'meta' => [
                'current_page' => $games->currentPage(),
                'last_page' => $games->lastPage(),
                'per_page' => $games->perPage(),
                'total' => $games->total(),
                'from' => $games->firstItem(),
                'to' => $games->lastItem(),
                'with_products' => $request->boolean('with_products'),
            ],
            'links' => [
                'first' => $games->url(1),
                'last' => $games->url($games->lastPage()),
                'prev' => $games->previousPageUrl(),
                'next' => $games->nextPageUrl(),
            ],
        ]);
    }

    public function searchSuggestions(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255',
        ]);

        $searchTerm = $request->get('q');
        
        // Get game suggestions
        $gameSuggestions = Game::where('is_active', true)
            ->where('name', 'like', "%{$searchTerm}%")
            ->select('id', 'name', 'slug', 'developer')
            ->limit(5)
            ->get()
            ->map(function ($game) {
                return [
                    'type' => 'game',
                    'id' => $game->id,
                    'name' => $game->name,
                    'slug' => $game->slug,
                    'developer' => $game->developer,
                ];
            });

        // Get developer suggestions
        $developerSuggestions = Game::where('is_active', true)
            ->where('developer', 'like', "%{$searchTerm}%")
            ->select('developer')
            ->distinct()
            ->limit(3)
            ->get()
            ->map(function ($game) {
                return [
                    'type' => 'developer',
                    'name' => $game->developer,
                ];
            });

        // Get category suggestions
        $categorySuggestions = Category::where('is_active', true)
            ->where('name', 'like', "%{$searchTerm}%")
            ->select('id', 'name', 'slug')
            ->limit(3)
            ->get()
            ->map(function ($category) {
                return [
                    'type' => 'category',
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ];
            });

        $suggestions = $gameSuggestions
            ->concat($developerSuggestions)
            ->concat($categorySuggestions)
            ->take(10);

        return response()->json([
            'success' => true,
            'data' => $suggestions,
            'meta' => [
                'search_term' => $searchTerm,
                'total_suggestions' => $suggestions->count(),
            ],
        ]);
    }
} 