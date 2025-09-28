<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'status' => $this->status,
            'owner' => [
                'id' => $this->owner->id,
                'name' => $this->owner->full_name,
            ],
            'title' => $this->description?->title,
            'description' => $this->description?->description,
            'images' => $this->description?->image_urls ?? [],
            'categories' => $this->description?->categories ?? [],
            'verification_status' => $this->verification?->verification_status ?? 'pending',
            'verification_notes' => $this->when(
                $this->verification?->verification_status === 'rejected',
                $this->verification?->notes
            ),
            'average_rating' => round($this->average_rating, 1),
            'total_reviews' => $this->total_reviews,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}