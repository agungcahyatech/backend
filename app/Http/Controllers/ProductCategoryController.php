<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductCategory;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::where('is_active', true)
            ->orderBy('display_order', 'asc')
            ->get();
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
} 