<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Role::with(['permissions']);
            
            // Filter by system roles
            if ($request->has('is_system')) {
                $query->where('is_system', $request->boolean('is_system'));
            }
            
            // Search by name
            if ($request->has('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('display_name', 'like', '%' . $request->search . '%');
                });
            }
            
            // Sort
            $sortBy = $request->get('sort_by', 'name');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);
            
            $roles = $query->paginate($request->get('per_page', 20));
            
            return response()->json([
                'success' => true,
                'data' => $roles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles',
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_system' => 'boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $role = Role::create([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
                'is_system' => $request->is_system ?? false
            ]);
            
            // Attach permissions if provided
            if ($request->has('permissions')) {
                $role->permissions()->attach($request->permissions);
                $role->permissions = $role->permissions()->pluck('name')->toArray();
                $role->save();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Role created successfully',
                'data' => $role->load(['permissions'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role): JsonResponse
    {
        try {
            $role->load(['permissions', 'users']);
            return response()->json([
                'success' => true,
                'data' => $role
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        // Prevent modification of system roles
        if ($role->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'System roles cannot be modified'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255|unique:roles,name,' . $role->id,
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $role->update($request->only(['name', 'display_name', 'description']));
            
            // Update permissions if provided
            if ($request->has('permissions')) {
                $role->permissions()->sync($request->permissions);
                $role->permissions = $role->permissions()->pluck('name')->toArray();
                $role->save();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully',
                'data' => $role->load(['permissions'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role): JsonResponse
    {
        // Prevent deletion of system roles
        if ($role->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'System roles cannot be deleted'
            ], 403);
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete role with assigned users. Remove users first.'
            ], 400);
        }

        try {
            // Detach all permissions
            $role->permissions()->detach();
            $role->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign permissions to role.
     */
    public function assignPermissions(Request $request, Role $role): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $role->permissions()->syncWithoutDetaching($request->permissions);
            $role->permissions = $role->permissions()->pluck('name')->toArray();
            $role->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Permissions assigned successfully',
                'data' => $role->load(['permissions'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove permissions from role.
     */
    public function removePermissions(Request $request, Role $role): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $role->permissions()->detach($request->permissions);
            $role->permissions = $role->permissions()->pluck('name')->toArray();
            $role->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Permissions removed successfully',
                'data' => $role->load(['permissions'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync permissions for role.
     */
    public function syncPermissions(Request $request, Role $role): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $role->permissions()->sync($request->permissions);
            $role->permissions = $role->permissions()->pluck('name')->toArray();
            $role->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Permissions synchronized successfully',
                'data' => $role->load(['permissions'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get role permissions.
     */
    public function permissions(Role $role): JsonResponse
    {
        try {
            $permissions = $role->permissions;
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
     * Get role users.
     */
    public function users(Role $role): JsonResponse
    {
        try {
            $users = $role->users()->paginate(20);
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
     * Get available permissions for role.
     */
    public function availablePermissions(Role $role): JsonResponse
    {
        try {
            $assignedPermissions = $role->permissions()->pluck('permissions.id');
            $availablePermissions = Permission::whereNotIn('id', $assignedPermissions)->get();
            
            return response()->json([
                'success' => true,
                'data' => $availablePermissions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch available permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get default system roles.
     */
    public function systemRoles(): JsonResponse
    {
        try {
            $roles = Role::where('is_system', true)->get();
            return response()->json([
                'success' => true,
                'data' => $roles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch system roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get role statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $total = Role::count();
            $system = Role::where('is_system', true)->count();
            $custom = Role::where('is_system', false)->count();
            
            $rolesWithUsers = Role::has('users')->count();
            $rolesWithoutUsers = Role::doesntHave('users')->count();
            
            $averagePermissions = Role::withCount('permissions')->get()->avg('permissions_count');
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total_roles' => $total,
                    'system_roles' => $system,
                    'custom_roles' => $custom,
                    'roles_with_users' => $rolesWithUsers,
                    'roles_without_users' => $rolesWithoutUsers,
                    'average_permissions_per_role' => round($averagePermissions, 2)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get role statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}