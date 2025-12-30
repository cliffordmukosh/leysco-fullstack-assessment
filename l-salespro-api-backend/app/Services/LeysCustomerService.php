<?php

namespace App\Services;

use App\Repositories\Interfaces\CustomerRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class LeysCustomerService
{
    protected $repository;

    public function __construct(CustomerRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAll(array $filters)
    {
        $cacheKey = 'customers_list_' . md5(json_encode($filters));

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($filters) {
            return $this->repository->getFiltered($filters);
        });
    }

    public function getById($id)
    {
        $customer = $this->repository->find($id);
        if (!$customer) {
            throw ValidationException::withMessages(['id' => 'Customer not found']);
        }
        return $customer;
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function update($id, array $data)
    {
        $customer = $this->repository->find($id);
        if (!$customer) {
            throw ValidationException::withMessages(['id' => 'Customer not found']);
        }
        return $this->repository->update($id, $data);
    }

    public function delete($id)
    {
        $customer = $this->repository->find($id);
        if (!$customer) {
            throw ValidationException::withMessages(['id' => 'Customer not found']);
        }
        $this->repository->delete($id);
    }

    public function getOrders($id)
    {
        $customer = $this->repository->find($id);
        if (!$customer) {
            throw ValidationException::withMessages(['id' => 'Customer not found']);
        }
        return $this->repository->getOrders($id);
    }

    public function getCreditStatus($id)
    {
        $customer = $this->repository->find($id);
        if (!$customer) {
            throw ValidationException::withMessages(['id' => 'Customer not found']);
        }
        return $this->repository->getCreditStatus($id);
    }

    public function getMapData()
    {
        return Cache::remember('customers_map_data', now()->addMinutes(30), function () {
            return $this->repository->getMapData();
        });
    }
}