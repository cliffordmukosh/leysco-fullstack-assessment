<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class FilterProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'min_price' => 'nullable|numeric',
            'max_price' => 'nullable|numeric',
            'stock_status' => 'nullable|in:in_stock,low_stock,out_of_stock',
            'sort_by' => 'nullable|in:name,price,sku,stock',
            'sort_direction' => 'nullable|in:asc,desc',
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
        ];
    }
}