<?php

namespace App\Http\Requests;

use App\Models\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\UnauthorizedException;

class ProductReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();
        return $user instanceof Admin && $user->isSuper() && $user->isActive();
    }

    public function rules(): array
    {
        return [
            'verification_status' => 'required|in:verified,rejected',
            'notes' => 'nullable|string|max:500'
        ];
    }

    public function messages(): array
    {
        return [
            'verification_status.required' => 'Verification status is required',
            'verification_status.in' => 'Status must be verified or rejected',
            'notes.max' => 'Notes cannot exceed 500 characters'
        ];
    }

    protected function failedAuthorization()
    {
        throw new UnauthorizedException('Only super admins can review products');
    }
}