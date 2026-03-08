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

        // Create permissions for user management
        Permission::create(['name' => 'view users']);
        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'edit users']);
        Permission::create(['name' => 'delete users']);

        // Create permissions for role management
        Permission::create(['name' => 'view roles']);
        Permission::create(['name' => 'create roles']);
        Permission::create(['name' => 'edit roles']);
        Permission::create(['name' => 'delete roles']);

        // Create permissions for permission management
        Permission::create(['name' => 'view permissions']);
        Permission::create(['name' => 'assign permissions']);

        // Create roles and assign permissions
        $userRole = Role::create(['name' => 'User']);
        // Users have basic access, no special permissions

        $adminRole = Role::create(['name' => 'Admin']);
        $adminRole->givePermissionTo([
            'view users',
            'create users',
            'edit users',
            'view roles',
            'view permissions',
        ]);

        $superAdminRole = Role::create(['name' => 'Super Admin']);
        // Super Admin gets all permissions via Gate::before in AppServiceProvider

        // Create a Super Admin user
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@nezvip.com',
            'password' => 'password', // Change this in production!
        ]);
        $superAdmin->assignRole($superAdminRole);
    }
}
