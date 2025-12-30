<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;  // RBAC in route
    }

    public function rules(): array
    {
        $id = $this->route('id');
        return [
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|max:100',
            'category' => 'sometimes|string|in:A,A+,B,C',
            'contact_person' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:50',
            'email' => 'sometimes|email|unique:customers,email,' . $id,
            'tax_id' => 'sometimes|string|unique:customers,tax_id,' . $id,
            'payment_terms' => 'sometimes|integer|min:0',
            'credit_limit' => 'sometimes|numeric|min:0',
            'current_balance' => 'sometimes|numeric|min:0',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'address' => 'sometimes|string',
            'territory' => 'sometimes|string|max:100',
        ];
    }
}