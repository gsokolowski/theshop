<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'zip_code' => $this->zip_code,
            'phone_number' => $this->phone_number,
            'profile_image' => $this->profile_image_url, //get the profile image url from the user model
            'profile_completed' => $this->profile_completed,
            'orders' => $this->orders,
            'reviews' => $this->reviews,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }   

}
