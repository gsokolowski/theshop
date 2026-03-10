<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'qty' => $this->qty,
            'total' => $this->total,
            'created_at' => $this->getRawOriginal('created_at'),
            'deliverd_at' => $this->getRawOriginal('deliverd_at'),
            'products' => $this->products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $product->price,
                    'description' => $product->description,
                    'thumbnail' => $product->thumbnail ? asset('storage/' . $product->thumbnail) : null,
                    'pivot' => [
                        'color_id' => $product->pivot->color_id ?? null,
                        'size_id' => $product->pivot->size_id ?? null,
                    ],
                    'colors' => $product->relationLoaded('colors') ? $product->colors : [],
                    'sizes' => $product->relationLoaded('sizes') ? $product->sizes : [],
                ];
            }),
            'coupon' => $this->whenLoaded('coupon', function () {
                if (!$this->coupon) {
                    return null;
                }
                return [
                    'id' => $this->coupon->id,
                    'name' => $this->coupon->name,
                    'discount' => $this->coupon->discount,
                ];
            }),
        ];
    }
}
