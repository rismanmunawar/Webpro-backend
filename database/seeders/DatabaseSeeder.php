<?php

namespace Database\Seeders;

use App\Models\Authorization\Permission;
use App\Models\Authorization\Role;
use App\Models\Authorization\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with Spatie permissions, roles, and users.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create Permissions
        $permissions = [
            // Users module
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            // Roles module
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            // Permissions module
            'permissions.view',
            'permissions.assign',
            // Analytics & Dashboard
            'dashboard.view',
            'analytics.view',
            // Orders & Commerce
            'orders.view',
            'orders.create',
            'orders.edit',
            'orders.delete',
            // System settings
            'settings.view',
            'settings.manage',
        ];

        foreach ($permissions as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        // 2. Create Spatie Roles
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        $editorRole = Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'web']);
        $editorRole->syncPermissions([
            'dashboard.view',
            'analytics.view',
            'users.view',
            'orders.view',
            'orders.create',
            'orders.edit',
        ]);

        $moderatorRole = Role::firstOrCreate(['name' => 'Moderator', 'guard_name' => 'web']);
        $moderatorRole->syncPermissions([
            'dashboard.view',
            'users.view',
            'orders.view',
        ]);

        $viewerRole = Role::firstOrCreate(['name' => 'Viewer', 'guard_name' => 'web']);
        $viewerRole->syncPermissions([
            'dashboard.view',
            'analytics.view',
        ]);

        // 3. Create Admin Users
        $mainAdmin = User::firstOrCreate(
            ['email' => 'admin@webpro.com'],
            [
                'name' => 'Aigars Silkalns',
                'password' => Hash::make('password123'),
                'role' => 'Admin',
                'avatar' => 'AS',
                'department' => 'Engineering',
                'status' => 'Active',
                'last_active_at' => now(),
            ]
        );
        $mainAdmin->syncRoles(['Admin']);

        $acmeAdmin = User::firstOrCreate(
            ['email' => 'm@example.com'],
            [
                'name' => 'Acme Member',
                'password' => Hash::make('password123'),
                'role' => 'Admin',
                'avatar' => 'AM',
                'department' => 'Management',
                'status' => 'Active',
                'last_active_at' => now(),
            ]
        );
        $acmeAdmin->syncRoles(['Admin']);

        // 4. Sample Team Members
        $sampleUsers = [
            ['name' => 'WebPro User', 'email' => 'user@webpro.com', 'avatar' => 'WU', 'role' => 'Editor', 'department' => 'Product', 'status' => 'Active'],
            ['name' => 'Emma Wilson', 'email' => 'emma@company.com', 'avatar' => 'EW', 'role' => 'Editor', 'department' => 'Marketing', 'status' => 'Active'],
            ['name' => 'James Chen', 'email' => 'james@company.com', 'avatar' => 'JC', 'role' => 'Admin', 'department' => 'Engineering', 'status' => 'Active'],
            ['name' => 'Sofia Garcia', 'email' => 'sofia@company.com', 'avatar' => 'SG', 'role' => 'Moderator', 'department' => 'Support', 'status' => 'Active'],
            ['name' => 'Alex Thompson', 'email' => 'alex@company.com', 'avatar' => 'AT', 'role' => 'Viewer', 'department' => 'Sales', 'status' => 'Active'],
            ['name' => 'Maria Santos', 'email' => 'maria@company.com', 'avatar' => 'MS', 'role' => 'Editor', 'department' => 'Design', 'status' => 'Active'],
            ['name' => 'David Kim', 'email' => 'david@company.com', 'avatar' => 'DK', 'role' => 'Viewer', 'department' => 'Finance', 'status' => 'Inactive'],
            ['name' => 'Lisa Park', 'email' => 'lisa@company.com', 'avatar' => 'LP', 'role' => 'Editor', 'department' => 'Marketing', 'status' => 'Active'],
            ['name' => 'Ryan Mitchell', 'email' => 'ryan@company.com', 'avatar' => 'RM', 'role' => 'Moderator', 'department' => 'Support', 'status' => 'Active'],
            ['name' => 'Nina Patel', 'email' => 'nina@company.com', 'avatar' => 'NP', 'role' => 'Admin', 'department' => 'Engineering', 'status' => 'Active'],
        ];

        foreach ($sampleUsers as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password123'),
                    'role' => $userData['role'],
                    'avatar' => $userData['avatar'],
                    'department' => $userData['department'],
                    'status' => $userData['status'],
                    'last_active_at' => now()->subMinutes(rand(5, 300)),
                ]
            );
            $user->syncRoles([$userData['role']]);
        }
    }
}
