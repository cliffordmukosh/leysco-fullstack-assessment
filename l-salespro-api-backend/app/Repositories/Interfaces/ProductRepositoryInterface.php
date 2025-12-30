<?php

namespace App\Repositories\Interfaces;

interface ProductRepositoryInterface
{
    public function getFiltered(array $filters);

    public function findWithRelations($id, array $relations = []);

    public function find($id);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);

    public function getLowStock();
}