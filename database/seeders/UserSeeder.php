<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // пароль у всех демо-пользователей: password

        // 1. Администратор
        $admin = User::firstOrCreate(['email' => 'admin@code-novorossii.ru'], [
            'name' => 'Администратор',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);
        $admin->syncRoles(['admin']);

        // 2. Редактор журнала
        $editor = User::firstOrCreate(['email' => 'editor@code-novorossii.ru'], [
            'name' => 'Редактор журнала',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);
        $editor->syncRoles(['editor']);

        // 3. Владелец бизнеса (Владелец GASTRO BAR)
        $owner = User::firstOrCreate(['email' => 'owner@code-novorossii.ru'], [
            'name' => 'Владелец GASTRO BAR',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);
        $owner->syncRoles(['business']);

        // 4. Пользователь / Турист (обычный житель или гость города)
        $tourist = User::firstOrCreate(['email' => 'user@code-novorossii.ru'], [
            'name' => 'Иван Турист',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);
        $tourist->syncRoles(['user']);

        // 5. Куратор (заготовка на будущее)
        $curator = User::firstOrCreate(['email' => 'curator@code-novorossii.ru'], [
            'name' => 'Анна Куратор',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);
        $curator->syncRoles(['curator']);
    }
}
