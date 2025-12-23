<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ColorStoreRequest;
use App\Http\Requests\ColorUpdateRequest;
use App\Models\Color;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // return view with all latest colors sorted by created_at in descending order
        return view('admin.colors.index', [
            'colors' => Color::latest()->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // return view with form for creating a new color
        return view('admin.colors.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ColorStoreRequest $request)
    {
        // Validation already happened automatically via FormRequest
        // If we reach here, validation passed
        // Create a new color
        $data = $request->validated();
        Color::create($data);
        // Redirect to the index page with a success message
        return redirect()->route('admin.colors.index')->with('success', 'Color created successfully');

        // Redirect to the create page with a error message
        return redirect()->route('admin.colors.create')->with('error', 'Color creation failed');
    }

    /**
     * Display the specified resource.
     */
    public function show(Color $color)
    {
        // dont show the show view for a color  
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Color $color)
    {
        // return view with form for editing the color
        return view('admin.colors.edit', [
            'color' => $color
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ColorUpdateRequest $request, Color $color)
    {
        // Validation already happened automatically via FormRequest
        // If we reach here, validation passed
        // Update the color
        $data = $request->validated();
        $color->update($data);
        // Redirect to the index page with a success message
        return redirect()->route('admin.colors.index')->with('success', 'Color updated successfully');

        // Redirect to the edit page with a error message
        return redirect()->route('admin.colors.edit', $color->id)
            ->with('error', 'Color update failed');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Color $color)
    {
        // Delete the color
        $color->delete();
        // Redirect to the index page with a success message
        return redirect()->route('admin.colors.index')
            ->with('success', 'Color deleted successfully');

    }
}
