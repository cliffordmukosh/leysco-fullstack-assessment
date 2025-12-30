<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;

class MarkAsReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null; // must be logged in
    }

    public function rules(): array
    {
        return [
            // No body data is required for this endpoint
            // But we can validate the route parameter
            'id' => 'required|uuid|exists:notifications,id',
        ];
    }

    public function messages(): array
    {
        return [
            'id.exists' => 'This notification does not exist or does not belong to you.',
            'id.uuid'   => 'Invalid notification ID format.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'id' => $this->route('id'),
        ]);
    }
}