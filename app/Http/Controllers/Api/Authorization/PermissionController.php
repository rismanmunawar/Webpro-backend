<?php

namespace App\Http\Controllers\Api\Authorization;

use App\Http\Controllers\Controller;
use App\Models\Authorization\Permission;
use App\Services\Authorization\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct(
        protected PermissionService $permissionService
    ) {}

    /**
     * Display a listing of permissions, grouped by module.
     */
    public function index(): JsonResponse
    {
        $result = $this->permissionService->getAllPermissions();

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }

    /**
     * Store a newly created permission.
     */
    public function store(Request $request): JsonResponse
    {
        $name = $request->input('name');
        if (empty($name) && $request->filled('module') && $request->filled('action')) {
            $name = strtolower(trim($request->input('module'))) . '.' . strtolower(trim($request->input('action')));
            $request->merge(['name' => $name]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:permissions,name'],
        ]);

        $permission = $this->permissionService->createPermission($validated['name']);

        return response()->json([
            'status' => 'success',
            'message' => 'Permission created successfully',
            'data' => [
                'permission' => $permission,
            ],
        ], 201);
    }

    /**
     * Bulk generate CRUD permissions for a module.
     */
    public function bulkGenerate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'module' => ['required', 'string', 'max:50'],
            'actions' => ['required', 'array', 'min:1'],
            'actions.*' => ['required', 'string', 'max:30'],
            'guard_name' => ['nullable', 'string', 'max:30'],
        ]);

        $created = $this->permissionService->bulkGenerateCrud(
            $validated['module'],
            $validated['actions'],
            $validated['guard_name'] ?? 'web'
        );

        return response()->json([
            'status' => 'success',
            'message' => count($created) . ' CRUD permissions processed for module ' . ucfirst($validated['module']),
            'data' => [
                'module' => ucfirst($validated['module']),
                'count' => count($created),
                'permissions' => $created,
            ],
        ], 201);
    }

    /**
     * Update the specified permission.
     */
    public function update(Request $request, Permission $permission): JsonResponse
    {
        $name = $request->input('name');
        if (empty($name) && $request->filled('module') && $request->filled('action')) {
            $name = strtolower(trim($request->input('module'))) . '.' . strtolower(trim($request->input('action')));
            $request->merge(['name' => $name]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:permissions,name,' . $permission->id],
        ]);

        $updatedPermission = $this->permissionService->updatePermission($permission, $validated['name']);

        return response()->json([
            'status' => 'success',
            'message' => 'Permission updated successfully',
            'data' => [
                'permission' => [
                    'id' => $updatedPermission->id,
                    'name' => $updatedPermission->name,
                    'module' => $updatedPermission->module,
                    'action' => $updatedPermission->action,
                    'guard_name' => $updatedPermission->guard_name,
                ],
            ],
        ]);
    }

    /**
     * Remove the specified permission.
     */
    public function destroy(Permission $permission): JsonResponse
    {
        $this->permissionService->deletePermission($permission);

        return response()->json([
            'status' => 'success',
            'message' => 'Permission deleted successfully',
        ]);
    }
}
