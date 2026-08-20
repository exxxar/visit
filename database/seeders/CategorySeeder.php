<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            'food'    => ['Еда', '🍽', '#ff8a3c', ['Рестораны и гастробары', 'Пиццерии', 'Бургерные', 'Суши и паназиатская кухня', 'Шаурмичные и стритфуд']],
            'coffee'  => ['Кофе', '☕', '#a3e635', ['Кафе и кондитерские', 'Пекарни и булочные', 'Мороженое и десерты']],
            'bars'    => ['Бары', '🍸', '#f050e0', ['Пабы и пивные', 'Винные бары и кальянные', 'Ночные клубы и караоке']],
            'beauty'  => ['Красота', '💆', '#8b5cf6', ['Салоны красоты', 'Барбершопы', 'SPA и массаж', 'Ногтевой сервис']],
            'sport'   => ['Спорт', '🏋', '#22d3ee', ['Фитнес-клубы', 'Йога и пилатес']],
            'auto'    => ['Авто', '🚗', '#4f7dff', ['Автосервисы', 'Автомойки', 'Детейлинг', 'АЗС и зарядные станции']],
            'leisure' => ['Отдых', '🎭', '#ff5c7a', ['Отели и апартаменты', 'Кинотеатры', 'Театры и концертные площадки', 'Детские центры']],
            'med'     => ['Здоровье', '🩺', '#34d399', ['Медицинские центры', 'Аптеки', 'Ветеринарные клиники']],
            'shop'    => ['Покупки', '🛍', '#facc15', ['Цветочные магазины', 'Подарки и сувениры', 'Бутики']],
        ];

        foreach ($tree as $slug => [$name, $icon, $color, $children]) {
            $root = Category::firstOrCreate(['slug' => $slug], [
                'name' => $name, 'icon' => $icon, 'color' => $color,
            ]);
            foreach ($children as $i => $child) {
                Category::firstOrCreate(['slug' => \Str::slug($child)], [
                    'parent_id' => $root->id, 'name' => $child, 'sort' => $i,
                    'icon' => $icon, 'color' => $color,
                ]);
            }
        }
    }
}
