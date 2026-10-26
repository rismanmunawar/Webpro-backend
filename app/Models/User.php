<?php

namespace App\Models;

use App\Models\Authorization\User as BaseAuthorizationUser;

/**
 * Main application User model extending the Authorization User.
 */
class User extends BaseAuthorizationUser
{
    /**
     * Use BaseAuthorizationUser class for all morph relations (Sanctum, Spatie).
     */
    public function getMorphClass(): string
    {
        return BaseAuthorizationUser::class;
    }
}
