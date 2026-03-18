<?php

namespace Tests\Unit;

use App\Models\Color;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for Color model.
 */
class ColorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Color has products relationship.
     */
    public function test_color_has_products_relationship(): void
    {
        $color = Color::factory()->create();
        $product = Product::factory()->create();

        $color->products()->attach($product->id);

        $this->assertCount(1, $color->products);
        $this->assertTrue($color->products->first()->is($product));
    }

    /**
     * Color has fillable name attribute.
     */
    public function test_color_has_fillable_name(): void
    {
        $color = Color::factory()->create(['name' => 'Red']);

        $this->assertSame('Red', $color->name);
    }
}
