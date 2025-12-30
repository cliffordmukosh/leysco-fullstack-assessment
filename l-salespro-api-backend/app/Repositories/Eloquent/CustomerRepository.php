<?php

namespace App\Repositories\Eloquent;

use App\Models\Customer;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerRepository implements CustomerRepositoryInterface
{
    protected $model;

    public function __construct(Customer $model)
    {
        $this->model = $model;
    }

    public function getFiltered(array $filters): LengthAwarePaginator
    {
        $query = $this->model->query();

        // Add filters (e.g. category, type, territory)
        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['territory'])) {
            $query->where('territory', $filters['territory']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    public function find($id): ?Customer
    {
        return $this->model->find($id);
    }

    public function create(array $data): Customer
    {
        return $this->model->create($data);
    }

    public function update($id, array $data): Customer
    {
        $customer = $this->find($id);
        $customer->update($data);
        return $customer;
    }

    public function delete($id): void
    {
        $customer = $this->find($id);
        $customer->delete();  // Soft delete
    }

    public function getOrders($id): LengthAwarePaginator
    {
        $customer = $this->find($id);
        return $customer->orders()->paginate(15);
    }

    public function getCreditStatus($id): array
    {
        $customer = $this->find($id);
        return [
            'credit_limit' => $customer->credit_limit,
            'current_balance' => $customer->current_balance,
            'available_credit' => $customer->credit_limit - $customer->current_balance,
        ];
    }

    public function getMapData(): array
    {
        return $this->model->select(['id', 'name', 'latitude', 'longitude', 'address'])->get()->toArray();
    }
}