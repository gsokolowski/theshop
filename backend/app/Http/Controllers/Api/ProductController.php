<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Color;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    //create index method to return all products with their categories, brands, colors, sizes but use ProductResource to format the response
    // http://127.0.0.1:8000/api/products
    public function index()
    {
        $products = ProductResource::collection(
            Product::with('category', 'brand', 'colors', 'sizes') // eager load the relationships
            ->latest()
            ->get())
            ->additional([
                'categories' => Cache::remember('categories', 3600, fn() => Category::latest()->get()),
                'brands' => Cache::remember('brands', 3600, fn() => Brand::latest()->get()),
                'colors' => Cache::remember('colors', 3600, fn() => Color::latest()->get()),
                'sizes' => Cache::remember('sizes', 3600, fn() => Size::latest()->get())
            ]);
        
        return $products; 
    }

    // Get Products by slug but use ProductResource to format the response and eager load the relationships
    // http://127.0.0.1:8000/api/products/{product}
    public function show(Product $product)
    {
        // check if the product exists
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        // $product is already a Product instance from Route Model Binding
        // So we use ->load() to add relationships to this existing instance not with() because it will create a new instance and we don't want to create a new instance
        return new ProductResource(
            $product->load('category', 'brand', 'colors', 'sizes', 'reviews')
        );
    }

    // Filter Product by catergory
    public function filterByCategory(Category $category)
    {
        $products = ProductResource::collection(
            Product::with('category', 'brand', 'colors', 'sizes') // eager load the relationships
            ->where('category_id', $category->id)
            ->latest()
            ->get())
            ->additional([ // additional data to the response
                'categories' => Cache::remember('categories', 3600, fn() => Category::latest()->get()),
                'brands' => Cache::remember('brands', 3600, fn() => Brand::latest()->get()),
                'colors' => Cache::remember('colors', 3600, fn() => Color::latest()->get()),
                'sizes' => Cache::remember('sizes', 3600, fn() => Size::latest()->get()),
                'filter' => $category->name
            ]);
        return $products;
    }

    // Filter Product by brand
    public function filterByBrand(Brand $brand)
    {
        $products = ProductResource::collection(
            Product::with('category', 'brand', 'colors', 'sizes') // eager load the relationships
            ->where('brand_id', $brand->id)
            ->latest()
            ->get())
            ->additional([ // additional data to the response
                'categories' => Cache::remember('categories', 3600, fn() => Category::latest()->get()),
                'brands' => Cache::remember('brands', 3600, fn() => Brand::latest()->get()),
                'colors' => Cache::remember('colors', 3600, fn() => Color::latest()->get()),
                'sizes' => Cache::remember('sizes', 3600, fn() => Size::latest()->get()),
                'filter' => $brand->name
            ]);
        return $products;
    }

    // Filter Product by color
    public function filterByColor(Color $color)
    {
        $products = ProductResource::collection(
            Product::with('category', 'brand', 'colors', 'sizes') // eager load the relationships
            ->where('color_id', $color->id)
            ->latest()
            ->get())
            ->additional([ // additional data to the response
                'categories' => Cache::remember('categories', 3600, fn() => Category::latest()->get()),
                'brands' => Cache::remember('brands', 3600, fn() => Brand::latest()->get()),
                'colors' => Cache::remember('colors', 3600, fn() => Color::latest()->get()),
                'sizes' => Cache::remember('sizes', 3600, fn() => Size::latest()->get()),
                'filter' => $color->name
            ]);
        return $products;
    }

    // Filter Product by size
    public function filterBySize(Size $size)
    {
        $products = ProductResource::collection(
            Product::with('category', 'brand', 'colors', 'sizes') // eager load the relationships
            ->where('size_id', $size->id)
            ->latest()
            ->get())
            ->additional([ // additional data to the response
                'categories' => Cache::remember('categories', 3600, fn() => Category::latest()->get()),
                'brands' => Cache::remember('brands', 3600, fn() => Brand::latest()->get()),
                'colors' => Cache::remember('colors', 3600, fn() => Color::latest()->get()),
                'sizes' => Cache::remember('sizes', 3600, fn() => Size::latest()->get()),
                'filter' => $size->name
            ]);
        return $products;
    }

    // Filter Product by searchTern
    public function filterBySearchTerm(Request $request)
    {
        $products = ProductResource::collection(
            Product::with('category', 'brand', 'colors', 'sizes') // eager load the relationships
            ->where('name', 'like', '%' . $request->searchTerm . '%')
            ->latest()
            ->get())
            ->additional([ // additional data to the response
                'categories' => Cache::remember('categories', 3600, fn() => Category::latest()->get()), 
                'brands' => Cache::remember('brands', 3600, fn() => Brand::latest()->get()),
                'colors' => Cache::remember('colors', 3600, fn() => Color::latest()->get()),
                'sizes' => Cache::remember('sizes', 3600, fn() => Size::latest()->get())
            ]);
        return $products;
    }
}
