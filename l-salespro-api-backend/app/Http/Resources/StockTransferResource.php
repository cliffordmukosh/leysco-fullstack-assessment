<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockTransferResource extends JsonResource
{
public function toArray(Request $request): array
{
    return [
        'id'              => $this->id,
        'from_warehouse'  => optional($this->fromWarehouse)->name ?? 'N/A',
        'to_warehouse'    => optional($this->toWarehouse)->name ?? 'N/A',
        'product'         => optional($this->product)->name ?? 'N/A',
        'quantity'        => $this->quantity,
        'status'          => $this->status,
        'notes'           => $this->notes,
        'transferred_by'  => $this->user->username ?? 'System',
        'created_at'      => $this->created_at->toDateTimeString(),
    ];
}

}