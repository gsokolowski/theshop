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
            'thumbnail' => asset($this->thumbnail), // get the full url of the thumbnail
            'first_image' => $this->first_image ? asset($this->first_image) : null, // get the full url of the first image
            'second_image' => $this->second_image ? asset($this->second_image) : null, // get the full url of the second image
            'third_image' => $this->third_image ? asset($this->third_image) : null, // get the full url of the third image
            'status' => $this->status,
            'category' => $this->category,// get the category name
            'brand' => $this->brand,// get the brand name
            'colors' => $this->colors,// get the colors
            'sizes' => $this->sizes,// get the sizes
            'reviews' => $this->reviews,// get the reviews
        ];
    }
}
