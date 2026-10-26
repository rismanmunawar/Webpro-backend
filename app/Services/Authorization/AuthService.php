<?php

namespace App\Services\Authorization;

use App\Models\Authorization\Role;
use App\Models\Authorization\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Authenticate user and create Sanctum access token.
     *
     * @throws ValidationException
     */
    public function login(string $email, string $password, ?string $deviceName = null): array
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        if ($user->status === 'Suspended') {
            throw ValidationException::withMessages([
                'email' => ['Your account has been suspended. Please contact the administrator.'],
            ]);
        }

        // Update last activity timestamp
        $user->last_active_at = now();
        $user->save();

        // Generate Sanctum plain text token
        $token = $user->createToken($deviceName ?? 'webpro-browser')->plainTextToken;

        return [
            'user' => $user->load('roles.permissions'),
            'token' => $token,
        ];
    }

    /**
     * Register a new user account with default Spatie role.
     */
    public function register(array $data): array
    {
        $words = explode(' ', trim($data['name']));
        $avatar = count($words) >= 2
            ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
            : strtoupper(substr($data['name'], 0, 2));

        $roleName = $data['role'] ?? 'Editor';

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $roleName,
            'avatar' => $avatar,
            'department' => $data['department'] ?? 'General',
            'status' => 'Active',
            'phone' => $data['phone'] ?? null,
            'last_active_at' => now(),
        ]);

        // Assign Spatie role
        if (Role::where('name', $roleName)->exists()) {
            $user->syncRoles([$roleName]);
        }

        $token = $user->createToken('webpro-browser')->plainTextToken;

        return [
            'user' => $user->load('roles.permissions'),
            'token' => $token,
        ];
    }

    /**
     * Revoke the user's current session token.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    /**
     * Revoke all tokens across all sessions/devices for this user.
     */
    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Update the authenticated user's profile.
     */
    public function updateProfile(User $user, array $data): User
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

        if (array_key_exists('phone', $data)) {
            $user->phone = $data['phone'];
        }

        if (array_key_exists('department', $data)) {
            $user->department = $data['department'];
        }

        $user->save();

        return $user->load('roles.permissions');
    }
}
