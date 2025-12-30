<?php

namespace App\Repositories\Eloquent;

use App\Models\Warehouse;
use App\Repositories\Interfaces\WarehouseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class WarehouseRepository implements WarehouseRepositoryInterface
{
    protected $model;

    public function __construct(Warehouse $model)
    {
        $this->model = $model;
    }

    public function getAll(): Collection
    {
        return $this->model->withCount('inventory')->get();
    }

    public function findWithInventory($id): ?Model
    {
        return $this->model->with(['inventory.product'])->find($id);
    }
}