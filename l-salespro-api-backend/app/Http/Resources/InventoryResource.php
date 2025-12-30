<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'warehouse' => $this->warehouse->name,
            'quantity' => $this->quantity,
            'reserved' => $this->reserved_quantity ?? 0,
        ];
    }
}