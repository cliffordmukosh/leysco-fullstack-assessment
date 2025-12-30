<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class CalculateTotalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // RBAC handled via route middleware (can:create_orders)
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_type' => 'nullable|in:percentage,fixed',
            'items.*.discount_value' => 'nullable|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount' => 'nullable|array',
            'discount.type' => 'nullable|in:percentage,fixed',
            'discount.value' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Custom messages
     */
    public function messages(): array
    {
        return [
            'items.required' => 'At least one item is required for calculation.',
            'items.min' => 'The calculation must include at least one product.',
            'items.*.product_id.exists' => 'One or more products do not exist.',
        ];
    }
}