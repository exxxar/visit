<?php

namespace Database\Seeders;

use App\Enums\EventType;
use App\Enums\ModerationStatus;
use App\Enums\PostStatus;
use App\Models\Event;
use App\Models\Place;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $editor = User::where('email', 'editor@code-novorossii.ru')->first();
        $admin = User::where('email', 'admin@code-novorossii.ru')->first();

        // Получаем места для привязки
        $delMar = Place::where('slug', 'del-mar')->first();
        $neonBar = Place::where('slug', 'neon-bar')->first();
        $coffeeRoom = Place::where('slug', 'coffee-room')->first();
        $gastroBar = Place::where('slug', 'gastro-bar')->first();
        $planetarium = Place::where('slug', 'planetarium')->first();

        // ===== СОБЫТИЯ =====
        Event::firstOrCreate(['slug' => 'simfonicheskaya-noch'], [
            'title' => 'Симфоническая ночь под открытым небом',
            'type' => EventType::Concert,
            'starts_at' => now()->addDays(2)->setTime(19, 0),
            'place_id' => $delMar?->id,
            'status' => ModerationStatus::Approved,
            'image' => 'assets/park.jpg',
            'description' => 'Открытый концерт при свечах. Классическая музыка в исполнении лучших оркестров города под звездным небом.',
        ]);

        Event::firstOrCreate(['slug' => 'neon-night-8'], [
            'title' => 'NEON NIGHT · Vol. 8',
            'type' => EventType::Party,
            'starts_at' => now()->addDays(3)->setTime(22, 0),
            'place_id' => $neonBar?->id,
            'status' => ModerationStatus::Approved,
            'image' => 'assets/bar.jpg',
            'description' => 'Главная техно-вечеринка месяца. Специальные гости из Москвы и световое шоу нового поколения.',
        ]);

        Event::firstOrCreate(['slug' => 'show-mylnyh-puzyrey'], [
            'title' => 'Большое шоу мыльных пузырей',
            'type' => EventType::Kids,
            'starts_at' => now()->addDays(4)->setTime(11, 0),
            'status' => ModerationStatus::Approved,
            'image' => 'assets/kids.jpg',
            'description' => 'Интерактивное шоу для всей семьи. Гигантские пузыри, мастер-классы и сладкие подарки каждому ребенку.',
        ]);

        // Новое событие
        Event::firstOrCreate(['slug' => 'tech-meetup-donetsk'], [
            'title' => 'Donetsk IT Meetup: Будущее Web3',
            'type' => EventType::Other, // Или добавь свой enum, если есть
            'starts_at' => now()->addDays(7)->setTime(18, 30),
            'place_id' => $coffeeRoom?->id,
            'status' => ModerationStatus::Approved,
            'image' => 'assets/coffee.jpg',
            'description' => 'Нетворкинг для разработчиков, дизайнеров и всех, кто интересуется технологиями. Пицца и кофе за счет организаторов.',
        ]);

        // Новое событие
        Event::firstOrCreate(['slug' => 'master-class-gastro'], [
            'title' => 'Мастер-класс от шеф-повара GASTRO BAR',
            'type' => EventType::Other,
            'starts_at' => now()->addDays(10)->setTime(15, 0),
            'place_id' => $gastroBar?->id,
            'status' => ModerationStatus::Approved,
            'image' => 'assets/restaurant.jpg',
            'description' => 'Научим готовить фирменный стейк и авторский десерт. Количество мест ограничено.',
        ]);


        // ===== РЕДАКЦИОННЫЕ ПОДБОРКИ (POSTS) =====
        $breakfast = Post::firstOrCreate(['slug' => '10-mest-dlya-idealnogo-zavtraka'], [
            'author_id' => $editor?->id,
            'tag' => 'Подборка',
            'status' => PostStatus::Published,
            'title' => '10 мест для идеального завтрака в Донецке',
            'excerpt' => 'Хрустящие круассаны, пышные сырники и спешелти кофе: составляем утреннюю карту города.',
            'cover'   => 'assets/breakfast.jpg',
            'published_at' => now()->subDays(5),
        ]);
        $breakfast->places()->syncWithoutDetaching(
            Place::whereIn('slug', ['coffee-room', 'gastro-bar', 'knigi-i-kofe'])->pluck('id')
        );

        Post::firstOrCreate(['slug' => 'gde-vypit-horoshiy-kofe'], [
            'author_id' => $editor?->id,
            'tag' => 'Кофе',
            'status' => PostStatus::Published,
            'title' => 'Где выпить действительно хороший кофе',
            'excerpt' => 'Три кофейни, с которых стоит начать свое утро, если вы цените качественное зерно.',
            'cover'   => 'assets/coffee.jpg',
            'published_at' => now()->subDays(2),
        ]);

        // Новый пост
        $autoPost = Post::firstOrCreate(['slug' => 'top-5-avtoservisov'], [
            'author_id' => $admin?->id,
            'tag' => 'Авто',
            'status' => PostStatus::Published,
            'title' => 'ТОП-5 автосервисов и детейлингов города',
            'excerpt' => 'Где не обманут с запчастями и сделают вашу машину как новую. Проверено редакцией.',
            'cover'   => 'assets/auto-service.jpg',
            'published_at' => now()->subDay(),
        ]);
        $autoPost->places()->syncWithoutDetaching(
            Place::whereIn('slug', ['titan-detailing', 'auto-profi'])->pluck('id')
        );

        // Новый пост
        $weekendPost = Post::firstOrCreate(['slug' => 'vyhodnye-s-detmi'], [
            'author_id' => $editor?->id,
            'tag' => 'Семья',
            'status' => PostStatus::Published,
            'title' => 'Куда сходить с ребенком в выходные: гид по досугу',
            'excerpt' => 'Планетарий, шоу мыльных пузырей и лучшие детские комнаты. Сохраняйте, чтобы не потерять!',
            'cover'   => 'assets/kids.jpg',
            'published_at' => now()->subHours(12),
        ]);
        $weekendPost->places()->syncWithoutDetaching(
            Place::whereIn('slug', ['planetarium', 'del-mar'])->pluck('id')
        );
    }
}
