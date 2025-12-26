<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    // fillable fields
    protected $fillable = [
        'name',
        'slug',
        'qty',
        'price',
        'description',
        'thumbnail',
        'first_image',
        'second_image',
        'third_image',
        'status',
        'category_id',
        'brand_id'
    ];

    // use slug for product url
    public function getRouteKeyName()
    {
        return 'slug';
    }

    // Product belongs to category
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Product belongs to brand
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    // Product belongs to many colors via pivot table color_product
    public function colors(): BelongsToMany
    {
        return $this->belongsToMany(Color::class, 'color_product');
    }

    // Product belongs to many sizes via pivot table product_size
    public function sizes(): BelongsToMany
    {
        return $this->belongsToMany(Size::class, 'product_size');
    }

    // Product belongs to many orders
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class);
    }

    // Product has many reviews
    public function reviews(): HasMany
    {
        // get reviews with user eager loading, where approved is true and order by latest review   
        return $this->hasMany(Review::class)
                    ->with('user')
                    ->where('approved', 1)
                    ->orderBy('id', 'desc');
    }
}
