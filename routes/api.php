<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\PopupController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\GameConfigurationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\FlashSaleController;

Route::prefix('v1')->group(function () {
    Route::get('settings', [SettingsController::class, 'index']);
    Route::get('settings/social-media', [SettingsController::class, 'socialMedia']);
    Route::get('sliders', [SliderController::class, 'index']);
    Route::get('pages', [PageController::class, 'index']);
    Route::get('articles', [ArticleController::class, 'index']);
    Route::get('popups', [PopupController::class, 'index']);
    Route::get('payment-methods', [PaymentMethodController::class, 'index']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{slug}', [CategoryController::class, 'show']);
    Route::get('games', [GameController::class, 'index']);
    Route::get('games/list', [GameController::class, 'list']);
    Route::get('games/search', [GameController::class, 'search']);
    Route::get('games/search/suggestions', [GameController::class, 'searchSuggestions']);
    Route::get('games/{slug}', [GameController::class, 'show']);
    Route::get('games/{slug}/products', [GameController::class, 'products']);
    Route::get('games/{slug}/configuration-info', [GameController::class, 'configurationInfo']);
    Route::get('games/{slug}/configuration-fields', [GameController::class, 'configurationFields']);
    Route::get('game-configurations', [GameConfigurationController::class, 'index']);
    Route::get('products', [ProductController::class, 'index']);
    Route::get('product-categories', [ProductCategoryController::class, 'index']);
    Route::get('vouchers', [VoucherController::class, 'index']);
    Route::get('flash-sales', [FlashSaleController::class, 'index']);
    Route::get('flash-sales/upcoming', [FlashSaleController::class, 'upcoming']);
    Route::get('flash-sales/{id}', [FlashSaleController::class, 'show']);
}); 