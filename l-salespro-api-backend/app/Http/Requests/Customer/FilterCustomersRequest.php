<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class FilterCustomersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => 'nullable|string|in:A,A+,B,C',
            'type' => 'nullable|string',
            'territory' => 'nullable|string',
            'per_page' => 'integer|min:1|max:100',
        ];
    }
}