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
            'full_name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'email' => 'required|string|email:rfc,dns|max:255|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone|regex:/^[\+]?[0-9\-\(\)\s]+$/',
            'gender' => 'required|string|in:H,F',
            'role' => 'required|string|in:customer,seller',
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.regex' => 'Full name should only contain letters and spaces.',
            'email.unique' => 'This email is already registered.',
            'phone.unique' => 'This phone number is already registered.',
            'phone.regex' => 'Please enter a valid phone number.',
            'gender.required' => 'Gender is required.',
            'gender.in' => 'Gender must be either H (Homme) or F (Femme).',
            'role.required' => 'Role selection is required.',
            'role.in' => 'Role must be either customer or seller.',
            'password.regex' => 'Password must contain at least one lowercase letter, one uppercase letter, and one number.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}
