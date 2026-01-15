<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SizeStoreRequest;
use App\Http\Requests\SizeUpdateRequest;
use App\Models\Size;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //Display a list of sizes
        return view('admin.sizes.index', [
            'sizes' => Size::latest()->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //Create a new size
        return view('admin.sizes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SizeStoreRequest $request)
    {
        // Validation already happened automatically via FormRequest
        // If we reach here, validation passed
        // Create a new size
        $data = $request->validated();
        Size::create($data);
        // Redirect to the index page with a success message
        return redirect()->route('admin.sizes.index')->with('success', 'Size created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Size $size)
    {
        // dont show the show view for a size  
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Size $size)
    {
        // return view with form for editing the size
        return view('admin.sizes.edit', [
            'size' => $size
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SizeUpdateRequest $request, Size $size)
    {
        // Validation already happened automatically via FormRequest
        // If we reach here, validation passed
        // Update the size
        $data = $request->validated();
        $size->update($data);
        // Redirect to the index page with a success message
        return redirect()->route('admin.sizes.index')
            ->with('success', 'Size updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Size $size)
    {
        // Delete the size
        $size->delete();
        // Redirect to the index page with a success message
        return redirect()->route('admin.sizes.index')
            ->with('success', 'Size deleted successfully');
    }
}
