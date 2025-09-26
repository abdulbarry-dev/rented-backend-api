<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    /**
     * Display a listing of users with pagination
     */
    public function index(): AnonymousResourceCollection
    {
        $users = $this->userService->getAllUsers();
        
        return UserResource::collection($users);
    }

    /**
     * Store a newly created user
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => new UserResource($user)
        ], 201);
    }

    /**
     * Display the specified user
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new UserResource($user)
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $updatedUser = $this->userService->updateUser($user, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => new UserResource($updatedUser)
        ]);
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user): JsonResponse
    {
        $this->userService->deleteUser($user);

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Get users by role
     */
    public function getUsersByRole(string $role): AnonymousResourceCollection
    {
        $users = $this->userService->getUsersByRole($role);
        
        return UserResource::collection($users);
    }

    /**
     * Activate user
     */
    public function activate(User $user): JsonResponse
    {
        $activatedUser = $this->userService->activateUser($user);

        return response()->json([
            'success' => true,
            'message' => 'User activated successfully',
            'data' => new UserResource($activatedUser)
        ]);
    }

    /**
     * Deactivate user
     */
    public function deactivate(User $user): JsonResponse
    {
        $deactivatedUser = $this->userService->deactivateUser($user);

        return response()->json([
            'success' => true,
            'message' => 'User deactivated successfully',
            'data' => new UserResource($deactivatedUser)
        ]);
    }

    /**
     * Change user role
     */
    public function changeRole(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'role' => 'required|string|in:customer,seller'
        ]);

        $updatedUser = $this->userService->changeUserRole($user, $request->role);

        return response()->json([
            'success' => true,
            'message' => 'User role changed successfully',
            'data' => new UserResource($updatedUser)
        ]);
    }
}