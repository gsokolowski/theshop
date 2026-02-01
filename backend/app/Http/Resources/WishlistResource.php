<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WishlistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Determine status badge based on product status
        $statusBadge = [
            'value' => $this->product->status,
            'label' => $this->product->status === 1 ? 'Available' : 'Out of Stock',
            'class' => $this->product->status === 1 ? 'success' : 'warning',
        ];

        return [
            'id' => $this->id,
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'slug' => $this->product->slug,
                'thumbnail' => $this->product->thumbnail ? asset('storage/' . $this->product->thumbnail) : null,
                'price' => $this->product->price,
                'qty' => $this->product->qty,
                'status' => $this->product->status,
                'status_badge' => $statusBadge,
                'category' => [
                    'id' => $this->product->category->id,
                    'name' => $this->product->category->name,
                    'slug' => $this->product->category->slug,
                ],
                'brand' => [
                    'id' => $this->product->brand->id,
                    'name' => $this->product->brand->name,
                    'slug' => $this->product->brand->slug,
                ],
                'sizes' => $this->product->sizes->map(function ($size) {
                    return [
                        'id' => $size->id,
                        'name' => $size->name,
                    ];
                }),
                'colors' => $this->product->colors->map(function ($color) {
                    return [
                        'id' => $color->id,
                        'name' => $color->name,
                    ];
                }),
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
