<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // RBAC handled in route middleware
    }

    public function rules(): array
    {
        return [
            'sku' => 'required|string|unique:products,sku',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'subcategory' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'unit' => 'required|string|max:50',
            'packaging' => 'required|string|max:100',
            'min_order_quantity' => 'required|integer|min:1',
            'reorder_level' => 'required|integer|min:0',
        ];
    }
}