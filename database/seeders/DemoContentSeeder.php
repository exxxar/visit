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
        $editor = User::where('email', 'editor@vizit-donetsk.ru')->first();
        $opera  = Place::where('slug', 'del-mar')->first();

        Event::firstOrCreate(['slug' => 'simfonicheskaya-noch'], [
            'title' => 'Симфоническая ночь под открытым небом', 'type' => EventType::Concert,
            'starts_at' => now()->addDays(2)->setTime(19, 0), 'place_id' => $opera?->id,
            'status' => ModerationStatus::Approved, 'image' => 'assets/park.jpg',
            'description' => 'Открытый концерт при свечах.',
        ]);
        Event::firstOrCreate(['slug' => 'neon-night-8'], [
            'title' => 'NEON NIGHT · Vol. 8', 'type' => EventType::Party,
            'starts_at' => now()->addDays(3)->setTime(22, 0), 'place_id' => Place::where('slug', 'neon-bar')->first()?->id,
            'status' => ModerationStatus::Approved, 'image' => 'assets/bar.jpg',
        ]);
        Event::firstOrCreate(['slug' => 'show-mylnyh-puzyrey'], [
            'title' => 'Большое шоу мыльных пузырей', 'type' => EventType::Kids,
            'starts_at' => now()->addDays(4)->setTime(11, 0),
            'status' => ModerationStatus::Approved, 'image' => 'assets/kids.jpg',
        ]);

        $breakfast = Post::firstOrCreate(['slug' => '10-mest-dlya-idealnogo-zavtraka'], [
            'author_id' => $editor?->id, 'tag' => 'Подборка', 'status' => PostStatus::Published,
            'title' => '10 мест для идеального завтрака',
            'excerpt' => 'Круассаны, сырники и спешелти: утренняя карта города.',
            'cover'   => 'assets/breakfast.jpg', 'published_at' => now()->subDays(2),
        ]);
        $breakfast->places()->syncWithoutDetaching(Place::whereIn('slug', ['coffee-room', 'gastro-bar'])->pluck('id'));

        Post::firstOrCreate(['slug' => 'gde-vypit-horoshiy-kofe'], [
            'author_id' => $editor?->id, 'tag' => 'Кофе', 'status' => PostStatus::Published,
            'title' => 'Где выпить хороший кофе в Донецке',
            'excerpt' => 'Три кофейни, с которых стоит начать утро.',
            'cover'   => 'assets/coffee.jpg', 'published_at' => now()->subDay(),
        ]);
    }
}
