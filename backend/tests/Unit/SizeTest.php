<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Size;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for Size model.
 */
class SizeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Size has products relationship.
     */
    public function test_size_has_products_relationship(): void
    {
        $size = Size::factory()->create();
        $product = Product::factory()->create();

        $size->products()->attach($product->id);

        $this->assertCount(1, $size->products);
        $this->assertTrue($size->products->first()->is($product));
    }

    /**
     * Size has fillable name attribute.
     */
    public function test_size_has_fillable_name(): void
    {
        $size = Size::factory()->create(['name' => 'Large']);

        $this->assertSame('Large', $size->name);
    }
}
