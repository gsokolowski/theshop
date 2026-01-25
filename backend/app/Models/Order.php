<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    use HasFactory;
    // fillable fields
    protected $fillable = [
        'qty',
        'total',
        'deliverd_at',
        'user_id',
        'coupon_id',
    ];

    // Order belongs to many products
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    // Each Order belongs to user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Each Order belongs to coupon
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    // Make created at date in readable human format
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->diffForHumans();
    }

    // Make deliverd at date in readable human format
    public function getDeliverdAtAttribute($value)
    {
        // check if deliverd at is null
        if ($value == null) {
            return null;
        }
        return Carbon::parse($value)->diffForHumans();
    }

}
