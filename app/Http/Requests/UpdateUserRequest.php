<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user'); // Get ID from route parameter
        
        return [
            'first_name' => 'sometimes|required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'last_name' => 'sometimes|required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'email' => 'sometimes|required|string|email:rfc,dns|max:255|unique:users,email,' . $userId,
            'phone' => 'sometimes|required|string|max:20|unique:users,phone,' . $userId . '|regex:/^[\+]?[0-9\-\(\)\s]+$/',
            'password' => 'sometimes|required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
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
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422));
    }
}