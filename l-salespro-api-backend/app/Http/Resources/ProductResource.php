<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'category' => $this->whenLoaded('category', fn() => $this->category->name),
            'subcategory' => $this->subcategory,
            'description' => $this->description,
            'price' => $this->price,
            'tax_rate' => $this->tax_rate,
            'unit' => $this->unit,
            'packaging' => $this->packaging,
            'min_order_quantity' => $this->min_order_quantity,
            'reorder_level' => $this->reorder_level,
            'total_stock' => $this->whenLoaded('inventory', fn() => $this->inventory->sum('quantity')),
            'created_at' => $this->created_at,
        ];
    }
}