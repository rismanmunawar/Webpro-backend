<?php

namespace App\Models\Authorization;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'module',
        'action',
    ];

    /**
     * Get the module name inferred from the permission name (e.g. 'users.view' -> 'Users').
     */
    public function getModuleAttribute(): string
    {
        $parts = explode('.', $this->name);

        return count($parts) > 1 ? ucfirst($parts[0]) : 'General';
    }

    /**
     * Get the action part inferred from the permission name (e.g. 'users.view' -> 'view').
     */
    public function getActionAttribute(): string
    {
        $parts = explode('.', $this->name);

        return count($parts) > 1 ? $parts[1] : $this->name;
    }
}
