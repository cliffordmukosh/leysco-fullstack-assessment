<?php

namespace App\Repositories\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Order;

interface OrderRepositoryInterface
{
    public function getFiltered(array $filters): LengthAwarePaginator;

    public function find($id): ?Order;

    public function create(array $data): Order;

    public function updateStatus($id, string $status): Order;

    public function generateInvoiceData($id): array;

    public function calculateTotal(array $items, ?array $orderDiscount): array;
}