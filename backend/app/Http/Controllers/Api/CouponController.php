<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    // url: http://127.0.0.1:8000/api/coupon/{name} as route parameter
    // Example: http://127.0.0.1:8000/api/coupon/TESTCOUPON
    public function getCouponByName(string $name)
    {
        $coupon = Coupon::where('name', $name)->first();
        if (! $coupon || ! $coupon->isValid()) {
            return response()->json([
                'error' => 'Invalid or expired coupon'
            ], 404);
        }
        // return the coupon resource with success message
        return response()->json([
            'message' => 'Coupon applied successfully',
            'data' => new CouponResource($coupon)
        ], 200);    }
}
