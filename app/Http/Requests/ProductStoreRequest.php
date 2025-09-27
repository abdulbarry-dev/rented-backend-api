<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ProductStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'product_images' => 'nullable|array|max:10',
            'product_images.*' => 'string', // Image paths from storage
            'categories' => 'nullable|array|max:5',
            'categories.*' => 'string|max:50'
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Product title is required',
            'title.max' => 'Product title cannot exceed 255 characters',
            'description.required' => 'Product description is required',
            'description.max' => 'Product description cannot exceed 2000 characters',
            'product_images.max' => 'You can upload maximum 10 images',
            'categories.max' => 'You can select maximum 5 categories',
            'categories.*.max' => 'Category name cannot exceed 50 characters'
        ];
    }
}