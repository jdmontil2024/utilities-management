<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Permission::query();
            
            // Filter by group
            if ($request->has('group')) {
                $query->where('group', $request->group);
            }
            
            // Filter by action
            if ($request->has('action')) {
                $query->where('action', $request->action);
            }
            
            // Search by name or display name
            if ($request->has('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('display_name', 'like', '%' . $request->search . '%');
                });
            }
            
            // Sort
            $sortBy = $request->get('sort_by', 'group');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder)->orderBy('name', 'asc');
            
            $permissions = $query->paginate($request->get('per_page', 50));
            
            return response()->json([
                'success' => true,
                'data' => $permissions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created permission.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:permissions',
            'display_name' => 'nullable|string|max:255',
            'group' => 'required|string|max:100',
            'action' => 'required|string|max:50',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $permission = Permission::create([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'group' => $request->group,
                'action' => $request->action,
                'description' => $request->description
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Permission created successfully',
                'data' => $permission
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified permission.
     */
    public function show(Permission $permission): JsonResponse
    {
        try {
            $permission->load(['roles', 'users']);
            return response()->json([
                'success' => true,
                'data' => $permission
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified permission.
     */
    public function update(Request $request, Permission $permission): JsonResponse
    {
        // Prevent modification of system permissions
        if ($permission->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'System permissions cannot be modified'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255|unique:permissions,name,' . $permission->id,
            'display_name' => 'nullable|string|max:255',
            'group' => 'sometimes|string|max:100',
            'action' => 'sometimes|string|max:50',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $permission->update($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Permission updated successfully',
                'data' => $permission
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified permission.
     */
    public function destroy(Permission $permission): JsonResponse
    {
        // Prevent deletion of system permissions
        if ($permission->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'System permissions cannot be deleted'
            ], 403);
        }

        // Check if permission is assigned to any roles
        if ($permission->roles()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete permission assigned to roles. Remove from roles first.'
            ], 400);
        }

        // Check if permission is assigned to any users
        if ($permission->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete permission assigned to users. Remove from users first.'
            ], 400);
        }

        try {
            $permission->delete();
            return response()->json([
                'success' => true,
                'message' => 'Permission deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get permission groups.
     */
    public function groups(): JsonResponse
    {
        try {
            $groups = Permission::select('group')
                ->distinct()
                ->orderBy('group')
                ->pluck('group');
            
            return response()->json([
                'success' => true,
                'data' => $groups
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch permission groups',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get permission actions.
     */
    public function actions(): JsonResponse
    {
        try {
            $actions = Permission::select('action')
                ->distinct()
                ->orderBy('action')
                ->pluck('action');
            
            return response()->json([
                'success' => true,
                'data' => $actions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch permission actions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get permissions by group.
     */
    public function byGroup($group): JsonResponse
    {
        try {
            $permissions = Permission::where('group', $group)
                ->orderBy('action')
                ->orderBy('name')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $permissions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch permissions by group',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get roles with this permission.
     */
    public function roles(Permission $permission): JsonResponse
    {
        try {
            $roles = $permission->roles()->paginate(20);
            return response()->json([
                'success' => true,
                'data' => $roles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch roles with permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get users with this permission.
     */
    public function users(Permission $permission): JsonResponse
    {
        try {
            $users = $permission->users()->paginate(20);
            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users with permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get permission statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $total = Permission::count();
            $system = Permission::where('is_system', true)->count();
            $custom = Permission::where('is_system', false)->count();
            
            $groupCounts = Permission::selectRaw('group, COUNT(*) as count')
                ->groupBy('group')
                ->orderBy('group')
                ->get();
            
            $actionCounts = Permission::selectRaw('action, COUNT(*) as count')
                ->groupBy('action')
                ->orderBy('action')
                ->get();
            
            $mostUsed = Permission::withCount('roles')
                ->orderBy('roles_count', 'desc')
                ->limit(10)
                ->get();
            
            $leastUsed = Permission::withCount('roles')
                ->orderBy('roles_count', 'asc')
                ->limit(10)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total_permissions' => $total,
                    'system_permissions' => $system,
                    'custom_permissions' => $custom,
                    'by_group' => $groupCounts,
                    'by_action' => $actionCounts,
                    'most_used' => $mostUsed,
                    'least_used' => $leastUsed
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get permission statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batch create permissions.
     */
    public function batchCreate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*.name' => 'required|string|max:255',
            'permissions.*.display_name' => 'nullable|string|max:255',
            'permissions.*.group' => 'required|string|max:100',
            'permissions.*.action' => 'required|string|max:50',
            'permissions.*.description' => 'nullable|string'
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
            
            foreach ($request->permissions as $permissionData) {
                // Check if permission already exists
                $exists = Permission::where('name', $permissionData['name'])->exists();
                
                if (!$exists) {
                    $permission = Permission::create([
                        'name' => $permissionData['name'],
                        'display_name' => $permissionData['display_name'] ?? null,
                        'group' => $permissionData['group'],
                        'action' => $permissionData['action'],
                        'description' => $permissionData['description'] ?? null
                    ]);
                    $created[] = $permission;
                } else {
                    $skipped[] = $permissionData['name'];
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Batch permissions processed',
                'data' => [
                    'created' => $created,
                    'created_count' => count($created),
                    'skipped' => $skipped,
                    'skipped_count' => count($skipped)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to batch create permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all permissions grouped by group.
     */
    public function grouped(): JsonResponse
    {
        try {
            $permissions = Permission::orderBy('group')
                ->orderBy('action')
                ->orderBy('name')
                ->get()
                ->groupBy('group');
            
            return response()->json([
                'success' => true,
                'data' => $permissions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch grouped permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}