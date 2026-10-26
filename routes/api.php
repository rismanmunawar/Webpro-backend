<?php

use App\Http\Controllers\Api\Authorization\AuthController;
use App\Http\Controllers\Api\Authorization\PermissionController;
use App\Http\Controllers\Api\Authorization\RoleController;
use App\Http\Controllers\Api\Authorization\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Authorization & System
|--------------------------------------------------------------------------
*/

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Public Authentication Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (Laravel Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Current user profile & authentication
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);

    // Authorization: User Management CRUD
    Route::apiResource('users', UserController::class);

    // Authorization: Role Management CRUD & Permission Assignment
    Route::apiResource('roles', RoleController::class);
    Route::post('/roles/{role}/permissions', [RoleController::class, 'syncPermissions']);

    // Authorization: Permission Catalog
    Route::post('/permissions/bulk-generate', [PermissionController::class, 'bulkGenerate']);
    Route::apiResource('permissions', PermissionController::class)->only(['index', 'store', 'update', 'destroy']);
});
