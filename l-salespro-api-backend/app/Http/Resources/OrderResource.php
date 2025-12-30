<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
public function toArray(Request $request): array
{
    return [
        'id'             => $this->id,
        'order_number'   => $this->order_number,
        'customer'       => [
            'id'   => $this->customer->id,
            'name' => $this->customer->name,
        ],
        'user'           => [
            'id'       => $this->user->id,
            'username' => $this->user->username,
        ],
        'status'         => $this->status,
        'subtotal'       => (string) $this->subtotal,
        'discount_amount'=> (string) $this->discount_amount,
        'tax_amount'     => (string) $this->tax_amount,
        'total_amount'   => (string) $this->total_amount,
        'notes'          => $this->notes ?? '',
        'created_at'     => $this->created_at?->format('Y-m-d H:i:s'),
        'items'          => $this->items->map(function ($item) {
            return [
                'product_id'     => $item->product_id,
                'product_name'   => $item->product->name ?? 'Unknown',
                'quantity'       => (int) $item->quantity,
                'unit_price'     => (string) $item->unit_price,
                'discount_type'  => $item->discount_type,       // keep null if null
                'discount_value' => (string) $item->discount_value,
                'discount_amount'=> (string) $item->discount_amount,
                'tax_amount'     => (string) $item->tax_amount,
                'subtotal'       => (string) $item->subtotal,
            ];
        }),
    ];
}

}
