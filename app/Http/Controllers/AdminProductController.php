<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductReviewRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}

    /**
     * Get products pending review
     */
    public function pendingProducts(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $products = $this->productService->getProductsForReview($perPage);

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($products->items()),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'last_page' => $products->lastPage()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pending products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Review a product (approve/reject)
     */
    public function reviewProduct(ProductReviewRequest $request, string $id): JsonResponse
    {
        try {
            $product = Product::findOrFail($id);
            $data = $request->validated();
            
            $reviewedProduct = $this->productService->reviewProduct(
                $product,
                $data['verification_status'],
                $data['notes'] ?? null,
                $request->user()->id
            );

            $message = $data['verification_status'] === 'verified' 
                ? 'Product approved successfully'
                : 'Product rejected successfully';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => new ProductResource($reviewedProduct)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all products (admin view with all statuses)
     */
    public function allProducts(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $status = $request->get('status');
            
            $query = Product::with(['description', 'owner:id,first_name,last_name', 'verification']);
            
            if ($status) {
                $query->whereHas('verification', function($q) use ($status) {
                    $q->where('verification_status', $status);
                });
            }
            
            $products = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($products->items()),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'last_page' => $products->lastPage()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products: ' . $e->getMessage()
            ], 500);
        }
    }
}