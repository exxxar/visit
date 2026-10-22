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
            // Админка и система
            'view admin', 'manage settings', 'manage users', 'assign roles',

            // Модерация
            'moderate places', 'moderate news', 'moderate events', 'moderate reviews',

            // Контент и реклама
            'publish posts', 'manage ads',

            // Бизнес (Владелец заведения)
            'manage own place', 'create news', 'view own analytics',

            // Куратор (заготовка на будущее)
            'manage investor requests', 'manage curator places',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // 1. Администратор (полный доступ)
        Role::firstOrCreate(['name' => 'admin'])
            ->syncPermissions($permissions);

        // 2. Модератор
        Role::firstOrCreate(['name' => 'moderator'])
            ->syncPermissions(['view admin', 'moderate places', 'moderate news', 'moderate events', 'moderate reviews']);

        // 3. Редактор
        Role::firstOrCreate(['name' => 'editor'])
            ->syncPermissions(['view admin', 'publish posts', 'moderate events']);

        // 4. Владелец бизнеса / представитель организации
        // (уже был, управляет своей карточкой)
        Role::firstOrCreate(['name' => 'business'])
            ->syncPermissions(['manage own place', 'create news', 'view own analytics']);

        // 5. Пользователь / Турист (базовая роль, пока без спец. прав, но полезна для идентификации)
        Role::firstOrCreate(['name' => 'user'])
            ->syncPermissions([]);

        // 6. Куратор (заготовка под будущую логику площадок и заявок инвесторов)
        Role::firstOrCreate(['name' => 'curator'])
            ->syncPermissions(['view admin', 'manage investor requests', 'manage curator places']);
    }
}
