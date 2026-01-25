<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['name' => 'Chanel', 'slug' => 'chanel'],
            ['name' => 'Louis Vuitton', 'slug' => 'louis-vuitton'],
            ['name' => 'Gucci', 'slug' => 'gucci'],
            ['name' => 'Prada', 'slug' => 'prada'],
            ['name' => 'Zara', 'slug' => 'zara'],
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }
    }
}