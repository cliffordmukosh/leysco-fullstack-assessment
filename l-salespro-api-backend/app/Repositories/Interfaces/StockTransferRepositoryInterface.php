<?php

namespace App\Repositories\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;

interface StockTransferRepositoryInterface
{
    public function create(array $data);

    public function getPaginatedHistory(array $filters): LengthAwarePaginator;
}