<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductDescription;
use App\Models\ProductVerification;
use App\Models\User;
use App\Exceptions\ResourceNotFoundException;
use App\Exceptions\UnauthorizedException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductService extends BaseService
{
    /**
     * Get all products with pagination (public view)
     */
    public function getAllProducts(int $perPage = 15): LengthAwarePaginator
    {
        return Product::with(['description', 'owner:id,first_name,last_name', 'verification'])
                     ->verified()
                     ->active()
                     ->paginate($perPage);
    }

    /**
     * Get user's own products
     */
    public function getUserProducts(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Product::with(['description', 'verification'])
                     ->byOwner($user->id)
                     ->paginate($perPage);
    }

    /**
     * Create a new product
     */
    public function createProduct(User $user, array $data): Product
    {
        // Check if user is verified
        if (!$user->canManageProducts()) {
            throw new UnauthorizedException('Only verified sellers can create products');
        }

        return DB::transaction(function () use ($user, $data) {
            // Create product
            $product = Product::create([
                'owner_id' => $user->id,
                'status' => 'available'
            ]);

            // Create description
            ProductDescription::create([
                'product_id' => $product->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'product_images' => $data['product_images'] ?? [],
                'categories' => $data['categories'] ?? []
            ]);

            // Create verification entry
            ProductVerification::create([
                'product_id' => $product->id,
                'verification_status' => 'pending',
                'submitted_at' => now()
            ]);

            return $product->load(['description', 'verification']);
        });
    }

    /**
     * Update product
     */
    public function updateProduct(Product $product, User $user, array $data): Product
    {
        // Check if user is verified
        if (!$user->canManageProducts()) {
            throw new UnauthorizedException('Only verified sellers can manage products');
        }

        if (!$product->isOwner($user)) {
            throw new UnauthorizedException('You can only update your own products');
        }

        return DB::transaction(function () use ($product, $data) {
            // Update product status if provided
            if (isset($data['status'])) {
                $product->update(['status' => $data['status']]);
            }

            // Update description
            if ($product->description) {
                $product->description->update([
                    'title' => $data['title'] ?? $product->description->title,
                    'description' => $data['description'] ?? $product->description->description,
                    'product_images' => $data['product_images'] ?? $product->description->product_images,
                    'categories' => $data['categories'] ?? $product->description->categories
                ]);
            }

            // Reset verification status if product details changed
            if (isset($data['title']) || isset($data['description']) || isset($data['product_images'])) {
                $product->verification()->update([
                    'verification_status' => 'pending',
                    'submitted_at' => now(),
                    'reviewed_at' => null,
                    'reviewed_by' => null,
                    'notes' => null
                ]);
            }

            return $product->fresh(['description', 'verification']);
        });
    }

    /**
     * Delete product
     */
    public function deleteProduct(Product $product, User $user): bool
    {
        // Check if user is verified
        if (!$user->canManageProducts()) {
            throw new UnauthorizedException('Only verified sellers can manage products');
        }

        if (!$product->isOwner($user)) {
            throw new UnauthorizedException('You can only delete your own products');
        }

        return DB::transaction(function () use ($product) {
            // Delete associated images from storage
            if ($product->description && $product->description->product_images) {
                foreach ($product->description->product_images as $imagePath) {
                    Storage::delete($imagePath);
                }
            }

            return $product->delete();
        });
    }

    /**
     * Get product by ID
     */
    public function getProductById(int $id): Product
    {
        return Product::with(['description', 'owner:id,first_name,last_name', 'verification', 'reviews.user:id,first_name,last_name'])
                     ->findOrFail($id);
    }

    /**
     * Get products by category
     */
    public function getProductsByCategory(string $category, int $perPage = 15): LengthAwarePaginator
    {
        return Product::with(['description', 'owner:id,first_name,last_name', 'verification'])
                     ->whereHas('description', function($query) use ($category) {
                         $query->whereJsonContains('categories', $category);
                     })
                     ->verified()
                     ->active()
                     ->paginate($perPage);
    }

    /**
     * Search products
     */
    public function searchProducts(string $search, int $perPage = 15): LengthAwarePaginator
    {
        return Product::with(['description', 'owner:id,first_name,last_name', 'verification'])
                     ->whereHas('description', function($query) use ($search) {
                         $query->where('title', 'like', "%{$search}%")
                               ->orWhere('description', 'like', "%{$search}%");
                     })
                     ->verified()
                     ->active()
                     ->paginate($perPage);
    }

    /**
     * Admin: Get all products for review
     */
    public function getProductsForReview(int $perPage = 15): LengthAwarePaginator
    {
        return Product::with(['description', 'owner:id,first_name,last_name', 'verification'])
                     ->whereHas('verification', function($query) {
                         $query->where('verification_status', 'pending');
                     })
                     ->paginate($perPage);
    }

    /**
     * Admin: Review product
     */
    public function reviewProduct(Product $product, string $status, ?string $notes = null, int $adminId = null): Product
    {
        $verification = $product->verification;
        
        if (!$verification) {
            throw new ResourceNotFoundException('Product verification not found');
        }

        $verification->update([
            'verification_status' => $status,
            'notes' => $notes,
            'reviewed_by' => $adminId,
            'reviewed_at' => now()
        ]);

        return $product->fresh(['description', 'verification']);
    }

    /**
     * Check if user can rent/book a product
     */
    public function canUserRentProduct(User $user, Product $product): array
    {
        $response = [
            'can_rent' => false,
            'message' => '',
            'verification_required' => false
        ];

        // Check if user is verified
        if (!$user->canRentProducts()) {
            $response['message'] = 'You must be verified to rent products. Please complete your identity verification first.';
            $response['verification_required'] = true;
            return $response;
        }

        // Check if product is available
        if (!$product->isAvailable()) {
            $response['message'] = 'This product is not available for rent.';
            return $response;
        }

        // Check if product is verified
        if (!$product->isVerified()) {
            $response['message'] = 'This product has not been verified yet.';
            return $response;
        }

        // Check if user is not the owner
        if ($product->isOwner($user)) {
            $response['message'] = 'You cannot rent your own product.';
            return $response;
        }

        $response['can_rent'] = true;
        $response['message'] = 'You can rent this product.';
        return $response;
    }

    /**
     * Initiate product rental (placeholder for rental logic)
     */
    public function initiateRental(User $user, Product $product, array $data): array
    {
        $canRent = $this->canUserRentProduct($user, $product);
        
        if (!$canRent['can_rent']) {
            throw new UnauthorizedException($canRent['message']);
        }

        // TODO: Implement actual rental logic (booking, payments, etc.)
        // This is a placeholder for future rental system implementation
        
        return [
            'message' => 'Rental request initiated successfully',
            'rental_id' => 'RENT_' . time() . '_' . $product->id,
            'product' => $product->load(['description', 'owner:id,first_name,last_name'])
        ];
    }
}