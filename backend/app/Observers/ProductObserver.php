<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    /**
     * Bust cached product list keys after create or update.
     */
    public function saved(Product $product): void
    {
        Product::bumpListCacheVersion();
    }

    /**
     * Bust cached product list keys after delete.
     */
    public function deleted(Product $product): void
    {
        Product::bumpListCacheVersion();
    }
}
