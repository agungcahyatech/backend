<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentMethod;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $methods = PaymentMethod::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();
        return response()->json([
            'success' => true,
            'data' => $methods
        ]);
    }
} 