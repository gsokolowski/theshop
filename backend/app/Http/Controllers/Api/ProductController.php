<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListProductsRequest;
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
    /**
     * Facet lists for the shop sidebar. Loaded fresh each request so empty caches cannot hide
     * categories/brands/colors after seeding (sizes were already uncached in index).
     */
    private function productFilterSidebarMetadata(): array
    {
        return [
            'categories' => Category::latest()->get(),
            'brands' => Brand::latest()->get(),
            'colors' => Color::latest()->get(),
            'sizes' => Size::orderBy('id', 'asc')->get(),
        ];
    }

    //create index method to return all products with their categories, brands, colors, sizes but use ProductResource to format the response
    // http://127.0.0.1:8000/api/v1/products?page=1&per_page=4
    // ✅ CHANGED: optional query filters (AND): category, brand (slugs), color_id, size_id, search — combined with ListProductsRequest
    // ✅ CHANGED: cache paginated product list in Redis (CACHE_STORE); key includes filters/page; busted via Product model version
    public function index(ListProductsRequest $request)
    {
        $perPage = min((int) $request->input('per_page', 4), 50);
        $page = max(1, (int) $request->input('page', 1));
        $validated = $request->validated();

        $query = Product::with('category', 'brand', 'colors', 'sizes');

        if (! empty($validated['category'] ?? null)) {
            $query->where('category_id', Category::where('slug', $validated['category'])->value('id'));
        }
        if (! empty($validated['brand'] ?? null)) {
            $query->where('brand_id', Brand::where('slug', $validated['brand'])->value('id'));
        }
        if (! empty($validated['color_id'] ?? null)) {
            $colorId = $validated['color_id'];
            $query->whereHas('colors', function ($q) use ($colorId) {
                $q->where('colors.id', $colorId);
            });
        }
        if (! empty($validated['size_id'] ?? null)) {
            $sizeId = $validated['size_id'];
            $query->whereHas('sizes', function ($q) use ($sizeId) {
                $q->where('sizes.id', $sizeId);
            });
        }
        if (! empty($validated['search'] ?? null)) {
            $term = $validated['search'];
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', '%'.$term.'%')
                    ->orWhere('description', 'like', '%'.$term.'%');
            });
        }

        // ✅ ADDED: Redis-backed list cache (version bumps when products change)
        $version = (int) Cache::get(Product::LIST_CACHE_VERSION_KEY, 1);
        $cacheKey = sprintf(
            'products.list.v%d.%s',
            $version,
            md5(json_encode([
                'filters' => $validated,
                'per_page' => $perPage,
                'page' => $page,
            ]))
        );

        // ✅ CHANGED: tie-break on id so pagination is stable when created_at matches (avoids duplicate rows across pages on MySQL)
        $paginator = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($query, $perPage, $page) {
            return $query->latest()
                ->orderByDesc('id')
                ->paginate($perPage, ['*'], 'page', $page);
        });

        $products = ProductResource::collection($paginator)
            ->additional($this->productFilterSidebarMetadata());

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
    // http://127.0.0.1:8000/api/v1/products/category/women?page=1&per_page=4
    public function filterByCategory(Request $request, Category $category)
    {
        $perPage = min((int) $request->get('per_page', 4), 50);
        $products = ProductResource::collection(
            Product::with('category', 'brand', 'colors', 'sizes')
                ->where('category_id', $category->id)
                ->latest()
                ->orderByDesc('id')
                ->paginate($perPage)
        )->additional(array_merge($this->productFilterSidebarMetadata(), [
            'filter' => $category->name,
        ]));
        return $products;
    }

    // Filter Product by brand
    // http://127.0.0.1:8000/api/v1/products/brand/addidas?page=1&per_page=4
    public function filterByBrand(Request $request, Brand $brand)
    {
        $perPage = min((int) $request->get('per_page', 4), 50);
        $products = ProductResource::collection(
            Product::with('category', 'brand', 'colors', 'sizes')
                ->where('brand_id', $brand->id)
                ->latest()
                ->orderByDesc('id')
                ->paginate($perPage)
        )->additional(array_merge($this->productFilterSidebarMetadata(), [
            'filter' => $brand->name,
        ]));
        return $products;
    }

    // Filter Product by color
    // http://127.0.0.1:8000/api/v1/products/color/1?page=1&per_page=4
    public function filterByColor(Request $request, Color $color)
    {
        $perPage = min((int) $request->get('per_page', 4), 50);
        $products = ProductResource::collection(
            Product::with('category', 'brand', 'colors', 'sizes')
                ->whereHas('colors', function($query) use ($color) {
                    $query->where('colors.id', $color->id);
                })
                ->latest()
                ->orderByDesc('id')
                ->paginate($perPage)
        )->additional(array_merge($this->productFilterSidebarMetadata(), [
            'filter' => $color->name,
        ]));
        return $products;
    }

    // Filter Product by size
    // http://127.0.0.1:8000/api/v1/products/size/1?page=1&per_page=4
    public function filterBySize(Request $request, Size $size)
    {
        $perPage = min((int) $request->get('per_page', 4), 50);
        $products = ProductResource::collection(
            Product::with('category', 'brand', 'colors', 'sizes')
                ->whereHas('sizes', function($query) use ($size) {
                    $query->where('sizes.id', $size->id);
                })
                ->latest()
                ->orderByDesc('id')
                ->paginate($perPage)
        )->additional(array_merge($this->productFilterSidebarMetadata(), [
            'filter' => $size->name,
        ]));
        return $products;
    }

    // Filter Product by searchTerm route with query parameter searchTerm
    // url: http://127.0.0.1:8000/api/v1/products/search/{searchTerm}?page=1&per_page=4
    public function filterBySearchTerm(Request $request, $searchTerm)
    {
        if (!$searchTerm) {
            return response()->json(['message' => 'searchTerm parameter is required'], 400);
        }

        $perPage = min((int) $request->get('per_page', 4), 50);
        $products = ProductResource::collection(
            Product::with('category', 'brand', 'colors', 'sizes')
                ->where('name', 'like', '%' . $searchTerm . '%')
                ->orWhere('description', 'like', '%' . $searchTerm . '%')
                ->latest()
                ->orderByDesc('id')
                ->paginate($perPage)
        )->additional($this->productFilterSidebarMetadata());
        return $products;
    }
}
