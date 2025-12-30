<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // RBAC handled via route middleware (can:update_order_status)
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => 'required|string|in:Pending,Confirmed,Processing,Shipped,Delivered,Cancelled',
        ];
    }

    /**
     * Custom messages
     */
    public function messages(): array
    {
        return [
            'status.in' => 'Invalid order status. Allowed: Pending, Confirmed, Processing, Shipped, Delivered, Cancelled.',
        ];
    }
}