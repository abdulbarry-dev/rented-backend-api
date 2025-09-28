<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class UserVerificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'verification_status' => $this->verification_status,
            'user' => $this->when($this->relationLoaded('user'), [
                'id' => $this->user?->id,
                'name' => $this->user?->full_name,
                'email' => $this->user?->email
            ]),
            'image_urls' => $this->image_urls,
            'notes' => $this->when(
                $this->verification_status === 'rejected' || Auth::user() instanceof \App\Models\Admin,
                $this->notes
            ),
            'reviewed_by' => $this->when($this->relationLoaded('reviewer') && $this->reviewer, [
                'id' => $this->reviewer?->id,
                'name' => $this->reviewer?->name
            ]),
            'submitted_at' => $this->submitted_at?->format('Y-m-d H:i:s'),
            'reviewed_at' => $this->reviewed_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
