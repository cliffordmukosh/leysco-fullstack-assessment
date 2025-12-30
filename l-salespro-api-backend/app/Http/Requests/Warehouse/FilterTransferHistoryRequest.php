<?php

namespace App\Http\Requests\Warehouse;

use Illuminate\Foundation\Http\FormRequest;

class FilterTransferHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'   => 'nullable|in:pending,completed,cancelled',
            'per_page' => 'nullable|integer|min:5|max:100',
        ];
    }
}