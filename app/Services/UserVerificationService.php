<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserVerification;
use App\Exceptions\ConflictException;
use App\Exceptions\ResourceNotFoundException;
use App\Exceptions\UnauthorizedException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class UserVerificationService extends BaseService
{
    /**
     * Submit user verification request
     */
    public function submitVerification(User $user, array $data): UserVerification
    {
        // Check if user already has pending or verified verification
        $existingVerification = UserVerification::where('user_id', $user->id)
            ->where('type', 'national_id')
            ->whereIn('verification_status', ['pending', 'verified'])
            ->first();

        if ($existingVerification) {
            if ($existingVerification->isVerified()) {
                throw new ConflictException('User is already verified');
            }
            if ($existingVerification->isPending()) {
                throw new ConflictException('User already has a pending verification request');
            }
        }

        // Create new verification request
        return UserVerification::create([
            'user_id' => $user->id,
            'type' => 'national_id',
            'verification_status' => 'pending',
            'image_paths' => $data['image_paths'] ?? [],
            'submitted_at' => now()
        ]);
    }

    /**
     * Get user's verification status
     */
    public function getUserVerificationStatus(User $user): ?UserVerification
    {
        return UserVerification::where('user_id', $user->id)
            ->where('type', 'national_id')
            ->latest()
            ->first();
    }

    /**
     * Admin: Get all pending verifications
     */
    public function getPendingVerifications(int $perPage = 15): LengthAwarePaginator
    {
        return UserVerification::with(['user:id,first_name,last_name,email'])
            ->pending()
            ->orderBy('submitted_at', 'asc')
            ->paginate($perPage);
    }

    /**
     * Admin: Get all verifications with status filter
     */
    public function getAllVerifications(?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = UserVerification::with(['user:id,first_name,last_name,email', 'reviewer:id,name']);

        if ($status) {
            $query->where('verification_status', $status);
        }

        return $query->orderBy('submitted_at', 'desc')->paginate($perPage);
    }

    /**
     * Admin: Review user verification
     */
    public function reviewVerification(
        UserVerification $verification,
        string $status,
        ?string $notes = null,
        ?int $adminId = null
    ): UserVerification {
        $verification->update([
            'verification_status' => $status,
            'notes' => $notes,
            'reviewed_by' => $adminId,
            'reviewed_at' => now()
        ]);

        return $verification->fresh(['user', 'reviewer']);
    }

    /**
     * Check if user is verified (used in other services)
     */
    public function isUserVerified(User $user): bool
    {
        return UserVerification::where('user_id', $user->id)
            ->where('type', 'national_id')
            ->where('verification_status', 'verified')
            ->exists();
    }

    /**
     * Get verification requirement message for unverified users
     */
    public function getVerificationRequiredMessage(): string
    {
        return 'You must verify your national ID before you can create products, rent items, or make purchases. Please submit your verification documents.';
    }

    /**
     * Resubmit verification after rejection
     */
    public function resubmitVerification(User $user, array $data): UserVerification
    {
        // Find the rejected verification
        $rejectedVerification = UserVerification::where('user_id', $user->id)
            ->where('type', 'national_id')
            ->where('verification_status', 'rejected')
            ->latest()
            ->first();

        if (!$rejectedVerification) {
            throw new ResourceNotFoundException('No rejected verification found to resubmit');
        }

        // Delete old image files if they exist
        if ($rejectedVerification->image_paths) {
            foreach ($rejectedVerification->image_paths as $imagePath) {
                Storage::delete($imagePath);
            }
        }

        // Update the rejected verification to pending with new data
        $rejectedVerification->update([
            'verification_status' => 'pending',
            'image_paths' => $data['image_paths'] ?? [],
            'notes' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'submitted_at' => now()
        ]);

        return $rejectedVerification->fresh();
    }

    /**
     * Delete verification images from storage
     */
    public function deleteVerificationImages(UserVerification $verification): bool
    {
        if ($verification->image_paths) {
            foreach ($verification->image_paths as $imagePath) {
                Storage::delete($imagePath);
            }
        }
        return true;
    }
}
