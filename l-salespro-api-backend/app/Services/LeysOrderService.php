<?php

namespace App\Services;

use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Repositories\Interfaces\InventoryRepositoryInterface;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use App\Jobs\SendOrderConfirmationEmail;
use App\Helpers\LeyscoHelpers;
use App\Services\LeysProductService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;


class LeysOrderService
{
    protected $orderRepo;
    protected $customerRepo;
    protected $inventoryRepo;
    protected $orderItemRepo;
    protected $productService;

    public function __construct(
        OrderRepositoryInterface $orderRepo,
        CustomerRepositoryInterface $customerRepo,
        InventoryRepositoryInterface $inventoryRepo,
        OrderItemRepositoryInterface $orderItemRepo,
        LeysProductService $productService
    ) {
        $this->orderRepo = $orderRepo;
        $this->customerRepo = $customerRepo;
        $this->inventoryRepo = $inventoryRepo;
        $this->orderItemRepo = $orderItemRepo;
        $this->productService = $productService;
    }

    public function getAll(array $filters)
    {
        return $this->orderRepo->getFiltered($filters);
    }

    public function getById($id)
    {
        $order = $this->orderRepo->find($id);
        if (!$order) {
            throw ValidationException::withMessages(['id' => 'Order not found']);
        }
        return $order;
    }

    /**
     * Create a new order
     */
public function create(array $data, int $userId): Order
{
    Log::info('LeysOrderService::create - Starting order creation', [
        'user_id' => $userId,
        'payload' => $data,
    ]);

    return DB::transaction(function () use ($data, $userId) {
        $customer = $this->customerRepo->find($data['customer_id']);
        if (!$customer) {
            throw ValidationException::withMessages(['customer_id' => 'Customer not found']);
        }

        $items = $data['items'] ?? [];
        $orderDiscount = $data['discount'] ?? null;

        // 1. Calculate totals including per-item discounts
        $totals = $this->orderRepo->calculateTotal($items, $orderDiscount);
        Log::info('Calculated order totals', $totals);

        // 2. Check customer credit
        $availableCredit = $customer->credit_limit - $customer->current_balance;
        if ($totals['total_amount'] > $availableCredit) {
            throw ValidationException::withMessages([
                'credit_limit' => 'Order exceeds available credit limit. Available: ' . number_format($availableCredit, 2),
            ]);
        }

        // 3. Check stock
        foreach ($items as $item) {
            $available = $this->inventoryRepo->getAvailableStock($item['product_id']);
            if ($available < $item['quantity']) {
                throw ValidationException::withMessages([
                    'items' => "Insufficient stock for product ID {$item['product_id']}",
                ]);
            }
        }

        // 4. Prepare per-item data with discounts
        $orderItemsData = collect($items)->map(function ($item) {
            $discountAmount = 0;

            if (!empty($item['discount_type']) && isset($item['discount_value'])) {
                if ($item['discount_type'] === 'percentage') {
                    $discountAmount = ($item['unit_price'] * $item['quantity']) * ($item['discount_value'] / 100);
                } else { // fixed
                    $discountAmount = $item['discount_value'];
                }
            }

            return [
                'product_id'      => $item['product_id'],
                'quantity'        => $item['quantity'],
                'unit_price'      => $item['unit_price'],
                'discount_type'   => $item['discount_type'] ?? null,
                'discount_value'  => $item['discount_value'] ?? 0,
                'discount_amount' => $discountAmount,
                'tax_amount'      => 0,
                'subtotal'        => ($item['unit_price'] * $item['quantity']) - $discountAmount,
            ];
        })->toArray();

        // 5. Total order discount amount
        $orderDiscountAmount = collect($orderItemsData)->sum('discount_amount');

        // 6. Generate order number
        $orderNumber = LeyscoHelpers::generateOrderNumber();

        // 7. Create order with **discount fields saved**
        $order = $this->orderRepo->create([
            'order_number'   => $orderNumber,
            'customer_id'    => $data['customer_id'],
            'user_id'        => $userId,
            'status'         => 'Pending',
            'notes'          => $data['notes'] ?? null,
            'discount_type'  => $orderDiscount['type'] ?? null,   // SAVE type
            'discount_value' => $orderDiscount['value'] ?? 0,     // SAVE value
            'discount_amount'=> $orderDiscountAmount,             // SAVE total amount
            'subtotal'       => $totals['subtotal'],
            'tax_amount'     => $totals['tax_amount'],
            'total_amount'   => $totals['total_amount'],
        ]);

        // 8. Save items
        $this->orderItemRepo->createMany($order->id, $orderItemsData);

        // 9. Reserve stock
        foreach ($items as $item) {
            $this->productService->reserve($item['product_id'], [
                'quantity'     => $item['quantity'],
                'warehouse_id' => $data['warehouse_id'] ?? 1,
                'order_id'     => $order->id,
            ]);
        }

        // 10. Queue confirmation email
        SendOrderConfirmationEmail::dispatch($order);
            // 11. Create notification for user 
    Notification::create([
        'user_id' => $userId,
        'type' => 'order_confirmation',
        'title' => 'New Order Created',
        'message' => "Order {$order->order_number} has been created successfully.",
    ]);


            return $order->fresh(['items.product']);
        });
}

    public function updateStatus($id, string $status)
    {
        $order = $this->orderRepo->find($id);
        if (!$order) {
            throw ValidationException::withMessages(['id' => 'Order not found']);
        }

        if (in_array($order->status, ['Shipped', 'Delivered'])) {
            throw ValidationException::withMessages(['status' => 'Cannot update status after shipping']);
        }

        return $this->orderRepo->updateStatus($id, $status);
    }

    public function getInvoiceData($id)
    {
        return $this->orderRepo->generateInvoiceData($id);
    }

    public function calculateTotal(array $data)
    {
        return $this->orderRepo->calculateTotal($data['items'], $data['discount'] ?? null);
    }
}
