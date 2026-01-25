<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Color;
use App\Models\Size;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $brands = Brand::all();
        $colors = Color::all();
        $sizes = Size::all();

        // Create specific products
        $specificProducts = [
            [
                'name' => 'Classic White T-Shirt',
                'qty' => 50,
                'price' => 29.99,
                'description' => 'Comfortable cotton t-shirt perfect for everyday wear. Made from 100% organic cotton.',
                'status' => 1,
            ],
            [
                'name' => 'Nike Air Max Running Shoes',
                'qty' => 30,
                'price' => 129.99,
                'description' => 'Premium running shoes with air cushioning technology for maximum comfort.',
                'status' => 1,
            ],
            [
                'name' => 'Adidas Sport Jacket',
                'qty' => 25,
                'price' => 79.99,
                'description' => 'Lightweight sports jacket perfect for active lifestyle. Water-resistant and breathable.',
                'status' => 1,
            ],
            [
                'name' => 'Puma Athletic Shorts',
                'qty' => 40,
                'price' => 34.99,
                'description' => 'Comfortable athletic shorts with moisture-wicking fabric.',
                'status' => 1,
            ],
            [
                'name' => 'Designer Leather Handbag',
                'qty' => 15,
                'price' => 199.99,
                'description' => 'Elegant leather handbag perfect for special occasions. Handcrafted with premium materials.',
                'status' => 1,
            ],
        ];

        foreach ($specificProducts as $productData) {
            $product = Product::create([
                'name' => $productData['name'],
                'slug' => Str::slug($productData['name']),
                'qty' => $productData['qty'],
                'price' => $productData['price'],
                'description' => $productData['description'],
                'status' => $productData['status'],
                'category_id' => $categories->random()->id,
                'brand_id' => $brands->random()->id,
                'thumbnail' => null,
                'first_image' => null,
                'second_image' => null,
                'third_image' => null,
            ]);

            // Attach random colors and sizes
            $product->colors()->attach(
                $colors->random(rand(2, 4))->pluck('id')->toArray()
            );
            $product->sizes()->attach(
                $sizes->random(rand(3, 5))->pluck('id')->toArray()
            );
        }

        // ✅ CHANGED: Create products using factory but override category_id and brand_id to use existing ones
        $randomProducts = Product::factory(15)->inStock()->create([
            'category_id' => fn() => $categories->random()->id,
            'brand_id' => fn() => $brands->random()->id,
        ]);
        
        foreach ($randomProducts as $product) {
            // Attach random colors and sizes to factory-created products
            $product->colors()->attach(
                $colors->random(rand(2, 4))->pluck('id')->toArray()
            );
            $product->sizes()->attach(
                $sizes->random(rand(3, 5))->pluck('id')->toArray()
            );
        }
    }
}