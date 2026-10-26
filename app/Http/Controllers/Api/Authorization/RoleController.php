<?php

namespace App\Http\Controllers\Api\Authorization;

use App\Http\Controllers\Controller;
use App\Models\Authorization\Role;
use App\Services\Authorization\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(
        protected RoleService $roleService
    ) {}

    /**
     * Display a listing of roles with permissions and user counts.
     */
    public function index(): JsonResponse
    {
        $roles = $this->roleService->getAllRoles();

        return response()->json([
            'status' => 'success',
            'data' => $roles,
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = $this->roleService->createRole($validated['name'], $validated['permissions'] ?? []);

        return response()->json([
            'status' => 'success',
            'message' => 'Role created successfully',
            'data' => [
                'role' => $role,
            ],
        ], 201);
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'role' => $role->load(['permissions', 'users:id,name,email,avatar,role']),
            ],
        ]);
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        $rules = [
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];

        if (! $role->isProtected() && $request->has('name')) {
            $rules['name'] = [
                'required',
                'string',
                'max:50',
                \Illuminate\Validation\Rule::unique('roles', 'name')->ignore($role->id),
            ];
        }

        $validated = $request->validate($rules);

        $updatedRole = $this->roleService->updateRole(
            $role,
            $validated['name'] ?? null,
            $validated['permissions'] ?? null
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Role updated successfully',
            'data' => [
                'role' => $updatedRole,
            ],
        ]);
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role): JsonResponse
    {
        $this->roleService->deleteRole($role);

        return response()->json([
            'status' => 'success',
            'message' => 'Role deleted successfully',
        ]);
    }

    /**
     * Sync permissions to a role.
     */
    public function syncPermissions(Request $request, Role $role): JsonResponse
    {
        $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $syncedRole = $this->roleService->syncPermissions($role, $request->permissions);

        return response()->json([
            'status' => 'success',
            'message' => 'Permissions synced successfully',
            'data' => [
                'role' => $syncedRole,
            ],
        ]);
    }
}
