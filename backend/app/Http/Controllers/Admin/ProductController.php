<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Support\Facades\Storage; 
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //Display a listing of products
        return view('admin.products.index', [
            'products' => Product::with('category', 'brand', 'colors', 'sizes')->latest()->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //return view with form for creating a new product
        return view('admin.products.create', [
            'categories' => Category::latest()->get(),
            'brands' => Brand::latest()->get(),
            'colors' => Color::latest()->get(),
            'sizes' => Size::latest()->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductStoreRequest $request)
    {
        // Validation already happened automatically via FormRequest
        // If we reach here, validation passed
                
        // get the validated data
        $data = $request->validated();
    
        // if thumbnail is uploaded, upload and save the thumbnail image
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $this->uploadImage($request, 'thumbnail');
        }
        // if first image is uploaded, upload and save the first image
        if ($request->hasFile('first_image')) {
            $data['first_image'] = $this->uploadImage($request, 'first_image');
        }
        // if second image is uploaded, upload and save the second image
        if ($request->hasFile('second_image')) {
            $data['second_image'] = $this->uploadImage($request, 'second_image');
        }
        // if third image is uploaded, upload and save the third image
        if ($request->hasFile('third_image')) {
            $data['third_image'] = $this->uploadImage($request, 'third_image');
        }

        $data['slug'] = Str::slug($data['name']);
        $data['status'] = $request->status;
        
        $colorIds = $data['color_id'] ?? [];
        $sizeIds = $data['size_id'] ?? [];
        // Remove color_id and size_id from data because they're handled via pivot tables
        unset($data['color_id'], $data['size_id']);
        
        // Create the product with the data
        $product = Product::create($data);
        
        // Attach colors to the product if color is selected
        if (!empty($colorIds)) {
            $product->colors()->attach($colorIds);
        }
        // Attach sizes to the product if size is selected
        if (!empty($sizeIds)) {
            $product->sizes()->attach($sizeIds);
        }

        // Redirect to the index page with a success message
        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully');

    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        // show abort 404
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        // return view with form for editing the product
        return view('admin.products.edit', [
            'product' => $product,
            'categories' => Category::latest()->get(),
            'brands' => Brand::latest()->get(),
            'colors' => Color::latest()->get(),
            'sizes' => Size::latest()->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductUpdateRequest $request, Product $product)
    {

        // Route model binding ensures product exists
        
        // get the validated data
        $data = $request->validated();
        // if thumbnail is uploaded, upload and save the thumbnail image
        if ($request->hasFile('thumbnail')) {
            // delete the old thumbnail image if it exists
            if ($product->thumbnail) {
                Storage::disk('public')->delete($product->thumbnail);
            }
            $data['thumbnail'] = $this->uploadImage($request, 'thumbnail');
        }
        // if first image is uploaded, upload and save the first image
        if ($request->hasFile('first_image')) {
            // delete the old first image if it exists
            if ($product->first_image) {
                Storage::disk('public')->delete($product->first_image);
            }
            $data['first_image'] = $this->uploadImage($request, 'first_image');
        }
        // if second image is uploaded, upload and save the second image
        if ($request->hasFile('second_image')) {
            // delete the old second image if it exists
            if ($product->second_image) {
                Storage::disk('public')->delete($product->second_image);
            }
            $data['second_image'] = $this->uploadImage($request, 'second_image');
        }
        // if third image is uploaded, upload and save the third image
        if ($request->hasFile('third_image')) {
            // delete the old third image if it exists
            if ($product->third_image) {
                Storage::disk('public')->delete($product->third_image);
            }
            $data['third_image'] = $this->uploadImage($request, 'third_image');
        }
        
        $data['slug'] = Str::slug($data['name']);        
        $data['status'] = $data['status'] ?? $request->status;
        
        $colorIds = $data['color_id'] ?? [];
        $sizeIds = $data['size_id'] ?? [];
        // Remove color_id and size_id from data because they're handled via pivot tables
        unset($data['color_id'], $data['size_id']);

        // Sync the product with the data
        $product->update($data);

        // Sync colors to the product if color is selected
        if (!empty($colorIds)) {
            $product->colors()->sync($colorIds);
        }
        // Sync sizes to the product if size is selected
        if (!empty($sizeIds)) {
            $product->sizes()->sync($sizeIds);
        }
        // Redirect to the index page with a success message
        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully');
    
    }

    /**
     * Remove a single product image (thumbnail or gallery) from disk and clear the DB column.
     */
    public function destroyProductImage(Request $request, Product $product)
    {
        $field = $request->validate([
            'field' => 'required|in:thumbnail,first_image,second_image,third_image',
        ])['field'];

        $path = $product->{$field};
        if ($path) {
            Storage::disk('public')->delete($path);
            $product->update([$field => null]);
        }

        return redirect()->route('admin.products.edit', $product)
            ->with('success', 'Image removed.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {

        // Delete product images if they exist
        if ($product->thumbnail) {
            Storage::disk('public')->delete($product->thumbnail);
        }
        if ($product->first_image) {
            Storage::disk('public')->delete($product->first_image);
        }
        if ($product->second_image) {
            Storage::disk('public')->delete($product->second_image);
        }
        if ($product->third_image) {
            Storage::disk('public')->delete($product->third_image);
        }

        $product->delete();
        
        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully');
    }


    /**
     * Upload and save product images to the product
     */
    public function uploadImage(Request $request, $fieldName)
    {
        // Validate the request
        $request->validate([
            $fieldName => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $file = $request->file($fieldName);
        $extension = strtolower($file->getClientOriginalExtension());
        $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $slug = Str::slug($baseName);

        if ($slug === '') {
            $slug = 'image';
        }

        // Single extension: original name already includes .ext — do not append extension twice
        $filename = time().'_'.$fieldName.'_'.$slug.'.'.$extension;

        $path = $file->storeAs('images/products', $filename, 'public');

        return $path;
    }   
}
