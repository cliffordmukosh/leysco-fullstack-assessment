<?php

namespace App\Repositories\Interfaces;

interface OrderItemRepositoryInterface
{
    public function createMany(int $orderId, array $items): void;

    public function getForOrder(int $orderId);

    public function deleteForOrder(int $orderId): void;
}