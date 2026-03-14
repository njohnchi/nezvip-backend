<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view users',
            'create users',
            'edit users',
            'delete users',
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'view permissions',
            'assign permissions',
            'view diagnostics',
            'manage diagnostics',
            'view submissions',
            'manage submissions',
            'view insights',
            'manage insights',
            'manage careers',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        Role::firstOrCreate(['name' => 'User']);

        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->syncPermissions([
            'view users',
            'create users',
            'edit users',
            'view roles',
            'view permissions',
            'view diagnostics',
            'manage diagnostics',
            'view submissions',
            'manage submissions',
            'view insights',
            'manage insights',
            'manage careers',
        ]);

        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        // Super Admin gets all permissions via Gate::before in AppServiceProvider

        // Create or update Super Admin user
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@nezvip.com'],
            [
                'name' => 'Nezvip Admin',
                'password' => 'password', // Change this in production!
            ]
        );

        if (! $superAdmin->hasRole($superAdminRole)) {
            $superAdmin->assignRole($superAdminRole);
        }
    }
}
