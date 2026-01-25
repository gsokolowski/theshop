<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    //Fillable fields
    protected $fillable = [
        'title',
        'body',
        'rating',
        'approved',
        'user_id',
        'product_id',
    ];

    // Review belongs to user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Review belongs to product
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Make created at date in readable human format
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->diffForHumans();
    }
}
