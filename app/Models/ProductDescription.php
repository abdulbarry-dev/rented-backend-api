<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductDescription extends Model
{
    protected $fillable = [
        'product_id',
        'title',
        'description',
        'product_images',
        'categories'
    ];

    protected $casts = [
        'product_images' => 'array',
        'categories' => 'array'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getImageUrlsAttribute(): array
    {
        return array_map(fn($path) => Storage::url($path), $this->product_images ?? []);
    }
}