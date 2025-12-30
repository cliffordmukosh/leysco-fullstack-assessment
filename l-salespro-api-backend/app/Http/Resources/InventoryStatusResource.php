<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'category_name'   => $this->category_name ?? $this['category_name'],
            'total_products'  => (int) ($this->total_products ?? $this['total_products']),
            'total_stock'     => (int) ($this->total_stock ?? $this['total_stock']),
            
            // Optional - useful for frontend visualization
            'stock_status'    => $this->getStockStatus(),
            'percentage_of_total' => $this->getPercentageOfTotalInventory(),
        ];
    }

    /**
     * Simple helper to classify stock level (can be used by frontend for colors/icons)
     */
    private function getStockStatus(): string
    {
        $stock = (int) ($this->total_stock ?? $this['total_stock']);

        if ($stock === 0) {
            return 'out_of_stock';
        }

        if ($stock <= 50) {
            return 'low';
        }

        if ($stock <= 200) {
            return 'medium';
        }

        return 'healthy';
    }

    /**
     * Calculate approximate percentage of total inventory
     * (requires total inventory to be passed or calculated in service)
     */
    private function getPercentageOfTotalInventory(): ?float
    {
        // If you pass total_inventory through the service, you can calculate here
        $totalInventory = $this->resource->total_inventory ?? null;

        if (!$totalInventory || $totalInventory == 0) {
            return null;
        }

        $stock = (int) ($this->total_stock ?? $this['total_stock']);

        return round(($stock / $totalInventory) * 100, 1);
    }
}