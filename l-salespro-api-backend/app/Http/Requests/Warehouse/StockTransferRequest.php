<?php

namespace App\Http\Requests\Warehouse;

use Illuminate\Foundation\Http\FormRequest;

class StockTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

public function rules(): array
{
    return [
        'from_warehouse_id' => [
            'nullable',
            'exists:warehouses,id',
        ],

        'to_warehouse_id' => [
            'required',
            'exists:warehouses,id',
            'different:from_warehouse_id',
        ],

        'product_id' => 'required|exists:products,id',

        'quantity' => 'required|integer|min:1',

        'notes' => 'nullable|string|max:500',
    ];
}

public function withValidator($validator)
{
    $validator->after(function ($validator) {
        $from = $this->input('from_warehouse_id');
        $to   = $this->input('to_warehouse_id');

        if ($from !== null && $from == $to) {
            $validator->errors()->add(
                'to_warehouse_id',
                'Source and destination warehouses must be different.'
            );
        }
    });
}

}