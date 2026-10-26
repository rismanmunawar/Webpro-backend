<?php

namespace App\Http\Controllers\Api\Authorization;

use App\Http\Controllers\Controller;
use App\Models\Authorization\User;
use App\Services\Authorization\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Display a listing of users with search and filter.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'role' => $request->input('role'),
            'department' => $request->input('department'),
            'per_page' => $request->input('per_page', 10),
            'sort_by' => $request->input('sort_by', 'id'),
            'sort_order' => $request->input('sort_order', 'desc'),
        ];

        $users = $this->userService->getUsers($filters);

        return response()->json([
            'status' => 'success',
            'data' => $users,
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'in:Active,Inactive,Suspended'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user = $this->userService->createUser($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => [
                'user' => $user,
            ],
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $user->load('roles.permissions'),
            ],
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['sometimes', 'required', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'required', 'string', 'in:Active,Inactive,Suspended'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $updatedUser = $this->userService->updateUser($user, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully',
            'data' => [
                'user' => $updatedUser,
            ],
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): JsonResponse
    {
        if (auth()->id() === $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot delete your own account.',
            ], 403);
        }

        $user->tokens()->delete();
        $this->userService->deleteUser($user);

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully',
        ]);
    }
}
