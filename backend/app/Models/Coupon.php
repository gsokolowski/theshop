<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class Coupon extends Model
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'discount',
        'valid_until',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'valid_until' => 'datetime:Y-m-d H:i',
    ];

    // Convert coupon code to uppercase
    public function setCodeAttribute($value)
    {
        $this->attributes['name'] = strtoupper($value);
    }

    // Check if coupon is valid
    public function isValid()
    {
        // If coupon is valid until is greater than current date, return true
        if ($this->valid_until > Carbon::now()->format('Y-m-d H:i')) {
            return true;
        }
        return false;
    }

    // Coupon belongs to one order
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
