<?php

namespace App\Services\Authorization;

use App\Models\Authorization\Role;
use App\Models\Authorization\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Get paginated or filtered list of users with Spatie roles and permissions.
     */
    public function getUsers(array $filters = []): LengthAwarePaginator
    {
        $query = User::with('roles:id,name')
            ->select(['id', 'name', 'email', 'role', 'avatar', 'department', 'status', 'phone', 'last_active_at', 'created_at'])
            ->latest('id');

        // Search query filter
        if (! empty($filters['search'])) {
            $search = strtolower(trim($filters['search']));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%");
            });
        }

        // Status filter
        if (! empty($filters['status']) && $filters['status'] !== 'All') {
            $query->where('status', $filters['status']);
        }

        // Role filter
        if (! empty($filters['role']) && $filters['role'] !== 'All') {
            $query->where('role', $filters['role']);
        }

        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 15;

        return $query->paginate($perPage);
    }

    /**
     * Find a user by ID.
     */
    public function getUserById(int $id): User
    {
        return User::with('roles.permissions')->findOrFail($id);
    }

    /**
     * Create a new user and sync their Spatie role.
     */
    public function createUser(array $data): User
    {
        $words = explode(' ', trim($data['name']));
        $avatar = count($words) >= 2
            ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
            : strtoupper(substr($data['name'], 0, 2));

        $roleName = $data['role'] ?? 'Editor';

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'] ?? 'password123'),
            'role' => $roleName,
            'avatar' => $avatar,
            'department' => $data['department'] ?? 'General',
            'status' => $data['status'] ?? 'Active',
            'phone' => $data['phone'] ?? null,
            'last_active_at' => now(),
        ]);

        // Sync Spatie role
        if (Role::where('name', $roleName)->exists()) {
            $user->syncRoles([$roleName]);
        }

        return $user->load('roles.permissions');
    }

    /**
     * Update an existing user and optionally sync new role / password.
     */
    public function updateUser(User $user, array $data): User
    {
        if (isset($data['name'])) {
            $user->name = $data['name'];
            $words = explode(' ', trim($data['name']));
            $user->avatar = count($words) >= 2
                ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
                : strtoupper(substr($data['name'], 0, 2));
        }

        if (isset($data['email'])) {
            $user->email = $data['email'];
        }

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        if (isset($data['role'])) {
            $user->role = $data['role'];
            if (Role::where('name', $data['role'])->exists()) {
                $user->syncRoles([$data['role']]);
            }
        }

        if (array_key_exists('department', $data)) {
            $user->department = $data['department'];
        }

        if (isset($data['status'])) {
            $user->status = $data['status'];
        }

        if (array_key_exists('phone', $data)) {
            $user->phone = $data['phone'];
        }

        $user->save();

        return $user->load('roles.permissions');
    }

    /**
     * Delete user from the database.
     */
    public function deleteUser(User $user): bool
    {
        return (bool) $user->delete();
    }
}
