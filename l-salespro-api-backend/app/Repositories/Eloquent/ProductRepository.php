<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    protected $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    public function getFiltered(array $filters): LengthAwarePaginator
    {
        $query = $this->model->query()->with('category', 'inventory.warehouse');

        // Full-text search on name and description
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->whereFullText(['name', 'description'], $search);
            });
        }

        // Category filter
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Warehouse filter
        if (!empty($filters['warehouse_id'])) {
            $query->whereHas('inventory', fn($q) => $q->where('warehouse_id', $filters['warehouse_id']));
        }

        // Price range
        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Stock status
        if (!empty($filters['stock_status'])) {
            $query->whereHas('inventory', function ($q) use ($filters) {
                if ($filters['stock_status'] === 'in_stock') {
                    $q->where('quantity', '>', 0);
                } elseif ($filters['stock_status'] === 'low_stock') {
                    $q->whereRaw('quantity <= reorder_level AND quantity > 0');
                } elseif ($filters['stock_status'] === 'out_of_stock') {
                    $q->where('quantity', 0);
                }
            });
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'name';
        $sortDirection = $filters['sort_direction'] ?? 'asc';
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    public function findWithRelations($id, array $relations = [])
    {
        return $this->model->with($relations)->find($id);
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $product = $this->find($id);
        if ($product) {
            $product->update($data);
            return $product;
        }
        return null;
    }

    public function delete($id)
    {
        $product = $this->find($id);
        if ($product) {
            $product->delete();
        }
    }

    public function getLowStock()
{
    $query = Product::select('products.*')
        ->selectSub(function ($sub) {
            $sub->from('inventory')
                ->selectRaw('SUM(quantity - COALESCE(reserved_quantity, 0))')
                ->whereColumn('inventory.product_id', 'products.id');
        }, 'available_stock')
        ->whereRaw('(select SUM(quantity - COALESCE(reserved_quantity, 0)) from inventory where inventory.product_id = products.id) <= products.reorder_level')
        ->with(['inventory.warehouse', 'category']);
        
    return $query->get();
}



}