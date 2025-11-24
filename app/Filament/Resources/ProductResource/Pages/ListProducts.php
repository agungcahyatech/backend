<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Filament\Widgets\ProductStatsWidget;
use App\Models\Game;
use App\Models\Product;
use App\Models\Setting;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    // Optimasi query dengan eager loading untuk menghindari N+1 problem
    protected function getTableQuery(): Builder
    {
        return Product::query()
            ->with(['game:id,name', 'productCategory:id,name'])
            ->select(['id', 'name', 'icon_path', 'base_price', 'provider_sku', 'is_active', 'game_id', 'product_category_id', 'created_at']);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New product'),
            
            // Get Product from Digiflazz Button
            Actions\Action::make('get_digiflazz_products')
                ->label('Get Product Digiflazz')
                ->icon('heroicon-o-arrow-path-rounded-square')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Get Products from Digiflazz')
                ->modalDescription('Pilih game untuk mengambil produk dari Digiflazz. Proses ini akan menambahkan produk baru ke database.')
                ->modalSubmitActionLabel('Get Products')
                ->modalCancelActionLabel('Cancel')
                ->form([
                    Forms\Components\Select::make('game_id')
                        ->label('Game')
                        ->options(function () {
                            // Get credentials from settings
                            $username = Setting::getValue('digiflazz_username');
                            $apiKey = Setting::getValue('digiflazz_api_key');

                            if (!$username || !$apiKey) {
                                return ['error' => 'Please fill in Digiflazz Username & API Key in Settings'];
                            }

                            $signature = md5($username . $apiKey . 'pricelist');

                            try {
                                // Add longer delay to avoid rate limiting
                                usleep(3000000); // 3 second delay

                                $response = Http::timeout(30)
                                    ->retry(3, 3000) // Increased retry delay
                                    ->post('https://api.digiflazz.com/v1/price-list', [
                                        'cmd' => 'prepaid',
                                        'username' => $username,
                                        'sign' => $signature,
                                    ]);

                                if ($response->failed()) {
                                    return ['error' => 'Cannot connect to Digiflazz API. Status: ' . $response->status()];
                                }

                                $responseData = $response->json();

                                // Check for rate limit error
                                if (isset($responseData['rc']) && $responseData['rc'] === '83') {
                                    return ['error' => 'Digiflazz API rate limit reached. Please wait a few minutes and try again.'];
                                }

                                if (isset($responseData['message']) && str_contains($responseData['message'], 'limitasi')) {
                                    return ['error' => 'Digiflazz API rate limit reached. Please wait a few minutes and try again.'];
                                }

                                // Debug: Log response for troubleshooting
                                Log::info('Digiflazz API Response for games:', [
                                    'response_status' => $response->status(),
                                    'data_count' => isset($responseData['data']) ? count($responseData['data']) : 0
                                ]);

                                // Validate response structure
                                if (!isset($responseData['data']) || !is_array($responseData['data'])) {
                                    return ['error' => 'Invalid API response format. Response: ' . json_encode($responseData)];
                                }

                                $productsFromApi = $responseData['data'];
                                $availableBrands = [];

                                // Get all available brands from API
                                foreach ($productsFromApi as $productData) {
                                    if (!is_array($productData) || !isset($productData['brand'])) {
                                        continue;
                                    }

                                    $brand = trim(strtoupper($productData['brand']));
                                    if (!in_array($brand, $availableBrands)) {
                                        $availableBrands[] = $brand;
                                    }
                                }

                                // Sort brands alphabetically
                                sort($availableBrands);

                                // Log available brands for debugging
                                Log::info('Available brands from Digiflazz:', $availableBrands);

                                // Get games from database that match available brands
                                $games = Game::where('is_active', true)->get();
                                $options = [];

                                foreach ($games as $game) {
                                    $gameBrand = trim(strtoupper($game->brand));
                                    if (in_array($gameBrand, $availableBrands)) {
                                        $options[$game->id] = $game->name . ' (' . $game->brand . ')';
                                    }
                                }

                                // Log games and their brands for debugging
                                Log::info('Games in database:', $games->pluck('name', 'brand')->toArray());
                                Log::info('Matching games:', $options);

                                if (empty($options)) {
                                    return ['error' => 'No games found that match available Digiflazz brands. Available brands: ' . implode(', ', array_slice($availableBrands, 0, 10))];
                                }

                                return $options;

                            } catch (\Exception $e) {
                                Log::error('Error getting Digiflazz games:', ['error' => $e->getMessage()]);
                                return ['error' => 'Error: ' . $e->getMessage()];
                            }
                        })
                        ->required()
                        ->helperText('Select a game to get products from Digiflazz')
                        ->reactive(),

                    Forms\Components\Select::make('product_type')
                        ->label('Product Type')
                        ->options(function (callable $get) {
                            $gameId = $get('game_id');
                            if (!$gameId || is_string($gameId) && str_contains($gameId, 'error')) {
                                return [];
                            }

                            $game = Game::find($gameId);
                            if (!$game) {
                                return [];
                            }

                            // Get credentials from settings
                            $username = Setting::getValue('digiflazz_username');
                            $apiKey = Setting::getValue('digiflazz_api_key');

                            if (!$username || !$apiKey) {
                                return ['error' => 'Please fill in Digiflazz Username & API Key in Settings'];
                            }

                            $gameCodeOnProvider = $game->brand;
                            $signature = md5($username . $apiKey . 'pricelist');

                            try {
                                // Add longer delay to avoid rate limiting
                                usleep(2000000); // 2 second delay

                                $response = Http::timeout(30)
                                    ->retry(3, 2000) // Increased retry delay
                                    ->post('https://api.digiflazz.com/v1/price-list', [
                                        'cmd' => 'prepaid',
                                        'username' => $username,
                                        'sign' => $signature,
                                    ]);

                                if ($response->failed()) {
                                    return ['error' => 'Cannot connect to Digiflazz API. Status: ' . $response->status()];
                                }

                                $responseData = $response->json();

                                // Check for rate limit error
                                if (isset($responseData['rc']) && $responseData['rc'] === '83') {
                                    return ['error' => 'Digiflazz API rate limit reached. Please wait a few minutes and try again.'];
                                }

                                if (isset($responseData['message']) && str_contains($responseData['message'], 'limitasi')) {
                                    return ['error' => 'Digiflazz API rate limit reached. Please wait a few minutes and try again.'];
                                }

                                // Debug: Log response for troubleshooting
                                Log::info('Digiflazz API Response for types:', [
                                    'game_brand' => $gameCodeOnProvider,
                                    'response_status' => $response->status(),
                                    'data_count' => isset($responseData['data']) ? count($responseData['data']) : 0,
                                    'first_few_products' => array_slice($responseData['data'] ?? [], 0, 3)
                                ]);

                                // Validate response structure
                                if (!isset($responseData['data']) || !is_array($responseData['data'])) {
                                    return ['error' => 'Invalid API response format. Response: ' . json_encode($responseData)];
                                }

                                $productsFromApi = $responseData['data'];
                                $types = [];

                                // Get all types available for this brand (using ProductRelationManager logic)
                                $allBrands = [];
                                $allTypes = [];
                                
                                foreach ($productsFromApi as $index => $productData) {
                                    // Log each product for debugging
                                    Log::info('Processing product ' . $index . ':', [
                                        'product' => $productData,
                                        'has_brand' => isset($productData['brand']),
                                        'has_type' => isset($productData['type']),
                                        'brand_value' => $productData['brand'] ?? 'NOT_SET',
                                        'type_value' => $productData['type'] ?? 'NOT_SET'
                                    ]);

                                    // Validate product data structure
                                    if (!is_array($productData)) {
                                        Log::info('Product is not an array, skipping');
                                        continue;
                                    }

                                    if (!isset($productData['brand'])) {
                                        Log::info('Product missing brand field, skipping');
                                        continue;
                                    }

                                    if (!isset($productData['type'])) {
                                        Log::info('Product missing type field, skipping');
                                        continue;
                                    }

                                    // Collect all brands and types for debugging
                                    $apiBrand = trim(strtoupper($productData['brand']));
                                    $apiType = trim($productData['type']); // Keep original case for types
                                    
                                    if (!in_array($apiBrand, $allBrands)) {
                                        $allBrands[] = $apiBrand;
                                    }
                                    
                                    if (!in_array($apiType, $allTypes)) {
                                        $allTypes[] = $apiType;
                                    }

                                    // Compare brand flexibly (case insensitive, trim whitespace)
                                    $searchBrand = trim(strtoupper($gameCodeOnProvider));

                                    Log::info('Comparing brands:', [
                                        'api_brand' => $apiBrand,
                                        'search_brand' => $searchBrand,
                                        'matches' => $apiBrand === $searchBrand
                                    ]);

                                    if ($apiBrand === $searchBrand) {
                                        if (!in_array($apiType, $types)) {
                                            $types[] = $apiType;
                                            Log::info('Added new type:', ['type' => $apiType]);
                                        }
                                    }
                                }
                                
                                // Log all available brands and types for debugging
                                Log::info('All brands in API response:', $allBrands);
                                Log::info('All types in API response:', $allTypes);

                                // Log types found for debugging
                                Log::info('Types found for brand ' . $gameCodeOnProvider . ': ' . implode(', ', $types));
                                Log::info('Total products processed: ' . count($productsFromApi));
                                Log::info('Total types found: ' . count($types));

                                // Sort types alphabetically
                                sort($types);

                                // Build options array
                                $options = [];
                                foreach ($types as $type) {
                                    $options[$type] = $type;
                                }

                                // Add "ALL" option at the beginning
                                $options = ['ALL' => 'All Types'] + $options;

                                // If no types found and it's Mobile Legends, provide fallback types
                                if (empty($types) && strtoupper($gameCodeOnProvider) === 'MOBILE LEGENDS') {
                                    Log::info('No types found, using fallback for Mobile Legends');
                                    $fallbackTypes = [
                                        'ALL' => 'All Types',
                                        'Umum' => 'Umum',
                                        'Membership' => 'Membership',
                                        'Malaysia' => 'Malaysia',
                                        'Global' => 'Global',
                                        'Filipina' => 'Filipina',
                                        'Brazil' => 'Brazil'
                                    ];
                                    return $fallbackTypes;
                                }

                                return $options;

                            } catch (\Exception $e) {
                                Log::error('Error getting Digiflazz types:', ['error' => $e->getMessage()]);
                                return ['error' => 'Error: ' . $e->getMessage()];
                            }
                        })
                        ->default('ALL')
                        ->required()
                        ->helperText('Select product type to sync from Digiflazz')
                        ->reactive()
                        ->visible(fn (callable $get) => $get('game_id') && !is_string($get('game_id')) || !str_contains($get('game_id'), 'error')),
                ])
                ->action(function (array $data) {
                    // Validate if there's an error when getting game
                    if (!isset($data['game_id']) || !is_numeric($data['game_id'])) {
                        Notification::make()->title('Failed!')->body('Cannot get game data from Digiflazz. Please try again.')->danger()->send();
                        return;
                    }

                    // Get credentials from settings
                    $username = Setting::getValue('digiflazz_username');
                    $apiKey = Setting::getValue('digiflazz_api_key');
                    $game = Game::find($data['game_id']);

                    if (!$username || !$apiKey) {
                        Notification::make()->title('Failed!')->body('Please fill in Digiflazz Username & API Key in Settings.')->danger()->send();
                        return;
                    }

                    if (!$game) {
                        Notification::make()->title('Failed!')->body('Selected game not found.')->danger()->send();
                        return;
                    }

                    $gameCodeOnProvider = $game->brand;
                    $signature = md5($username . $apiKey . 'pricelist');

                    try {
                        // Add longer delay to avoid rate limiting
                        usleep(2000000); // 2 second delay

                        $response = Http::timeout(30)
                            ->retry(3, 2000) // Increased retry delay
                            ->post('https://api.digiflazz.com/v1/price-list', [
                                'cmd' => 'prepaid',
                                'username' => $username,
                                'sign' => $signature,
                            ]);

                        if ($response->failed()) {
                            Notification::make()->title('Failed!')->body('Cannot connect to Digiflazz API. Status: ' . $response->status())->danger()->send();
                            return;
                        }

                        $responseData = $response->json();

                        // Check for rate limit error
                        if (isset($responseData['rc']) && $responseData['rc'] === '83') {
                            Notification::make()->title('Rate Limit Reached!')->body('Digiflazz API rate limit reached. Please wait a few minutes and try again.')->warning()->send();
                            return;
                        }

                        if (isset($responseData['message']) && str_contains($responseData['message'], 'limitasi')) {
                            Notification::make()->title('Rate Limit Reached!')->body('Digiflazz API rate limit reached. Please wait a few minutes and try again.')->warning()->send();
                            return;
                        }

                        // Validate response structure
                        if (!isset($responseData['data']) || !is_array($responseData['data'])) {
                            Notification::make()->title('Failed!')->body('Invalid API response format.')->danger()->send();
                            return;
                        }

                        $productsFromApi = $responseData['data'];
                        $syncedCount = 0;
                        $defaultProductCategoryId = $game->productCategories()->first()?->id;

                        if (!$defaultProductCategoryId) {
                            Notification::make()->title('Failed!')->body("Game {$game->name} does not have Product Categories. Please add them first.")->warning()->send();
                            return;
                        }

                        $selectedType = $data['product_type'] ?? 'ALL';

                        // Filter and sync based on selected type (using ProductRelationManager logic)
                        foreach ($productsFromApi as $productData) {
                            // Validate product data structure
                            if (!is_array($productData) || !isset($productData['brand']) || !isset($productData['type']) ||
                                !isset($productData['buyer_sku_code']) || !isset($productData['product_name']) ||
                                !isset($productData['price']) || !isset($productData['seller_product_status'])) {
                                continue; // Skip if data is not valid
                            }

                            $productType = $productData['type']; // Keep original case

                            // Compare brand flexibly (case insensitive, trim whitespace)
                            $apiBrand = trim(strtoupper($productData['brand']));
                            $searchBrand = trim(strtoupper($gameCodeOnProvider));

                            // Filter based on brand and selected type
                            if ($apiBrand === $searchBrand &&
                                ($selectedType === 'ALL' || $productType === $selectedType)) {
                                Product::updateOrCreate(
                                    [
                                        'provider' => 'digiflazz',
                                        'provider_sku' => $productData['buyer_sku_code'],
                                    ],
                                    [
                                        'name' => $productData['product_name'],
                                        'base_price' => round($productData['price']),
                                        'is_active' => $productData['seller_product_status'],
                                        'game_id' => $game->id,
                                        'product_category_id' => $defaultProductCategoryId,
                                    ]
                                );
                                $syncedCount++;
                            }
                        }

                        $typeLabel = $selectedType === 'ALL' ? 'all types' : "type {$selectedType}";
                        Notification::make()
                            ->title('Sync Successful!')
                            ->body("{$syncedCount} Digiflazz products with {$typeLabel} for {$game->name} have been updated.")
                            ->success()
                            ->send();

                    } catch (\Exception $e) {
                        Notification::make()->title('Error!')->body($e->getMessage())->danger()->send();
                    }
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ProductStatsWidget::class,
        ];
    }
} 