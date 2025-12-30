<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TopProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'product_id'       => $this->product_id,
            'sku'              => $this->product?->sku ?? 'N/A',
            'name'             => $this->product?->name ?? 'Unknown Product',
            'category'         => $this->product?->category?->name ?? null,
            
            'total_quantity_sold' => (int) $this->total_quantity,
            'total_revenue'       => round($this->total_revenue ?? 0, 2),
            'average_unit_price'  => $this->total_quantity > 0 
                ? round($this->total_revenue / $this->total_quantity, 2) 
                : 0,

            // Optional: useful for charts/visuals
            'rank' => $this->when(isset($this->rank), $this->rank),
        ];
    }
}