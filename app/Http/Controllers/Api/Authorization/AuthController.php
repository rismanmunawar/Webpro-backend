<?php

namespace App\Http\Controllers\Api\Authorization;

use App\Http\Controllers\Controller;
use App\Services\Authorization\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Register a new user and generate a Sanctum API token.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['nullable', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $result = $this->authService->register($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'data' => [
                'user' => $result['user'],
                'token' => $result['token'],
                'token_type' => 'Bearer',
            ],
        ], 201);
    }

    /**
     * Authenticate user and issue a new Sanctum API token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string'],
        ]);

        $deviceName = $request->device_name ?: ($request->header('User-Agent') ?: 'webpro-browser');
        $result = $this->authService->login($request->email, $request->password, $deviceName);

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'user' => $result['user'],
                'token' => $result['token'],
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * Get authenticated user profile with roles and permissions.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('roles.permissions');

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $user,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ],
        ]);
    }

    /**
     * Update authenticated user profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'avatar' => ['nullable', 'string', 'max:255'],
            'current_password' => ['nullable', 'required_with:new_password', 'current_password'],
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (! empty($validated['new_password'])) {
            $validated['password'] = $validated['new_password'];
        }

        $user = $this->authService->updateProfile($request->user(), $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => $user,
            ],
        ]);
    }

    /**
     * Log out current device (revoke current token).
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out and token invalidated',
        ]);
    }

    /**
     * Log out from all devices (revoke all tokens).
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $this->authService->logoutAll($request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out from all devices and all tokens invalidated',
        ]);
    }
}
