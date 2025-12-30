<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReleaseStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        Log::info('ReleaseStockRequest authorize called', [
            'user_id' => optional($this->user())->id,
            'ip' => $this->ip(),
        ]);

        return true;
    }

    public function rules(): array
    {
        Log::debug('ReleaseStockRequest validation rules loaded', [
            'payload' => $this->all(),
        ]);

        return [
            'reservation_id' => 'required|exists:stock_reservations,id',
            'reason'         => 'nullable|string|max:100',
        ];
    }

    /**
     * Called when validation fails
     */
    protected function failedValidation(Validator $validator)
    {
        Log::warning('ReleaseStockRequest validation failed', [
            'user_id' => optional($this->user())->id,
            'errors'  => $validator->errors()->toArray(),
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
     * Called when validation passes
     */
    protected function passedValidation()
    {
        Log::info('ReleaseStockRequest validation passed', [
            'user_id'        => optional($this->user())->id,
            'reservation_id' => $this->reservation_id,
            'reason'         => $this->reason,
        ]);
    }
}
