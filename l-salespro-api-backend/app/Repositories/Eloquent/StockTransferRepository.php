<?php

namespace App\Repositories\Eloquent;

use App\Models\StockTransfer;
use App\Repositories\Interfaces\StockTransferRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class StockTransferRepository implements StockTransferRepositoryInterface
{
    protected $model;

    public function __construct(StockTransfer $model)
    {
        $this->model = $model;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function getPaginatedHistory(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['fromWarehouse', 'toWarehouse', 'product', 'user'])
            ->orderBy('created_at', 'desc');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }
}