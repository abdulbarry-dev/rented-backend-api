<?php

namespace App\Http\Controllers;

use App\Exceptions\ConflictException;
use App\Exceptions\ResourceNotFoundException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\ValidationFailedException;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\AdminRegisterRequest;
use App\Http\Resources\AdminResource;
use App\Models\Admin;
use App\Services\AdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    protected AdminService $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    /**
     * Admin login
     */
    public function login(AdminLoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->validated();
            $result = $this->adminService->login($credentials);

            return response()->json([
                'success' => true,
                'message' => 'Admin logged in successfully',
                'data' => [
                    'admin' => new AdminResource($result['admin']),
                    'token' => $result['token'],
                    'role' => $result['admin']->role,
                    'permissions' => $result['admin']->getPermissions()
                ]
            ]);
        } catch (UnauthorizedException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        } catch (\Exception $e) {
            Log::error('Admin login failed: ' . $e->getMessage(), [
                'exception' => $e,
                'email' => $request->email ?? 'unknown'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to login admin',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Admin registration (only super admins can create other admins)
     */
    public function register(AdminRegisterRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $admin = $this->adminService->register($data);

            return response()->json([
                'success' => true,
                'message' => 'Admin registered successfully',
                'data' => [
                    'admin' => new AdminResource($admin),
                    'role' => $admin->role
                ]
            ], 201);
        } catch (ConflictException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \Exception('Failed to register admin', 500);
        }
    }

    /**
     * Admin logout
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $admin = $request->user();
            
            if (!$admin instanceof Admin) {
                throw new UnauthorizedException('Invalid admin authentication');
            }

            // Log the logout action
            $admin->logAction('logout');
            
            // Revoke current token
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Admin logged out successfully'
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to logout admin', 500);
        }
    }

    /**
     * Logout from all devices
     */
    public function logoutAll(Request $request): JsonResponse
    {
        try {
            $admin = $request->user();
            
            if (!$admin instanceof Admin) {
                throw new UnauthorizedException('Invalid admin authentication');
            }

            // Log the logout all action
            $admin->logAction('logout_all');
            
            // Revoke all tokens
            $admin->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Admin logged out from all devices successfully'
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to logout admin from all devices', 500);
        }
    }

    /**
     * Get admin profile
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $admin = $request->user();
            
            if (!$admin instanceof Admin) {
                throw new UnauthorizedException('Invalid admin authentication');
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'admin' => new AdminResource($admin),
                    'role' => $admin->role,
                    'permissions' => $admin->getPermissions()
                ]
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to get admin profile', 500);
        }
    }

    /**
     * Get all admins (super admin only)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $admin = $request->user();
            
            if (!$admin instanceof Admin || !$admin->isSuper()) {
                throw new UnauthorizedException('Only super admins can view all admins');
            }

            $admins = Admin::active()->get();

            return response()->json([
                'success' => true,
                'data' => AdminResource::collection($admins)
            ]);
        } catch (UnauthorizedException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \Exception('Failed to get admins', 500);
        }
    }

    /**
     * Update admin status (super admin only)
     */
    public function updateStatus(Request $request, int $adminId): JsonResponse
    {
        try {
            $currentAdmin = $request->user();
            
            if (!$currentAdmin instanceof Admin || !$currentAdmin->isSuper()) {
                throw new UnauthorizedException('Only super admins can update admin status');
            }

            $request->validate([
                'is_active' => 'required|boolean'
            ]);

            $admin = Admin::findOrFail($adminId);
            
            // Prevent deactivating self
            if ($admin->id === $currentAdmin->id) {
                throw new ConflictException('Cannot change your own status');
            }

            $admin->update(['is_active' => $request->is_active]);
            
            // Log the action
            $currentAdmin->logAction(
                $request->is_active ? 'admin_activated' : 'admin_deactivated',
                Admin::class,
                $adminId
            );

            return response()->json([
                'success' => true,
                'message' => 'Admin status updated successfully',
                'data' => new AdminResource($admin)
            ]);
        } catch (UnauthorizedException | ConflictException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \Exception('Failed to update admin status', 500);
        }
    }

    /**
     * Debug endpoint to test admin model
     */
    public function debug(): JsonResponse
    {
        try {
            $adminCount = Admin::count();
            $superAdmin = Admin::where('email', 'super@admin.com')->first();
            
            return response()->json([
                'success' => true,
                'debug' => [
                    'total_admins' => $adminCount,
                    'super_admin_exists' => $superAdmin ? true : false,
                    'super_admin_data' => $superAdmin ? [
                        'id' => $superAdmin->id,
                        'name' => $superAdmin->name,
                        'email' => $superAdmin->email,
                        'role' => $superAdmin->role,
                        'is_active' => $superAdmin->is_active
                    ] : null
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}