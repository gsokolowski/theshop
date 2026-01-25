<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::create([
            'name' => 'WELCOME10',
            'discount' => 10,
            'valid_until' => Carbon::now()->addMonths(3),
        ]);

        Coupon::create([
            'name' => 'SAVE20',
            'discount' => 20,
            'valid_until' => Carbon::now()->addMonths(6),
        ]);

        Coupon::create([
            'name' => 'SUMMER50',
            'discount' => 50,
            'valid_until' => Carbon::now()->addDays(30), // Expires soon
        ]);
    }
}