<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserVerificationStoreRequest;
use App\Http\Resources\UserVerificationResource;
use App\Services\UserVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserVerificationController extends Controller
{
    public function __construct(
        private UserVerificationService $verificationService
    ) {}

    /**
     * Get user's verification status
     */
    public function status(Request $request): JsonResponse
    {
        try {
            $verification = $this->verificationService->getUserVerificationStatus($request->user());

            if (!$verification) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'verification_status' => 'not_submitted',
                        'message' => 'No verification request found. Please submit your national ID for verification.'
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => new UserVerificationResource($verification)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get verification status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit verification request
     */
    public function submit(UserVerificationStoreRequest $request): JsonResponse
    {
        try {
            $verification = $this->verificationService->submitVerification(
                $request->user(),
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Verification request submitted successfully. Please wait for admin review.',
                'data' => new UserVerificationResource($verification)
            ], 201);
        } catch (\App\Exceptions\ConflictException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 409);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit verification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resubmit verification after rejection
     */
    public function resubmit(UserVerificationStoreRequest $request): JsonResponse
    {
        try {
            $verification = $this->verificationService->resubmitVerification(
                $request->user(),
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Verification request resubmitted successfully. Please wait for admin review.',
                'data' => new UserVerificationResource($verification)
            ]);
        } catch (\App\Exceptions\ResourceNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resubmit verification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get verification requirements info
     */
    public function requirements(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'type' => 'national_id',
                'required_documents' => [
                    'National ID card (front side)',
                    'National ID card (back side - optional)',
                    'Clear selfie holding the ID (optional)'
                ],
                'requirements' => [
                    'Images must be clear and readable',
                    'All text and photo must be visible',
                    'No blurry or dark images',
                    'Maximum file size: 5MB per image',
                    'Supported formats: JPG, PNG'
                ],
                'processing_time' => '1-3 business days',
                'restrictions' => [
                    'Cannot create products until verified',
                    'Cannot rent or purchase items until verified',
                    'Can only browse products as viewer'
                ]
            ]
        ]);
    }
}
