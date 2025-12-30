<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class DashboardAnalyticsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // or check permissions if needed
    }

    public function rules(): array
    {
        return [
            'period' => 'sometimes|in:today,week,month,quarter,year',
        ];
    }
}