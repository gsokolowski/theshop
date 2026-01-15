<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // return view with all latest categories sorted by created_at in descending order
        return view('admin.categories.index', [
            'categories' => Category::latest()->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Show the form for creating a new category
        return view('admin.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryStoreRequest $request)
    {
        // Validation already happened automatically via FormRequest
        // If we reach here, validation passed
        // Create a new category
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);
        Category::create($data);
        // Redirect to the index page with a success message
        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        // dont show the show view for a category
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        // Show the form for editing category use with() to pass the category
        return view('admin.categories.edit', [
            'category' => $category
        ]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryUpdateRequest $request, Category $category)
    {
        // Validation already happened automatically via FormRequest
        // If we reach here, validation passed
        // Update the category
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);
        $category->update($data);
        // Redirect to the index page with a success message
        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully');                
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // Delete the category
        $category->delete();
        // Redirect to the index page with a success message
        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully');
    }
}
