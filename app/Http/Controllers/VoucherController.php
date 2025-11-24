<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;
use Carbon\Carbon;

class VoucherController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $vouchers = Voucher::where('is_active', true)
            ->where('end_date', '>=', $now)
            ->orderBy('end_date', 'asc')
            ->get();
        return response()->json([
            'success' => true,
            'data' => $vouchers
        ]);
    }
} 