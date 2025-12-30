<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

interface InventoryRepositoryInterface
{
    /**
     * Get total physical stock (sum of quantity) for a product across all warehouses
     */
    public function getTotalStock(int $productId): int;

    /**
     * Find inventory record for a specific product in a specific warehouse
     */
    public function findForWarehouse(int $productId, int $warehouseId): ?Model;

    /**
     * Get all inventory records for a product with warehouse relation
     */
    public function getForProduct(int $productId): Collection;

    /**
     * Calculate total AVAILABLE stock (quantity - reserved_quantity) across all warehouses
     */
    public function getAvailableStock(int $productId): int;

    /**
     * Update inventory record (useful for reserve/release operations)
     */
    public function updateInventory(int $productId, int $warehouseId, array $changes): bool;
}