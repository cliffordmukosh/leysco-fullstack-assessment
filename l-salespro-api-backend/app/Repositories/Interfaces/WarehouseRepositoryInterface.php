<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface WarehouseRepositoryInterface
{
    public function getAll(): Collection;

    public function findWithInventory($id): ?Model;
}