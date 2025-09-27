<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UserVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'image_paths' => 'required|array|min:1|max:3',
            'image_paths.*' => 'required|string' // Image paths from storage
        ];
    }

    public function messages(): array
    {
        return [
            'image_paths.required' => 'At least one verification document is required',
            'image_paths.min' => 'At least one verification document is required',
            'image_paths.max' => 'You can upload maximum 3 verification documents',
            'image_paths.*.required' => 'All image paths are required',
            'image_paths.*.string' => 'Image paths must be valid strings'
        ];
    }
}