<?php

namespace App\Repositories\Eloquent;

use App\Models\Inventory;
use App\Repositories\Interfaces\InventoryRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class InventoryRepository implements InventoryRepositoryInterface
{
    protected $model;

    public function __construct(Inventory $model)
    {
        $this->model = $model;
    }

    public function getTotalStock(int $productId): int
    {
        return (int) $this->model
            ->where('product_id', $productId)
            ->sum('quantity');
    }

    public function findForWarehouse(int $productId, int $warehouseId): ?Model
    {
        return $this->model
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();
    }

    public function getForProduct(int $productId): Collection
    {
        return $this->model
            ->where('product_id', $productId)
            ->with('warehouse')
            ->get();
    }

    public function getAvailableStock(int $productId): int
    {
        return (int) $this->model
            ->where('product_id', $productId)
            ->sum(DB::raw('GREATEST(0, quantity - COALESCE(reserved_quantity, 0))'));
    }

    public function updateInventory(int $productId, int $warehouseId, array $changes): bool
    {
        $inventory = $this->findForWarehouse($productId, $warehouseId);

        if (!$inventory) {
            return false;
        }

        return $inventory->update($changes);
    }
}