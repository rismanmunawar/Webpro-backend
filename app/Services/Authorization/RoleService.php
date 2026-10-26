<?php

namespace App\Services\Authorization;

use App\Models\Authorization\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class RoleService
{
    /**
     * Get all Spatie roles with their assigned permissions and user counts.
     */
    public function getAllRoles(): Collection
    {
        return Role::with('permissions:id,name,guard_name')
            ->withCount('users')
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Find a role by ID.
     */
    public function getRoleById(int $id): Role
    {
        return Role::with('permissions')->withCount('users')->findOrFail($id);
    }

    /**
     * Create a new Spatie role and optionally assign permissions.
     */
    public function createRole(string $name, array $permissions = []): Role
    {
        $role = Role::create([
            'name' => trim($name),
            'guard_name' => 'web',
        ]);

        if (! empty($permissions)) {
            $role->syncPermissions($permissions);
        }

        return $role->load('permissions');
    }

    /**
     * Update role name and sync its permissions.
     */
    public function updateRole(Role $role, ?string $name = null, ?array $permissions = null): Role
    {
        if ($name !== null && ! $role->isProtected()) {
            $role->name = trim($name);
            $role->save();
        }

        if ($permissions !== null) {
            $role->syncPermissions($permissions);
        }

        return $role->load('permissions');
    }

    /**
     * Delete role (with protection for default Admin role).
     *
     * @throws ValidationException
     */
    public function deleteRole(Role $role): bool
    {
        if ($role->isProtected()) {
            throw ValidationException::withMessages([
                'role' => ['The default Admin role is protected and cannot be deleted.'],
            ]);
        }

        return (bool) $role->delete();
    }

    /**
     * Sync permissions to the specified role.
     */
    public function syncPermissions(Role $role, array $permissions): Role
    {
        $role->syncPermissions($permissions);

        return $role->load('permissions');
    }
}
