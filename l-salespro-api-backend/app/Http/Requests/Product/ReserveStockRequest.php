<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReserveStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        Log::info('ReserveStockRequest authorize called', [
            'user_id' => optional($this->user())->id,
            'ip' => $this->ip(),
        ]);

        return true;
    }

    public function rules(): array
    {
        Log::debug('ReserveStockRequest validation rules loaded', [
            'payload' => $this->all()
        ]);

        return [
            'order_id'     => 'required|exists:orders,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity'     => 'required|integer|min:1',
        ];
    }

    /**
     * Called when validation FAILS
     */
    protected function failedValidation(Validator $validator)
    {
        Log::warning('ReserveStockRequest validation failed', [
            'user_id' => optional($this->user())->id,
            'errors' => $validator->errors()->toArray(),
            'payload' => $this->all(),
        ]);

        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }

    /**
     * Called when validation PASSES
     */
    protected function passedValidation()
    {
        Log::info('ReserveStockRequest validation passed', [
            'user_id' => optional($this->user())->id,
            'order_id' => $this->order_id,
            'warehouse_id' => $this->warehouse_id,
            'quantity' => $this->quantity,
        ]);
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