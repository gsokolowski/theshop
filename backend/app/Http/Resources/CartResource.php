<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
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
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'slug' => $this->product->slug,
                'price' => $this->product->price,
                'qty' => $this->product->qty,
                'status' => $this->product->status,
                'thumbnail' => $this->product->thumbnail ? asset('storage/' . $this->product->thumbnail) : null,
            ],
            'color' => [
                'id' => $this->color->id,
                'name' => $this->color->name,
            ],
            'size' => [
                'id' => $this->size->id,
                'name' => $this->size->name,
            ],
            'quantity' => $this->quantity,
            'reference' => "{$this->product->id}-{$this->color->id}-{$this->size->id}",
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
