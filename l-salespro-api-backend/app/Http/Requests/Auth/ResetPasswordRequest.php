<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token'                 => ['required', 'string'],
            'email'                 => ['required', 'string', 'email', 'max:255'],
            'password'              => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',           // Must have at least 1 uppercase
                'regex:/[0-9]/',           // Must have at least 1 number
                'regex:/[@$!%*#?&]/',      // Must have at least 1 special character
                'confirmed',
            ],
            'password_confirmation' => ['required'],
        ];
    }

    /**
     * Custom error messages for validation
     */
    public function messages(): array
    {
        return [
            'token.required'                 => 'Reset token is required.',
            'email.required'                 => 'Email address is required.',
            'email.email'                    => 'Please provide a valid email address.',
            'email.max'                      => 'Email cannot exceed 255 characters.',
            'password.required'              => 'Password is required.',
            'password.min'                   => 'Password must be at least 8 characters long.',
            'password.regex'                 => 'Password must contain at least one uppercase letter, one number, and one special character.',
            'password.confirmed'             => 'Password confirmation does not match.',
            'password_confirmation.required' => 'Please confirm your password.',
        ];
    }
}
