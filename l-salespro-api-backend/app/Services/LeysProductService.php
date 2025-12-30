<?php

namespace App\Services;

use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\InventoryRepositoryInterface;
use App\Jobs\SendLowStockAlert;
use App\Models\StockReservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;


class LeysProductService
{
    protected $productRepo;
    protected $inventoryRepo;

    public function __construct(
        ProductRepositoryInterface $productRepo,
        InventoryRepositoryInterface $inventoryRepo
    ) {
        $this->productRepo = $productRepo;
        $this->inventoryRepo = $inventoryRepo;
    }

    public function getAll(array $filters)
    {
        $cacheKey = 'products_list_' . md5(json_encode($filters));

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($filters) {
            return $this->productRepo->getFiltered($filters);
        });
    }

    public function getById($id)
    {
        $product = $this->productRepo->findWithRelations($id, ['category', 'inventory.warehouse']);

        if (!$product) {
            throw ValidationException::withMessages(['id' => 'Product not found']);
        }

        return $product;
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $product = $this->productRepo->create($data);
            return $product;
        });
    }

    public function update($id, array $data)
    {
        $product = $this->productRepo->find($id);

        if (!$product) {
            throw ValidationException::withMessages(['id' => 'Product not found']);
        }

        return DB::transaction(function () use ($product, $data) {
            $product->update($data);

            $totalAvailable = $this->inventoryRepo->getAvailableStock($product->id);

            if ($totalAvailable < $product->reorder_level) {
                SendLowStockAlert::dispatch($product);
            }

            return $product->fresh(['category', 'inventory.warehouse']);
        });
    }

    public function delete($id)
    {
        $product = $this->productRepo->find($id);

        if (!$product) {
            throw ValidationException::withMessages(['id' => 'Product not found']);
        }

        $product->delete();
    }

    public function getStock($productId)
    {
        $cacheKey = "product_stock_{$productId}";

        return Cache::remember($cacheKey, now()->addSeconds(60), function () use ($productId) {
            $product = $this->productRepo->findWithRelations($productId, ['inventory.warehouse']);

            if (!$product) {
                throw ValidationException::withMessages(['id' => 'Product not found']);
            }

            $activeReservations = DB::table('stock_reservations')
                ->where('product_id', $productId)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->select('warehouse_id', DB::raw('SUM(quantity) as reserved'))
                ->groupBy('warehouse_id')
                ->pluck('reserved', 'warehouse_id')
                ->toArray();

            $warehouses = $product->inventory->map(function ($inv) use ($activeReservations) {
                $reserved = $activeReservations[$inv->warehouse_id] ?? 0;
                return [
                    'warehouse_id'       => $inv->warehouse_id,
                    'warehouse_code'     => $inv->warehouse->code,
                    'warehouse_name'     => $inv->warehouse->name,
                    'total_quantity'     => $inv->quantity,
                    'reserved_quantity'  => $reserved,
                    'available_quantity' => max(0, $inv->quantity - $reserved),
                ];
            });

            $totalAvailable = $warehouses->sum('available_quantity');

            return [
                'product_id'            => $product->id,
                'sku'                   => $product->sku,
                'name'                  => $product->name,
                'total_available_stock' => $totalAvailable,
                'warehouses'            => $warehouses->toArray(),
                'reorder_level'         => $product->reorder_level,
                'low_stock_alert'       => $totalAvailable < $product->reorder_level,
            ];
        });
    }

    public function reserve($productId, array $data)
    {
        return DB::transaction(function () use ($productId, $data) {
            $inventory = $this->inventoryRepo->findForWarehouse($productId, $data['warehouse_id']);

            if (!$inventory) {
                throw ValidationException::withMessages([
                    'warehouse_id' => 'No inventory record for this product in selected warehouse'
                ]);
            }

            $available = $inventory->quantity - ($inventory->reserved_quantity ?? 0);

            if ($available < $data['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => "Insufficient stock. Only {$available} units available in this warehouse."
                ]);
            }

            $inventory->decrement('quantity', $data['quantity']);
            $inventory->increment('reserved_quantity', $data['quantity']);

            $reservation = StockReservation::create([
                'product_id'   => $productId,
                'order_id'     => $data['order_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'],
                'quantity'     => $data['quantity'],
                'expires_at'   => now()->addMinutes(30),
                'status'       => 'active',
            ]);

            Cache::forget("product_stock_{$productId}");

            return $reservation;
        });
    }

    public function release($productId, array $data)
    {
        return DB::transaction(function () use ($productId, $data) {
            $reservation = StockReservation::where('id', $data['reservation_id'])
                ->where('product_id', $productId)
                ->where('status', 'active')
                ->firstOrFail();

            $inventory = $this->inventoryRepo->findForWarehouse($productId, $reservation->warehouse_id);

            if (!$inventory) {
                throw new \Exception('Inventory record missing for this reservation');
            }

            $inventory->increment('quantity', $reservation->quantity);
            $inventory->decrement('reserved_quantity', $reservation->quantity);

            $reservation->update([
                'status' => 'released',
                'released_at' => now(),
            ]);

            Cache::forget("product_stock_{$productId}");

            return $reservation;
        });
    }

    public function getLowStock()
    {
        $result = $this->productRepo->getLowStock();

        return $result;
    }

}