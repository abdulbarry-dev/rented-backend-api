<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login' => 'required|string', // Can be email or phone
            'password' => 'required|string|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'login.required' => 'Email or phone number is required.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
        ];
    }

    /**
     * Validate that login is either email or phone format
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $login = $this->input('login');
            
            if (!filter_var($login, FILTER_VALIDATE_EMAIL) && !preg_match('/^[0-9+\-\s()]+$/', $login)) {
                $validator->errors()->add('login', 'Login must be a valid email address or phone number.');
            }
        });
    }
}
