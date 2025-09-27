<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserVerificationReviewRequest;
use App\Http\Resources\UserVerificationResource;
use App\Models\UserVerification;
use App\Services\UserVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserVerificationController extends Controller
{
    public function __construct(
        private UserVerificationService $verificationService
    ) {}

    /**
     * Get pending user verifications
     */
    public function pendingVerifications(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $verifications = $this->verificationService->getPendingVerifications($perPage);

            return response()->json([
                'success' => true,
                'data' => UserVerificationResource::collection($verifications->items()),
                'pagination' => [
                    'current_page' => $verifications->currentPage(),
                    'per_page' => $verifications->perPage(),
                    'total' => $verifications->total(),
                    'last_page' => $verifications->lastPage()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pending verifications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all user verifications with optional status filter
     */
    public function allVerifications(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $status = $request->get('status');
            
            $verifications = $this->verificationService->getAllVerifications($status, $perPage);

            return response()->json([
                'success' => true,
                'data' => UserVerificationResource::collection($verifications->items()),
                'pagination' => [
                    'current_page' => $verifications->currentPage(),
                    'per_page' => $verifications->perPage(),
                    'total' => $verifications->total(),
                    'last_page' => $verifications->lastPage()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch verifications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Review user verification (approve/reject)
     */
    public function reviewVerification(UserVerificationReviewRequest $request, string $id): JsonResponse
    {
        try {
            $verification = UserVerification::findOrFail($id);
            $data = $request->validated();

            $reviewedVerification = $this->verificationService->reviewVerification(
                $verification,
                $data['verification_status'],
                $data['notes'] ?? null,
                $request->user()->id
            );

            $message = $data['verification_status'] === 'verified'
                ? 'User verification approved successfully'
                : 'User verification rejected successfully';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => new UserVerificationResource($reviewedVerification)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get verification statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_verifications' => UserVerification::count(),
                'pending_verifications' => UserVerification::pending()->count(),
                'verified_users' => UserVerification::verified()->count(),
                'rejected_verifications' => UserVerification::rejected()->count(),
                'recent_submissions' => UserVerification::where('submitted_at', '>=', now()->subDays(7))->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics: ' . $e->getMessage()
            ], 500);
        }
    }
}
