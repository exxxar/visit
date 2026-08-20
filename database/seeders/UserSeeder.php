<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // пароль у всех демо-пользователей: password
        $admin = User::firstOrCreate(['email' => 'admin@vizit-donetsk.ru'], [
            'name' => 'Администратор', 'password' => 'password', 'email_verified_at' => now(),
        ]);
        $admin->syncRoles(['admin']);

        $editor = User::firstOrCreate(['email' => 'editor@vizit-donetsk.ru'], [
            'name' => 'Редактор журнала', 'password' => 'password', 'email_verified_at' => now(),
        ]);
        $editor->syncRoles(['editor']);

        $owner = User::firstOrCreate(['email' => 'owner@vizit-donetsk.ru'], [
            'name' => 'Владелец GASTRO BAR', 'password' => 'password', 'email_verified_at' => now(),
        ]);
        $owner->syncRoles(['business']);
    }
}
