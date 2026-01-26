<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Change rating from integer to decimal(3,1) to allow values like 1.5, 2.5, etc.
            // decimal(3,1) means: 3 total digits, 1 decimal place (e.g., 5.0, 4.5, 1.5)
            $table->decimal('rating', 3, 1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Revert back to integer
            $table->integer('rating')->change();
        });
    }
};