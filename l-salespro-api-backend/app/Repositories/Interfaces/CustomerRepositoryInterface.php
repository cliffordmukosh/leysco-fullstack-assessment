<?php

namespace App\Repositories\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Customer;

interface CustomerRepositoryInterface
{
    public function getFiltered(array $filters): LengthAwarePaginator;

    public function find($id): ?Customer;

    public function create(array $data): Customer;

    public function update($id, array $data): Customer;

    public function delete($id): void;

    public function getOrders($id): LengthAwarePaginator;

    public function getCreditStatus($id): array;

    public function getMapData(): array;
}