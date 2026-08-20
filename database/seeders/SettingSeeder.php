<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::set('hero', [
            'title' => 'ВИЗИТ ДОНЕЦК',
            'sub'   => 'Главный путеводитель по заведениям, услугам и отдыху города',
            'add'   => 'Все лучшие места Донецка — в одном путеводителе.',
        ]);

        Setting::set('counters', ['places' => '1 240', 'categories' => '30', 'pages' => '64']);

        Setting::set('socials', [
            'telegram'  => 'https://t.me/vizit_donetsk',
            'vk'        => 'https://vk.com/vizit_donetsk',
            'instagram' => '#',
        ]);

        Setting::set('contacts', [
            'email'   => 'hello@vizit-donetsk.ru',
            'phone'   => '+7 856 100-00-00',
            'address' => 'Донецк, ул. Артёма, 1',
        ]);
    }
}
