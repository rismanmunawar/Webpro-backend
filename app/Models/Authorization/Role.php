<?php

namespace App\Models\Authorization;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\PermissionRegistrar;

class Role extends SpatieRole
{
    /**
     * Default attributes for the model.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'guard_name' => 'web',
    ];

    /**
     * Check if the role is protected from deletion (e.g. Admin/Super Admin).
     */
    public function isProtected(): bool
    {
        return strtolower($this->name) === 'admin';
    }

    /**
     * A role belongs to some users of the model associated with its guard.
     */
    public function users(): BelongsToMany
    {
        return $this->morphedByMany(
            User::class,
            'model',
            config('permission.table_names.model_has_roles', 'model_has_roles'),
            app(PermissionRegistrar::class)->pivotRole,
            config('permission.column_names.model_morph_key', 'model_id')
        );
    }
}
