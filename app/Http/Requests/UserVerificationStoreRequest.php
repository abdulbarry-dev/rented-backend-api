<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UserVerificationStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'image_paths' => 'required|array|min:1|max:3',
            'image_paths.*' => 'string|max:255' // Image paths from storage
        ];
    }

    public function messages(): array
    {
        return [
            'image_paths.required' => 'At least one image of your national ID is required',
            'image_paths.min' => 'Please upload at least one image of your national ID',
            'image_paths.max' => 'You can upload maximum 3 images',
            'image_paths.*.string' => 'Image path must be a valid string',
            'image_paths.*.max' => 'Image path is too long'
        ];
    }
}