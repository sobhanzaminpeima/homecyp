<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view properties', 'create properties', 'edit properties', 'delete properties',
            'view projects', 'create projects', 'edit projects', 'delete projects',
            'view leads', 'create leads', 'edit leads', 'delete leads',
            'view agents', 'create agents', 'edit agents', 'delete agents',
            'manage settings', 'manage users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $agent = Role::firstOrCreate(['name' => 'agent']);
        $editor = Role::firstOrCreate(['name' => 'editor']);
        $customer = Role::firstOrCreate(['name' => 'customer']);

        $superAdmin->givePermissionTo(Permission::all());
        $admin->givePermissionTo([
            'view properties', 'create properties', 'edit properties',
            'view projects', 'create projects', 'edit projects',
            'view leads', 'create leads', 'edit leads',
            'view agents', 'create agents', 'edit agents',
        ]);
        $agent->givePermissionTo([
            'view properties', 'create properties', 'edit properties',
            'view leads', 'create leads',
        ]);
        $editor->givePermissionTo([
            'view properties', 'edit properties',
            'view projects', 'edit projects',
        ]);
    }
}
