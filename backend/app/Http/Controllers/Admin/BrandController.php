<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BrandStoreRequest;
use App\Http\Requests\BrandUpdateRequest;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // return view with all latest brands sorted by created_at in descending order
        return view('admin.brands.index', [
            'brands' => Brand::latest()->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Show the form for creating a new brand
        return view('admin.brands.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BrandStoreRequest $request)
    {
        // Validation already happened automatically via FormRequest
        // If we reach here, validation passed
        
        // Create a new brand
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);
        Brand::create($data);
        
        // Redirect to the index page with a success message
        return redirect()->route('admin.brands.index')->with('success', 'Brand created successfully');

    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        // dont show the show view for a brand
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brand $brand)
    {
        // Show the form for editing the specified brand
        return view('admin.brands.edit', [
            'brand' => $brand
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BrandUpdateRequest $request, Brand $brand)
    {
        // Validation already happened automatically via FormRequest
        // If we reach here, validation passed
        // Update the brand
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);
        $brand->update($data);
        // Redirect to the index page with a success message
        return redirect()->route('admin.brands.index')->with('success', 'Brand updated successfully');

        // Redirect to the edit page with a error message
        return redirect()->route('admin.brands.edit', $brand->slug)
            ->with('error', 'Brand update failed');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        // Delete the brand
        $brand->delete();
        // Redirect to the index page with a success message
        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand deleted successfully');
    }
}
