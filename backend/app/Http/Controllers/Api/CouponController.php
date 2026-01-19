<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    // get coupon by name and return the coupon resource
    // url: http://127.0.0.1:8000/api/coupon?name=TESTCOUPON
    public function getCouponByName(Request $request)
    {
        $coupon = Coupon::where('name', $request->query('name'))->first();
        if (! $coupon || ! $coupon->isValid()) {
            return response()->json(['message' => 'Coupon not found or is not valid'], 404);
        }
        // return the coupon resource
        return new CouponResource($coupon);
    }
}
