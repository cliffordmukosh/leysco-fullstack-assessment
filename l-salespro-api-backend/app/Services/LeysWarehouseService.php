<?php

namespace App\Services;

use App\Repositories\Interfaces\WarehouseRepositoryInterface;
use App\Repositories\Interfaces\StockTransferRepositoryInterface;
use App\Repositories\Interfaces\InventoryRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;

class LeysWarehouseService
{
    protected $warehouseRepo;
    protected $stockTransferRepo;
    protected $inventoryRepo;

    public function __construct(
        WarehouseRepositoryInterface $warehouseRepo,
        StockTransferRepositoryInterface $stockTransferRepo,
        InventoryRepositoryInterface $inventoryRepo
    ) {
        $this->warehouseRepo     = $warehouseRepo;
        $this->stockTransferRepo = $stockTransferRepo;
        $this->inventoryRepo     = $inventoryRepo;
    }

    public function getAllWarehouses()
    {
        return Cache::remember('warehouses_list', now()->addMinutes(60), function () {
            return $this->warehouseRepo->getAll();
        });
    }

    public function getWarehouseInventory($warehouseId)
    {
        $cacheKey = "warehouse_{$warehouseId}_inventory";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($warehouseId) {
            $warehouse = $this->warehouseRepo->findWithInventory($warehouseId);

            if (!$warehouse) {
                throw ValidationException::withMessages(['id' => 'Warehouse not found']);
            }

            $totalUsed = $warehouse->inventory->sum('quantity');

            return [
                'warehouse' => [
                    'id'      => $warehouse->id,
                    'code'    => $warehouse->code,
                    'name'    => $warehouse->name,
                    'type'    => $warehouse->type,
                    'address' => $warehouse->address,
                ],
                'capacity'  => [
                    'total'      => $warehouse->capacity,
                    'used'       => $totalUsed,
                    'available'  => $warehouse->capacity ? $warehouse->capacity - $totalUsed : null,
                    'percentage' => $warehouse->capacity ? round(($totalUsed / $warehouse->capacity) * 100, 2) : null,
                ],
                'inventory' => $warehouse->inventory->map(fn($inv) => [
                    'product_id'       => $inv->product_id,
                    'product_name'     => $inv->product->name ?? 'N/A',
                    'quantity'         => $inv->quantity,
                    'reserved'         => $inv->reserved_quantity ?? 0,
                    'available'        => ($inv->quantity ?? 0) - ($inv->reserved_quantity ?? 0),
                ]),
            ];
        });
    }

    /**
     * Transfer stock between warehouses
     */
    public function transferStock(array $data, int $userId)
    {
        return DB::transaction(function () use ($data, $userId) {

            $fromId = $data['from_warehouse_id'] ?? null;
            $toId   = $data['to_warehouse_id'];
            $prodId = $data['product_id'];
            $qty    = $data['quantity'];

            // Ensure the product exists
            $product = \App\Models\Product::find($prodId);
            if (!$product) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'product_id' => 'Invalid product for stock transfer',
                ]);
            }

            // Ensure destination warehouse exists
            $toWarehouse = \App\Models\Warehouse::findOrFail($toId);

            // Check destination warehouse capacity
            if ($toWarehouse->capacity) {
                $used = $toWarehouse->inventory->sum('quantity');
                if (($used + $qty) > $toWarehouse->capacity) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'to_warehouse_id' => 'Destination warehouse capacity exceeded',
                    ]);
                }
            }

            // Only handle source warehouse if it's provided
            if ($fromId !== null) {

                if ($fromId === $toId) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'to_warehouse_id' => 'Cannot transfer to the same warehouse',
                    ]);
                }

                $fromInv = $this->inventoryRepo->findForWarehouse($prodId, $fromId);

                if (!$fromInv || $fromInv->quantity < $qty) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'quantity' => 'Insufficient stock in source warehouse',
                    ]);
                }

                $fromInv->decrement('quantity', $qty);
            }

            // Get or create destination inventory
            $toInv = $this->inventoryRepo->findForWarehouse($prodId, $toId);
            if (!$toInv) {
                $toInv = \App\Models\Inventory::create([
                    'product_id'        => $prodId,
                    'warehouse_id'      => $toId,
                    'quantity'          => 0,
                    'reserved_quantity' => 0,
                ]);
            }

            $toInv->increment('quantity', $qty);

            // Create stock transfer record
            $transfer = $this->stockTransferRepo->create([
                'from_warehouse_id' => $fromId,
                'to_warehouse_id'   => $toId,
                'product_id'        => $prodId,
                'quantity'          => $qty,
                'status'            => 'completed',
                'notes'             => $data['notes'] ?? null,
                'user_id'           => $userId,
            ]);

            // Clear cache
            Cache::forget("warehouse_{$toId}_inventory");
            if ($fromId) {
                Cache::forget("warehouse_{$fromId}_inventory");
            }

            return $transfer;
        });
    }


    public function getTransferHistory(array $filters)
    {
        return $this->stockTransferRepo->getPaginatedHistory($filters);
    }
}