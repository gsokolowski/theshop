<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Category extends Model
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug'
    ];

    // Brans has many products
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // Change id to slug in the url route
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
