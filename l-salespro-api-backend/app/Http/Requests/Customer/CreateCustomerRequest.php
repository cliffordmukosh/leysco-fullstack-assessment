<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class CreateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;  // RBAC in route
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:100',
            'category' => 'nullable|string|in:A,A+,B,C',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|unique:customers,email',
            'tax_id' => 'nullable|string|unique:customers,tax_id',
            'payment_terms' => 'integer|min:0',
            'credit_limit' => 'numeric|min:0',
            'current_balance' => 'numeric|min:0',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'address' => 'nullable|string',
            'territory' => 'nullable|string|max:100',
        ];
    }
}