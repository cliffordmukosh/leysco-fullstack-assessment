<?php

namespace App\Repositories\Eloquent;

use App\Models\OrderItem;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;

class OrderItemRepository implements OrderItemRepositoryInterface
{
    protected $model;

    public function __construct(OrderItem $model)
    {
        $this->model = $model;
    }

    public function createMany(int $orderId, array $items): void
    {
        $prepared = array_map(function ($item) use ($orderId) {
            return [
                'order_id' => $orderId,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount_type' => $item['discount_type'] ?? null,
                'discount_value' => $item['discount_value'] ?? 0,
                'discount_amount' => $item['discount_amount'] ?? 0,
                'tax_amount' => $item['tax_amount'] ?? 0,
                'subtotal' => $item['subtotal'] ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $items);

        $this->model->insert($prepared);
    }

    public function getForOrder(int $orderId)
    {
        return $this->model->with('product')
            ->where('order_id', $orderId)
            ->get();
    }

    public function deleteForOrder(int $orderId): void
    {
        $this->model->where('order_id', $orderId)->delete();
    }
}