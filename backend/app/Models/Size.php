<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Size extends Model
{
    //Fillable fields
    protected $fillable = [
        'name',
    ];

    // Size belongs to many products
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }
}
