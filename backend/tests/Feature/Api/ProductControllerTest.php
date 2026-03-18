<?php

namespace Tests\Feature\Api;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for Api\ProductController. Public endpoints for index, show, filterByCategory,
 * filterByBrand, filterByColor, filterBySize, filterBySearchTerm.
 */
class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Index returns all products with categories, brands, colors, sizes.
     */
    public function test_index_returns_products_with_metadata(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson(route('products.index'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'slug', 'price', 'category', 'brand'],
            ],
            'categories',
            'brands',
            'colors',
            'sizes',
        ]);
    }

    /**
     * Show returns single product with relationships.
     */
    public function test_show_returns_product_by_slug(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson(route('products.show', $product->slug));

        $response->assertStatus(200);
        $response->assertJsonPath('data.name', $product->name);
    }

    /**
     * filterByCategory returns products in the given category.
     */
    public function test_filter_by_category_returns_matching_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(2)->create(['category_id' => $category->id]);
        Product::factory()->create();

        $response = $this->getJson(route('products.filter.category', $category->slug));

        $response->assertStatus(200);
        $response->assertJsonPath('filter', $category->name);
        $this->assertCount(2, $response->json('data'));
    }

    /**
     * filterByBrand returns products for the given brand.
     */
    public function test_filter_by_brand_returns_matching_products(): void
    {
        $brand = Brand::factory()->create();
        Product::factory()->count(2)->create(['brand_id' => $brand->id]);
        Product::factory()->create();

        $response = $this->getJson(route('products.filter.brand', $brand->slug));

        $response->assertStatus(200);
        $response->assertJsonPath('filter', $brand->name);
        $this->assertCount(2, $response->json('data'));
    }

    /**
     * filterByColor returns products that have the given color.
     */
    public function test_filter_by_color_returns_matching_products(): void
    {
        $color = Color::factory()->create();
        $product1 = Product::factory()->create();
        $product1->colors()->attach($color->id);
        $product2 = Product::factory()->create();
        $product2->colors()->attach($color->id);

        $response = $this->getJson(route('products.filter.color', $color));

        $response->assertStatus(200);
        $response->assertJsonPath('filter', $color->name);
        $this->assertCount(2, $response->json('data'));
    }

    /**
     * filterBySize returns products that have the given size.
     */
    public function test_filter_by_size_returns_matching_products(): void
    {
        $size = Size::factory()->create();
        $product1 = Product::factory()->create();
        $product1->sizes()->attach($size->id);
        $product2 = Product::factory()->create();
        $product2->sizes()->attach($size->id);

        $response = $this->getJson(route('products.filter.size', $size));

        $response->assertStatus(200);
        $response->assertJsonPath('filter', $size->name);
        $this->assertCount(2, $response->json('data'));
    }

    /**
     * filterBySearchTerm returns products matching name or description.
     */
    public function test_filter_by_search_term_returns_matching_products(): void
    {
        Product::factory()->create(['name' => 'Blue Widget']);
        Product::factory()->create(['name' => 'Red Gadget']);
        Product::factory()->create(['description' => 'A blue widget alternative']);

        $response = $this->getJson(route('products.filter.searchTerm', 'blue'));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(2, count($data));
    }

    /**
     * filterBySearchTerm returns empty array when no matches.
     */
    public function test_filter_by_search_term_returns_empty_when_no_matches(): void
    {
        Product::factory()->create(['name' => 'Widget']);

        $response = $this->getJson(route('products.filter.searchTerm', 'nonexistentxyz'));

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }
}
