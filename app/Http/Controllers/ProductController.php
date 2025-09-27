<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}

    /**
     * Display a listing of products (public)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search');
            $category = $request->get('category');

            if ($search) {
                $products = $this->productService->searchProducts($search, $perPage);
            } elseif ($category) {
                $products = $this->productService->getProductsByCategory($category, $perPage);
            } else {
                $products = $this->productService->getAllProducts($perPage);
            }

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

    /**
     * Store a newly created product
     */
    public function store(ProductStoreRequest $request): JsonResponse
    {
        try {
            $product = $this->productService->createProduct(
                $request->user(),
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully and submitted for review',
                'data' => new ProductResource($product)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified product
     */
    public function show(string $id): JsonResponse
    {
        try {
            $product = $this->productService->getProductById($id);

            return response()->json([
                'success' => true,
                'data' => new ProductResource($product)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
    }

    /**
     * Update the specified product
     */
    public function update(ProductUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $product = Product::findOrFail($id);
            $updatedProduct = $this->productService->updateProduct(
                $product,
                $request->user(),
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => new ProductResource($updatedProduct)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e instanceof \App\Exceptions\UnauthorizedException ? 403 : 500);
        }
    }

    /**
     * Remove the specified product
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $product = Product::findOrFail($id);
            $this->productService->deleteProduct($product, request()->user());

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e instanceof \App\Exceptions\UnauthorizedException ? 403 : 500);
        }
    }

    /**
     * Get current user's products
     */
    public function myProducts(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $products = $this->productService->getUserProducts($request->user(), $perPage);

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
                'message' => 'Failed to fetch your products: ' . $e->getMessage()
            ], 500);
        }
    }
}
