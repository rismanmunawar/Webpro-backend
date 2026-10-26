<?php

namespace App\Services\Authorization;

use App\Models\Authorization\Permission;

class PermissionService
{
    /**
     * Get all permissions and grouped by module.
     */
    public function getAllPermissions(): array
    {
        $permissions = Permission::all()->map(function ($perm) {
            return [
                'id' => $perm->id,
                'name' => $perm->name,
                'module' => $perm->module,
                'action' => $perm->action,
                'guard_name' => $perm->guard_name,
                'created_at' => $perm->created_at,
            ];
        });

        $grouped = $permissions->groupBy('module');

        return [
            'all' => $permissions,
            'grouped' => $grouped,
        ];
    }

    /**
     * Create a new permission.
     */
    public function createPermission(string $name): Permission
    {
        return Permission::create([
            'name' => strtolower(trim($name)),
            'guard_name' => 'web',
        ]);
    }

    /**
     * Update an existing permission.
     */
    public function updatePermission(Permission $permission, string $name): Permission
    {
        $permission->name = strtolower(trim($name));
        $permission->save();

        return $permission;
    }

    /**
     * Delete a permission.
     */
    public function deletePermission(Permission $permission): bool
    {
        return (bool) $permission->delete();
    }

    /**
     * Bulk generate CRUD permissions for a specific module.
     *
     * @param string $module
     * @param array<string> $actions
     * @param string $guard
     * @return array<array<string, mixed>>
     */
    public function bulkGenerateCrud(string $module, array $actions, string $guard = 'web'): array
    {
        $cleanModule = strtolower(trim($module));
        $created = [];

        foreach ($actions as $action) {
            $cleanAction = strtolower(trim($action));
            if (empty($cleanAction)) {
                continue;
            }

            $permissionName = "{$cleanModule}.{$cleanAction}";

            $perm = Permission::firstOrCreate(
                ['name' => $permissionName],
                ['guard_name' => $guard]
            );

            $created[] = [
                'id' => $perm->id,
                'name' => $perm->name,
                'module' => $perm->module,
                'action' => $perm->action,
                'guard_name' => $perm->guard_name,
            ];
        }

        // Flush Spatie permission cache
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return $created;
    }
}
