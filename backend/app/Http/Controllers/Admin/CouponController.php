<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CouponStoreRequest;
use App\Http\Requests\CouponUpdateRequest;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // return view with all latest coupons sorted by created_at in descending order
        return view('admin.coupons.index', [
            'coupons' => Coupon::latest()->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Show the form for creating a new coupon
        return view('admin.coupons.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CouponStoreRequest $request)
    {
        // Validation already happened automatically via FormRequest
        // If we reach here, validation passed
        // Create a new coupon
        $data = $request->validated();
        Coupon::create($data);
        // Redirect to the index page with a success message
        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Coupon $coupon)
    {
        // dont show the show view for a coupon
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Coupon $coupon)
    {
        // Show the form for editing coupon use with() to pass the coupon
        return view('admin.coupons.edit', [
            'coupon' => $coupon
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CouponUpdateRequest $request, Coupon $coupon)
    {
        // Validation already happened automatically via FormRequest
        // If we reach here, validation passed
        // Update the coupon
        $data = $request->validated();
        $coupon->update($data);
        // Redirect to the index page with a success message
        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon updated successfully'); 
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Coupon $coupon)
    {
        // Delete the coupon
        $coupon->delete();
        // Redirect to the index page with a success message
        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon deleted successfully');
    }
}
