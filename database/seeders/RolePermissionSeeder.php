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
        // Сбрасываем кэш прав доступа
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

            // Технический партнёр (Новое)
            'manage tech requests', 'view tech dashboard',

            // Рекламный партнёр (Новое)
            'manage ad campaigns', 'view ad analytics',

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

        // 4. Владелец бизнеса
        Role::firstOrCreate(['name' => 'business'])
            ->syncPermissions(['manage own place', 'create news', 'view own analytics']);

        // 5. Технический партнёр (НОВОЕ)
        // Может управлять своими заявками и имеет базовые права бизнеса, если у него есть своё место
        Role::firstOrCreate(['name' => 'tech_partner'])
            ->syncPermissions(['manage tech requests', 'view tech dashboard', 'manage own place']);

        // 6. Рекламный партнёр (НОВОЕ)
        // Может управлять рекламными кампаниями и имеет базовые права бизнеса
        Role::firstOrCreate(['name' => 'ad_partner'])
            ->syncPermissions(['manage ad campaigns', 'view ad analytics', 'manage own place']);

        // 7. Пользователь / Турист (базовая роль)
        Role::firstOrCreate(['name' => 'user'])
            ->syncPermissions([]); // Пока без специальных прав, просто для идентификации

        // 8. Куратор (заготовка)
        Role::firstOrCreate(['name' => 'curator'])
            ->syncPermissions(['view admin', 'manage investor requests', 'manage curator places']);
    }
}
