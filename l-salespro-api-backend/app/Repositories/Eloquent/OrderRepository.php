<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Helpers\LeyscoHelpers;

class OrderRepository implements OrderRepositoryInterface
{
    protected $model;

    public function __construct(Order $model)
    {
        $this->model = $model;
    }

    public function getFiltered(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['customer', 'user', 'items.product']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->latest()->paginate($perPage);
    }

    public function find($id): ?Order
    {
        return $this->model->with(['customer', 'items.product'])->find($id);
    }

    public function create(array $data): Order
    {
        return $this->model->create($data);
    }

    public function updateStatus($id, string $status): Order
    {
        $order = $this->find($id);
        $order->update(['status' => $status]);
        return $order->fresh();
    }

    public function generateInvoiceData($id): array
    {
        $order = $this->find($id);
        if (!$order) return [];

        return [
            'order_number' => $order->order_number,
            'customer' => $order->customer->name,
            'date' => $order->created_at->format('Y-m-d'),
            'items' => $order->items->map(fn($item) => [
                'product' => $item->product->name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'subtotal' => $item->subtotal,
            ]),
            'subtotal' => $order->subtotal,
            'discount' => $order->discount_amount,
            'tax' => $order->tax_amount,
            'total' => $order->total_amount,
        ];
    }

    public function calculateTotal(array $items, ?array $orderDiscount): array
    {
        $subtotal = 0;
        $tax = 0;

        foreach ($items as $item) {
            $price = $item['unit_price'] * $item['quantity'];
            $itemDiscount = 0;

            if (!empty($item['discount_value'])) {
                if ($item['discount_type'] === 'percentage') {
                    $itemDiscount = $price * ($item['discount_value'] / 100);
                } else {
                    $itemDiscount = $item['discount_value'];
                }
            }

            $itemSubtotal = $price - $itemDiscount;
            $subtotal += $itemSubtotal;
            $tax += LeyscoHelpers::calculateTax($itemSubtotal, $item['tax_rate'] ?? 16);
        }

        $orderDiscountAmount = 0;
        if ($orderDiscount && !empty($orderDiscount['value'])) {
            if ($orderDiscount['type'] === 'percentage') {
                $orderDiscountAmount = $subtotal * ($orderDiscount['value'] / 100);
            } else {
                $orderDiscountAmount = $orderDiscount['value'];
            }
        }

        $finalSubtotal = $subtotal - $orderDiscountAmount;
        $finalTax = LeyscoHelpers::calculateTax($finalSubtotal, 16);
        $total = $finalSubtotal + $finalTax;

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $orderDiscountAmount,
            'tax_amount' => $finalTax,
            'total_amount' => $total,
        ];
    }
}