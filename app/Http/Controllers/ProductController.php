<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::where('is_active', true)
            ->orderBy('display_order', 'asc')
            ->get();
        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }
} 