<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'last_name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'email' => 'required|string|email:rfc,dns|max:255|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone|regex:/^[\+]?[0-9\-\(\)\s]+$/',
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.regex' => 'First name should only contain letters and spaces.',
            'last_name.regex' => 'Last name should only contain letters and spaces.',
            'email.unique' => 'This email is already registered.',
            'phone.unique' => 'This phone number is already registered.',
            'phone.regex' => 'Please enter a valid phone number.',
            'password.regex' => 'Password must contain at least one lowercase letter, one uppercase letter, and one number.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}
