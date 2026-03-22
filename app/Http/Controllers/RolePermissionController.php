<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class RolePermissionController extends Controller
{
    /**
     * Display a listing of role-permission assignments.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = RolePermission::with(['role', 'permission']);
            
            // Filter by role
            if ($request->has('role_id')) {
                $query->where('role_id', $request->role_id);
            }
            
            // Filter by permission
            if ($request->has('permission_id')) {
                $query->where('permission_id', $request->permission_id);
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
                'message' => 'Failed to fetch role-permission assignments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created role-permission assignment.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id',
            'constraints' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if assignment already exists
            $exists = RolePermission::where('role_id', $request->role_id)
                ->where('permission_id', $request->permission_id)
                ->exists();
            
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role-permission assignment already exists'
                ], 409);
            }
            
            $assignment = RolePermission::create([
                'role_id' => $request->role_id,
                'permission_id' => $request->permission_id,
                'constraints' => $request->constraints
            ]);
            
            // Update the role's permissions array
            $role = Role::find($request->role_id);
            $permission = Permission::find($request->permission_id);
            
            $rolePermissions = $role->permissions ?? [];
            $rolePermissions[] = $permission->name;
            $role->update(['permissions' => array_unique($rolePermissions)]);
            
            return response()->json([
                'success' => true,
                'message' => 'Role-permission assignment created successfully',
                'data' => $assignment->load(['role', 'permission'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create role-permission assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified role-permission assignment.
     */
    public function show(RolePermission $rolePermission): JsonResponse
    {
        try {
            $rolePermission->load(['role', 'permission']);
            return response()->json([
                'success' => true,
                'data' => $rolePermission
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch role-permission assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified role-permission assignment.
     */
    public function update(Request $request, RolePermission $rolePermission): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'constraints' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $rolePermission->update([
                'constraints' => $request->constraints
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Role-permission assignment updated successfully',
                'data' => $rolePermission->load(['role', 'permission'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role-permission assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified role-permission assignment.
     */
    public function destroy(RolePermission $rolePermission): JsonResponse
    {
        try {
            // Get role and permission before deletion
            $role = $rolePermission->role;
            $permission = $rolePermission->permission;
            
            $rolePermission->delete();
            
            // Update the role's permissions array
            if ($role && $permission) {
                $rolePermissions = $role->permissions ?? [];
                $rolePermissions = array_filter($rolePermissions, function($perm) use ($permission) {
                    return $perm !== $permission->name;
                });
                $role->update(['permissions' => array_values($rolePermissions)]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Role-permission assignment deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role-permission assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk assign permissions to role.
     */
    public function bulkAssign(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|exists:roles,id',
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id',
            'constraints' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $role = Role::find($request->role_id);
            $created = [];
            $skipped = [];
            
            foreach ($request->permission_ids as $permissionId) {
                // Check if assignment already exists
                $exists = RolePermission::where('role_id', $request->role_id)
                    ->where('permission_id', $permissionId)
                    ->exists();
                
                if (!$exists) {
                    $assignment = RolePermission::create([
                        'role_id' => $request->role_id,
                        'permission_id' => $permissionId,
                        'constraints' => $request->constraints
                    ]);
                    $created[] = $assignment;
                } else {
                    $skipped[] = $permissionId;
                }
            }
            
            // Update role permissions array
            if ($role) {
                $permissions = Permission::whereIn('id', $request->permission_ids)->pluck('name')->toArray();
                $existingPermissions = $role->permissions ?? [];
                $role->update([
                    'permissions' => array_unique(array_merge($existingPermissions, $permissions))
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Bulk assignment completed',
                'data' => [
                    'created' => count($created),
                    'skipped' => count($skipped),
                    'total_requested' => count($request->permission_ids)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk assign permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk remove permissions from role.
     */
    public function bulkRemove(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|exists:roles,id',
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $role = Role::find($request->role_id);
            $deleted = RolePermission::where('role_id', $request->role_id)
                ->whereIn('permission_id', $request->permission_ids)
                ->delete();
            
            // Update role permissions array
            if ($role && $deleted > 0) {
                $permissionsToRemove = Permission::whereIn('id', $request->permission_ids)
                    ->pluck('name')
                    ->toArray();
                
                $existingPermissions = $role->permissions ?? [];
                $remainingPermissions = array_filter($existingPermissions, function($perm) use ($permissionsToRemove) {
                    return !in_array($perm, $permissionsToRemove);
                });
                
                $role->update(['permissions' => array_values($remainingPermissions)]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Bulk removal completed',
                'data' => [
                    'deleted' => $deleted,
                    'total_requested' => count($request->permission_ids)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk remove permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if role has permission.
     */
    public function checkPermission(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $hasPermission = RolePermission::where('role_id', $request->role_id)
                ->where('permission_id', $request->permission_id)
                ->exists();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'has_permission' => $hasPermission,
                    'role_id' => $request->role_id,
                    'permission_id' => $request->permission_id
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get permissions for role.
     */
    public function rolePermissions($roleId): JsonResponse
    {
        try {
            $permissions = RolePermission::where('role_id', $roleId)
                ->with(['permission'])
                ->paginate(50);
            
            return response()->json([
                'success' => true,
                'data' => $permissions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch role permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get roles for permission.
     */
    public function permissionRoles($permissionId): JsonResponse
    {
        try {
            $roles = RolePermission::where('permission_id', $permissionId)
                ->with(['role'])
                ->paginate(50);
            
            return response()->json([
                'success' => true,
                'data' => $roles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch permission roles',
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
            $totalAssignments = RolePermission::count();
            $rolesWithPermissions = Role::has('permissions')->count();
            $rolesWithoutPermissions = Role::doesntHave('permissions')->count();
            
            $avgPermissionsPerRole = Role::withCount('permissions')->get()->avg('permissions_count');
            $maxPermissionsRole = Role::withCount('permissions')->orderBy('permissions_count', 'desc')->first();
            $minPermissionsRole = Role::withCount('permissions')->orderBy('permissions_count', 'asc')->first();
            
            $permissionsWithRoles = Permission::has('roles')->count();
            $permissionsWithoutRoles = Permission::doesntHave('roles')->count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total_assignments' => $totalAssignments,
                    'roles_with_permissions' => $rolesWithPermissions,
                    'roles_without_permissions' => $rolesWithoutPermissions,
                    'average_permissions_per_role' => round($avgPermissionsPerRole, 2),
                    'role_with_most_permissions' => $maxPermissionsRole ? [
                        'role_id' => $maxPermissionsRole->id,
                        'role_name' => $maxPermissionsRole->name,
                        'permissions_count' => $maxPermissionsRole->permissions_count
                    ] : null,
                    'role_with_least_permissions' => $minPermissionsRole ? [
                        'role_id' => $minPermissionsRole->id,
                        'role_name' => $minPermissionsRole->name,
                        'permissions_count' => $minPermissionsRole->permissions_count
                    ] : null,
                    'permissions_with_roles' => $permissionsWithRoles,
                    'permissions_without_roles' => $permissionsWithoutRoles
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
     * Sync all permissions for a role.
     */
    public function syncRolePermissions(Request $request, $roleId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id',
            'constraints' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $role = Role::findOrFail($roleId);
            
            // Delete all existing assignments
            RolePermission::where('role_id', $roleId)->delete();
            
            // Create new assignments
            $assignments = [];
            foreach ($request->permission_ids as $permissionId) {
                $assignments[] = RolePermission::create([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                    'constraints' => $request->constraints
                ]);
            }
            
            // Update role permissions array
            $permissionNames = Permission::whereIn('id', $request->permission_ids)->pluck('name')->toArray();
            $role->update(['permissions' => $permissionNames]);
            
            return response()->json([
                'success' => true,
                'message' => 'Role permissions synchronized successfully',
                'data' => [
                    'role_id' => $roleId,
                    'permissions_assigned' => count($assignments),
                    'permissions_list' => $permissionNames
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync role permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}