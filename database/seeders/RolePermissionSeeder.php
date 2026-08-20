<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'view admin', 'manage settings',
            'manage users', 'assign roles',
            'moderate places', 'moderate news', 'moderate events', 'moderate reviews',
            'publish posts', 'manage ads',
            'manage own place', 'create news', 'view own analytics',
        ];
        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        Role::firstOrCreate(['name' => 'admin'])
            ->syncPermissions($permissions);

        Role::firstOrCreate(['name' => 'moderator'])
            ->syncPermissions(['view admin', 'moderate places', 'moderate news', 'moderate events', 'moderate reviews']);

        Role::firstOrCreate(['name' => 'editor'])
            ->syncPermissions(['view admin', 'publish posts', 'moderate events']);

        Role::firstOrCreate(['name' => 'business'])
            ->syncPermissions(['manage own place', 'create news', 'view own analytics']);
    }
}
