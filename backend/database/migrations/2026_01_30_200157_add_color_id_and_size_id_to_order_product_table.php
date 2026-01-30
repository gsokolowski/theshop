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
        Schema::table('order_product', function (Blueprint $table) {
            // ✅ ADDED: Add color_id column with foreign key constraint
            $table->foreignId('color_id')->nullable()->after('product_id')->constrained('colors')->onDelete('cascade');
            // ✅ ADDED: Add size_id column with foreign key constraint
            $table->foreignId('size_id')->nullable()->after('color_id')->constrained('sizes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_product', function (Blueprint $table) {
            // ✅ REMOVED: Drop foreign key constraints and columns
            $table->dropForeign(['color_id']);
            $table->dropForeign(['size_id']);
            $table->dropColumn(['color_id', 'size_id']);
        });
    }
};
