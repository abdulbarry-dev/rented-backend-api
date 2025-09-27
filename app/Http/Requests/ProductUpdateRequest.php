<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ProductUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:2000',
            'product_images' => 'sometimes|nullable|array|max:10',
            'product_images.*' => 'string',
            'categories' => 'sometimes|nullable|array|max:5',
            'categories.*' => 'string|max:50',
            'status' => 'sometimes|in:available,rented,maintenance'
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'Product title cannot exceed 255 characters',
            'description.max' => 'Product description cannot exceed 2000 characters',
            'product_images.max' => 'You can upload maximum 10 images',
            'categories.max' => 'You can select maximum 5 categories',
            'categories.*.max' => 'Category name cannot exceed 50 characters',
            'status.in' => 'Status must be available, rented, or maintenance'
        ];
    }
}