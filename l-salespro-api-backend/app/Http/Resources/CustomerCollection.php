<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CustomerCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($customer) {
                return new CustomerResource($customer);
            }),
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}