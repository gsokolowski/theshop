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
            'products' => Product::with('category', 'brand', 'color', 'size')->latest()->get()
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
        // if validated
        if ($request->validated()) {
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
            // product slug
            $data['slug'] = Str::slug($data['name']);
            
            // Create the product with the data
            $product = Product::create($data);
            
            // pivot table for colors sync the colors to the product if color is selected
            if ($request->has('color_id')) {
                $product->colors()->sync($data['color_id']);
            }
            // pivot table for sizes sync the sizes to the product if size is selected
            if ($request->has('size_id')) {
                $product->sizes()->sync($data['size_id']);
            }

            // Redirect to the index page with a success message
            return redirect()->route('admin.products.index')
                ->with('success', 'Product created successfully');

        }
        // Redirect to the create page with a error message
        return redirect()->route('admin.products.create')
            ->with('error', 'Product creation failed');
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

        // if validated
        if ($request->validated()) {
            // if product is not found, return 404
            if (!$product) {
                abort(404);
            }
            // get the validated data
            $data = $request->validated();
            // if thumbnail is uploaded, upload and save the thumbnail image
            if ($request->hasFile('thumbnail')) {
                // delete the old thumbnail image
                Storage::disk('public')->delete($product->thumbnail);
                $data['thumbnail'] = $this->uploadImage($request, 'thumbnail');
            }
            // if first image is uploaded, upload and save the first image
            if ($request->hasFile('first_image')) {
                // delete the old first image
                Storage::disk('public')->delete($product->first_image);
                $data['first_image'] = $this->uploadImage($request, 'first_image');
            }
            // if second image is uploaded, upload and save the second image
            if ($request->hasFile('second_image')) {
                // delete the old second image
                Storage::disk('public')->delete($product->second_image);
                $data['second_image'] = $this->uploadImage($request, 'second_image');
            }
            // if third image is uploaded, upload and save the third image
            if ($request->hasFile('third_image')) {
                // delete the old third image
                Storage::disk('public')->delete($product->third_image);
                $data['third_image'] = $this->uploadImage($request, 'third_image');
            }
            // product slug
            $data['slug'] = Str::slug($data['name']);
            // update status
            $data['status'] = $request->status; //1 for active, 0 for inactive
            // update the product with the data
            $product->update($data);
            // pivot table for colors sync the colors to the product if color is selected
            if ($request->has('color_id')) {
                $product->colors()->sync($data['color_id']);
            }
            // pivot table for sizes sync the sizes to the product if size is selected
            if ($request->has('size_id')) {
                $product->sizes()->sync($data['size_id']);
            }
            // Redirect to the index page with a success message
            return redirect()->route('admin.products.index')
                ->with('success', 'Product updated successfully');
        }

        // Redirect to the edit page with a error message
        return redirect()->route('admin.products.edit', $product->id)
            ->with('error', 'Product update failed');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {

        // delete the product images
        Storage::disk('public')->delete($product->thumbnail);
        Storage::disk('public')->delete($product->first_image);
        Storage::disk('public')->delete($product->second_image);
        Storage::disk('public')->delete($product->third_image);

        // Delete the product
        $product->delete();
        // Redirect to the index page with a success message
        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully');
    }


    /**
     * Upload and save product images to the product
     */
    public function uploadImage(Request $request, $type)
    {
        // Validate the request
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        // Generate a unique name for the image
        $image_name = time().'_'.$type.'_'.$request->file('image')->getClientOriginalName();
        // Get the extension of the image
        $image_extension = $request->file('image')->getClientOriginalExtension();
        // Upload and save the image to the storage/app/public/images/products
        $path = $request->file('image')->storeAs('images/products', $image_name.'.'.$image_extension, 'public'); // path like: storage/app/public/images/products/image_name.extension
        // Returns: "images/products/1234567890_thumbnail_product_name.jpg" which is the path to the image
        return $path;
    }   
}
