<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Пароль по умолчанию для всех демо-пользователей
     */
    private const DEFAULT_PASSWORD = 'password';

    public function run(): void
    {
        $this->command->info('🚀 Начинаем создание пользователей...');

        // 1. Администратор (полный доступ к системе)
        $admin = User::firstOrCreate(
            ['email' => 'admin@vistit-donetsk.ru'],
            [
                'name' => 'Администратор',
                'password' => Hash::make(self::DEFAULT_PASSWORD),
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['admin']);
        $this->command->info('✅ Администратор создан');

        // 2. Модератор (модерация контента)
        $moderator = User::firstOrCreate(
            ['email' => 'moderator@vistit-donetsk.ru'],
            [
                'name' => 'Модератор Контента',
                'password' => Hash::make(self::DEFAULT_PASSWORD),
                'email_verified_at' => now(),
            ]
        );
        $moderator->syncRoles(['moderator']);
        $this->command->info('✅ Модератор создан');

        // 3. Редактор (публикация постов и событий)
        $editor = User::firstOrCreate(
            ['email' => 'editor@vistit-donetsk.ru'],
            [
                'name' => 'Редактор Журнала',
                'password' => Hash::make(self::DEFAULT_PASSWORD),
                'email_verified_at' => now(),
            ]
        );
        $editor->syncRoles(['editor']);
        $this->command->info('✅ Редактор создан');

        // 4. Владелец бизнеса (управление заведением)
        $owner = User::firstOrCreate(
            ['email' => 'owner@vistit-donetsk.ru'],
            [
                'name' => 'Владелец GASTRO BAR',
                'password' => Hash::make(self::DEFAULT_PASSWORD),
                'email_verified_at' => now(),
            ]
        );
        $owner->syncRoles(['business']);
        $this->command->info('✅ Владелец бизнеса создан');

        // 5. Технический партнёр (разработка и интеграции)
        $techPartner = User::firstOrCreate(
            ['email' => 'tech@vistit-donetsk.ru'],
            [
                'name' => 'Технический Партнёр',
                'password' => Hash::make(self::DEFAULT_PASSWORD),
                'email_verified_at' => now(),
            ]
        );
        $techPartner->syncRoles(['tech_partner']);
        $this->command->info('✅ Технический партнёр создан');

        // 6. Рекламный партнёр (рекламные кампании)
        $adPartner = User::firstOrCreate(
            ['email' => 'ad@vistit-donetsk.ru'],
            [
                'name' => 'Рекламный Партнёр',
                'password' => Hash::make(self::DEFAULT_PASSWORD),
                'email_verified_at' => now(),
            ]
        );
        $adPartner->syncRoles(['ad_partner']);
        $this->command->info('✅ Рекламный партнёр создан');

        // 7. Турист / Обычный пользователь (базовая роль)
        $tourist = User::firstOrCreate(
            ['email' => 'tourist@vistit-donetsk.ru'],
            [
                'name' => 'Иван Турист',
                'password' => Hash::make(self::DEFAULT_PASSWORD),
                'email_verified_at' => now(),
            ]
        );
        $tourist->syncRoles(['user']);
        $this->command->info('✅ Турист создан');

        // 8. Куратор (управление площадками и заявками инвесторов)
        $curator = User::firstOrCreate(
            ['email' => 'curator@vistit-donetsk.ru'],
            [
                'name' => 'Анна Куратор',
                'password' => Hash::make(self::DEFAULT_PASSWORD),
                'email_verified_at' => now(),
            ]
        );
        $curator->syncRoles(['curator']);
        $this->command->info('✅ Куратор создан');

        $this->command->info('🎉 Все пользователи успешно созданы!');
        $this->command->info('📧 Пароль для всех аккаунтов: ' . self::DEFAULT_PASSWORD);
    }
}
