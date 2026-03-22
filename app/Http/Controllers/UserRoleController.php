<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class UserRoleController extends Controller
{
    /**
     * Display a listing of user-role assignments.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = UserRole::with(['user', 'role']);
            
            // Filter by user
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            
            // Filter by role
            if ($request->has('role_id')) {
                $query->where('role_id', $request->role_id);
            }
            
            // Filter by primary role
            if ($request->has('is_primary')) {
                $query->where('is_primary', $request->boolean('is_primary'));
            }
            
            // Filter by active assignments (not expired)
            if ($request->has('active_only')) {
                $query->where(function($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>=', now());
                });
            }
            
            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);
            
            $assignments = $query->paginate($request->get('per_page', 50));
            
            return response()->json([
                'success' => true,
                'data' => $assignments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user-role assignments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created user-role assignment.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
            'scope' => 'nullable|array',
            'assigned_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:assigned_at',
            'is_primary' => 'boolean',
            'assigned_by' => 'nullable|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if assignment already exists
            $exists = UserRole::where('user_id', $request->user_id)
                ->where('role_id', $request->role_id)
                ->exists();
            
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'User-role assignment already exists'
                ], 409);
            }
            
            // If setting as primary, remove primary flag from other roles
            if ($request->boolean('is_primary')) {
                UserRole::where('user_id', $request->user_id)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }
            
            $assignment = UserRole::create([
                'user_id' => $request->user_id,
                'role_id' => $request->role_id,
                'scope' => $request->scope,
                'assigned_at' => $request->assigned_at ?? now(),
                'expires_at' => $request->expires_at,
                'is_primary' => $request->is_primary ?? false,
                'assigned_by' => $request->assigned_by ?? auth()->id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'User-role assignment created successfully',
                'data' => $assignment->load(['user', 'role', 'assignedBy'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user-role assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user-role assignment.
     */
    public function show(UserRole $userRole): JsonResponse
    {
        try {
            $userRole->load(['user', 'role', 'assignedBy']);
            return response()->json([
                'success' => true,
                'data' => $userRole
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user-role assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified user-role assignment.
     */
    public function update(Request $request, UserRole $userRole): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'scope' => 'nullable|array',
            'expires_at' => 'nullable|date|after:assigned_at',
            'is_primary' => 'boolean',
            'assigned_by' => 'nullable|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // If setting as primary, remove primary flag from other roles
            if ($request->has('is_primary') && $request->boolean('is_primary')) {
                UserRole::where('user_id', $userRole->user_id)
                    ->where('id', '!=', $userRole->id)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }
            
            $userRole->update($request->only(['scope', 'expires_at', 'is_primary', 'assigned_by']));
            
            return response()->json([
                'success' => true,
                'message' => 'User-role assignment updated successfully',
                'data' => $userRole->load(['user', 'role', 'assignedBy'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user-role assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user-role assignment.
     */
    public function destroy(UserRole $userRole): JsonResponse
    {
        try {
            // Check if this is the user's primary role
            if ($userRole->is_primary) {
                // Find another role to set as primary if available
                $otherRole = UserRole::where('user_id', $userRole->user_id)
                    ->where('id', '!=', $userRole->id)
                    ->first();
                
                if ($otherRole) {
                    $otherRole->update(['is_primary' => true]);
                }
            }
            
            $userRole->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'User-role assignment deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user-role assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set primary role for user.
     */
    public function setPrimary(UserRole $userRole): JsonResponse
    {
        try {
            // Remove primary flag from all other roles for this user
            UserRole::where('user_id', $userRole->user_id)
                ->where('id', '!=', $userRole->id)
                ->update(['is_primary' => false]);
            
            // Set this role as primary
            $userRole->update(['is_primary' => true]);
            
            return response()->json([
                'success' => true,
                'message' => 'Primary role set successfully',
                'data' => $userRole->load(['user', 'role'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set primary role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get roles for a specific user.
     */
    public function userRoles($userId): JsonResponse
    {
        try {
            $roles = UserRole::where('user_id', $userId)
                ->with(['role', 'assignedBy'])
                ->orderBy('is_primary', 'desc')
                ->orderBy('assigned_at', 'desc')
                ->paginate(50);
            
            return response()->json([
                'success' => true,
                'data' => $roles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get users for a specific role.
     */
    public function roleUsers($roleId): JsonResponse
    {
        try {
            $users = UserRole::where('role_id', $roleId)
                ->with(['user', 'assignedBy'])
                ->orderBy('is_primary', 'desc')
                ->orderBy('assigned_at', 'desc')
                ->paginate(50);
            
            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch role users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's primary role.
     */
    public function userPrimaryRole($userId): JsonResponse
    {
        try {
            $primaryRole = UserRole::where('user_id', $userId)
                ->where('is_primary', true)
                ->with(['role'])
                ->first();
            
            if (!$primaryRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'User has no primary role assigned'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $primaryRole
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user primary role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if user has role.
     */
    public function checkUserRole(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $hasRole = UserRole::where('user_id', $request->user_id)
                ->where('role_id', $request->role_id)
                ->where(function($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>=', now());
                })
                ->exists();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'has_role' => $hasRole,
                    'user_id' => $request->user_id,
                    'role_id' => $request->role_id
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check user role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk assign roles to users.
     */
    public function bulkAssign(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'role_id' => 'required|exists:roles,id',
            'scope' => 'nullable|array',
            'expires_at' => 'nullable|date',
            'is_primary' => 'boolean',
            'assigned_by' => 'nullable|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $created = [];
            $skipped = [];
            
            foreach ($request->user_ids as $userId) {
                // Check if assignment already exists
                $exists = UserRole::where('user_id', $userId)
                    ->where('role_id', $request->role_id)
                    ->exists();
                
                if (!$exists) {
                    // If setting as primary, remove primary flag from other roles
                    if ($request->boolean('is_primary')) {
                        UserRole::where('user_id', $userId)
                            ->where('is_primary', true)
                            ->update(['is_primary' => false]);
                    }
                    
                    $assignment = UserRole::create([
                        'user_id' => $userId,
                        'role_id' => $request->role_id,
                        'scope' => $request->scope,
                        'assigned_at' => now(),
                        'expires_at' => $request->expires_at,
                        'is_primary' => $request->is_primary ?? false,
                        'assigned_by' => $request->assigned_by ?? auth()->id()
                    ]);
                    $created[] = $assignment;
                } else {
                    $skipped[] = $userId;
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Bulk assignment completed',
                'data' => [
                    'created' => count($created),
                    'skipped' => count($skipped),
                    'total_requested' => count($request->user_ids)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk assign roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get assignment statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $totalAssignments = UserRole::count();
            $activeAssignments = UserRole::where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>=', now());
            })->count();
            
            $expiredAssignments = UserRole::where('expires_at', '<', now())->count();
            $primaryAssignments = UserRole::where('is_primary', true)->count();
            
            $usersWithRoles = User::has('roles')->count();
            $usersWithoutRoles = User::doesntHave('roles')->count();
            
            $rolesWithUsers = Role::has('users')->count();
            $rolesWithoutUsers = Role::doesntHave('users')->count();
            
            $avgRolesPerUser = User::withCount('roles')->get()->avg('roles_count');
            $maxRolesUser = User::withCount('roles')->orderBy('roles_count', 'desc')->first();
            $minRolesUser = User::withCount('roles')->orderBy('roles_count', 'asc')->first();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total_assignments' => $totalAssignments,
                    'active_assignments' => $activeAssignments,
                    'expired_assignments' => $expiredAssignments,
                    'primary_assignments' => $primaryAssignments,
                    'users_with_roles' => $usersWithRoles,
                    'users_without_roles' => $usersWithoutRoles,
                    'roles_with_users' => $rolesWithUsers,
                    'roles_without_users' => $rolesWithoutUsers,
                    'average_roles_per_user' => round($avgRolesPerUser, 2),
                    'user_with_most_roles' => $maxRolesUser ? [
                        'user_id' => $maxRolesUser->id,
                        'user_name' => $maxRolesUser->name,
                        'roles_count' => $maxRolesUser->roles_count
                    ] : null,
                    'user_with_least_roles' => $minRolesUser ? [
                        'user_id' => $minRolesUser->id,
                        'user_name' => $minRolesUser->name,
                        'roles_count' => $minRolesUser->roles_count
                    ] : null
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get assignment statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get expiring assignments.
     */
    public function expiringAssignments(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'days' => 'nullable|integer|min:1|max:365'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $days = $request->days ?? 7;
            $expiryDate = now()->addDays($days);
            
            $expiring = UserRole::whereNotNull('expires_at')
                ->where('expires_at', '<=', $expiryDate)
                ->where('expires_at', '>=', now())
                ->with(['user', 'role'])
                ->orderBy('expires_at')
                ->paginate(50);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'expiring_within_days' => $days,
                    'assignments' => $expiring
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch expiring assignments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Renew expired assignment.
     */
    public function renewAssignment(UserRole $userRole): JsonResponse
    {
        try {
            // Check if assignment is expired
            if ($userRole->expires_at && $userRole->expires_at >= now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Assignment is not expired yet'
                ], 400);
            }
            
            // Renew for 1 year from now
            $userRole->update([
                'expires_at' => now()->addYear(),
                'assigned_by' => auth()->id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Assignment renewed successfully',
                'data' => $userRole->load(['user', 'role'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to renew assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user permissions through roles.
     */
    public function userPermissions($userId): JsonResponse
    {
        try {
            $user = User::findOrFail($userId);
            $permissions = collect();
            
            foreach ($user->roles as $role) {
                foreach ($role->permissions as $permission) {
                    $permissions->push($permission);
                }
            }
            
            $uniquePermissions = $permissions->unique('id')->values();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'user_id' => $userId,
                    'total_permissions' => $uniquePermissions->count(),
                    'permissions' => $uniquePermissions
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}