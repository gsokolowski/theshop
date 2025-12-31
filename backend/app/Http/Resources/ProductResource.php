<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'slug' => $this->slug,
            'qty' => $this->qty,
            'price' => $this->price,
            'description' => $this->description,
            'thumbnail' => $this->thumbnail ? asset('storage/' . $this->thumbnail) : null,
            'first_image' => $this->first_image ? asset('storage/' . $this->first_image) : null,
            'second_image' => $this->second_image ? asset('storage/' . $this->second_image) : null,
            'third_image' => $this->third_image ? asset('storage/' . $this->third_image) : null,
            'status' => $this->status,
            'category' => $this->category,// get the category name
            'brand' => $this->brand,// get the brand name
            'colors' => $this->colors,// get the colors
            'sizes' => $this->sizes,// get the sizes
            'reviews' => $this->reviews,// get the reviews
        ];
    }
}
