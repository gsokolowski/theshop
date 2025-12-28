<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'address',
        'city',
        'country',
        'zip_code',
        'phone_number',
        'profile_image',
        'profile_completed',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Use profile image for user avatar
    protected $appends = ['profile_image_url'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // User orders relationship
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->with('products')->orderBy('id', 'desc');
    }

    // User reviews relationship
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->with('product')->orderBy('id', 'desc');
    }

    // Get profile image url
    public function getProfileImageUrlAttribute()
    {
        if ($this->profile_image) {
            return asset('storage/' . $this->profile_image);
        }
        // If no profile image, return default avatar
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=ffffff&background=111827';
    }
}
