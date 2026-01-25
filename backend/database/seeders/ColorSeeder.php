<?php

namespace Database\Seeders;

use App\Models\Color;
use Illuminate\Database\Seeder;

class ColorSeeder extends Seeder
{
    public function run(): void
    {
        $colors = ['Black', 'White', 'Red', 'Blue', 'Green'];

        foreach ($colors as $color) {
            Color::create(['name' => $color]);
        }
    }
}