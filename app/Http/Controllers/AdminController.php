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
            $token = $request->user()->currentAccessToken();
            if ($token) {
                $token->delete();
            }

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

            // Get all admins with their approver information, ordered by status and creation date
            $admins = Admin::with('approver:id,name,email')
                          ->orderBy('status', 'desc') // active first, then pending, then banned
                          ->orderBy('created_at', 'desc')
                          ->get();

            return response()->json([
                'success' => true,
                'data' => AdminResource::collection($admins),
                'summary' => [
                    'total' => $admins->count(),
                    'active' => $admins->where('status', 'active')->count(),
                    'pending' => $admins->where('status', 'pending')->count(),
                    'banned' => $admins->where('status', 'banned')->count()
                ]
            ]);
        } catch (UnauthorizedException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \Exception('Failed to get admins', 500);
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

    /**
     * Get pending admin registrations (super admin only)
     */
    public function pendingAdmins(Request $request): JsonResponse
    {
        try {
            $currentAdmin = $request->user();
            
            if (!$currentAdmin instanceof Admin || !$currentAdmin->isSuper()) {
                throw new UnauthorizedException('Only super admins can view pending admins');
            }

            $pendingAdmins = Admin::pending()->with('approver')->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => AdminResource::collection($pendingAdmins)
            ]);
        } catch (UnauthorizedException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \Exception('Failed to get pending admins', 500);
        }
    }

    /**
     * Approve pending admin (super admin only)
     */
    public function approveAdmin(Request $request, Admin $admin): JsonResponse
    {
        $this->adminService->approvePendingAdmin($admin, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Admin approved successfully',
            'data' => new AdminResource($admin->fresh())
        ]);
    }

    /**
     * Reject pending admin (super admin only)
     */
    public function rejectAdmin(Request $request, Admin $admin): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:500'
        ]);

        $this->adminService->rejectPendingAdmin($admin, $request->user(), $validated['reason']);

        return response()->json([
            'success' => true,
            'message' => 'Admin rejected successfully',
            'data' => new AdminResource($admin->fresh())
        ]);
    }

    /**
     * Ban admin (super admin only)
     */
    public function banAdmin(Request $request, Admin $admin): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:500'
        ]);

        $this->adminService->banAdmin($admin, $request->user(), $validated['reason']);

        return response()->json([
            'success' => true,
            'message' => 'Admin banned successfully',
            'data' => new AdminResource($admin->fresh())
        ]);
    }

    /**
     * Unban admin (super admin only)
     */
    public function unbanAdmin(Request $request, Admin $admin): JsonResponse
    {
        $this->adminService->unbanAdmin($admin, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Admin unbanned successfully',
            'data' => new AdminResource($admin->fresh())
        ]);
    }

    /**
     * Delete admin (super admin only)
     */
    public function deleteAdmin(Request $request, Admin $admin): JsonResponse
    {
        $this->adminService->deleteAdmin($admin, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Admin deleted successfully'
        ]);
    }

    /**
     * Get admin statistics (super admin only)
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $currentAdmin = $request->user();
            
            if (!$currentAdmin instanceof Admin || !$currentAdmin->isSuper()) {
                throw new UnauthorizedException('Only super admins can view statistics');
            }

            $stats = $this->adminService->getStatistics();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (UnauthorizedException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \Exception('Failed to get statistics', 500);
        }
    }
}