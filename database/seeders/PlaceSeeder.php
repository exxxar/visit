<?php

namespace Database\Seeders;

use App\Enums\ModerationStatus;
use App\Enums\ReviewStatus;
use App\Models\Category;
use App\Models\District;
use App\Models\Place;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlaceSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::where('email', 'owner@vizit-donetsk.ru')->first();

        $places = [
            // slug, name, cat, district, address, lat, lng, price, img, rating, owner?
            ['gastro-bar', 'GASTRO BAR', 'food', 'Ворошиловский', 'ул. Артёма, 12', 48.044, 37.804, 2, 'assets/restaurant.jpg', 4.8, true],
            ['coffee-room', 'COFFEE ROOM', 'coffee', 'Ворошиловский', 'бул. Пушкина, 3', 48.062, 37.828, 1, 'assets/coffee.jpg', 4.9, true],
            ['neon-bar', 'NEON BAR', 'bars', 'Ворошиловский', 'ул. Университетская, 21', 48.016, 37.784, 3, 'assets/bar.jpg', 4.7, false],
            ['sakura', 'SAKURA', 'food', 'Киевский', 'пр-т Киевский, 64', 48.036, 37.872, 2, 'assets/sushi.jpg', 4.6, false],
            ['aura-spa', 'AURA SPA', 'beauty', 'Ворошиловский', 'ул. Челюскинцев, 8', 48.064, 37.764, 3, 'assets/spa.jpg', 4.9, false],
            ['powerhouse-gym', 'POWERHOUSE GYM', 'sport', 'Калининский', 'ул. Горбатова, 14', 47.992, 37.852, 2, 'assets/gym.jpg', 4.7, false],
            ['titan-detailing', 'TITAN DETAILING', 'auto', 'Куйбышевский', 'ул. Кирова, 101', 47.988, 37.700, 2, 'assets/car.jpg', 4.9, false],
            ['del-mar', 'DEL MAR', 'leisure', 'Ворошиловский', 'ул. Набережная, 1', 48.030, 37.820, 3, 'assets/hotel.jpg', 4.8, false],
            ['apteka-24', 'АПТЕКА «24»', 'med', 'Ворошиловский', 'ул. Артёма, 40', 48.000, 37.796, 1, 'assets/pharmacy.jpg', 4.5, false],
            ['fleur', 'FLEUR', 'shop', 'Киевский', 'пр-т Киевский, 12', 48.016, 37.844, 2, 'assets/flowers.jpg', 4.8, true],
        ];

        foreach ($places as [$slug, $name, $cat, $district, $address, $lat, $lng, $price, $img, $rating, $hasOwner]) {
            $place = Place::firstOrCreate(['slug' => $slug], [
                'name'              => $name,
                'category_id'       => Category::where('slug', $cat)->first()->id,
                'district_id'       => District::where('name', $district)->first()->id,
                'owner_id'          => $hasOwner ? $owner?->id : null,
                'address'           => $address,
                'lat'               => $lat,
                'lng'               => $lng,
                'price_level'       => $price,
                'short_description' => $name . ' — проверено горожанами.',
                'description'       => 'Атмосферное место в сердце Донецка: продуманный интерьер, внимательный сервис и то, за чем хочется возвращаться.',
                'phone'             => '+7 856 000-00-00',
                'status'            => ModerationStatus::Approved,
                'rating'            => $rating,
                'working_hours'     => [['d' => 'ежедневно', 'from' => '09:00', 'to' => '22:00']],
                'socials'           => ['telegram' => '@' . $slug, 'vk' => 'vk.com/' . $slug],
            ]);

            $place->photos()->firstOrCreate(['is_cover' => true], ['path' => $img, 'sort' => 0]);
        }

        /* отзывы для живой карточки */
        $gastro = Place::where('slug', 'gastro-bar')->first();
        if ($gastro && $gastro->reviews()->count() === 0) {
            $gastro->reviews()->createMany([
                ['author_name' => 'Мария', 'rating' => 5, 'text' => 'Лучший ужин в городе, винная карта — огонь.', 'status' => ReviewStatus::Approved],
                ['author_name' => 'Дмитрий', 'rating' => 4, 'text' => 'Отлично, но вечером лучше бронировать.', 'status' => ReviewStatus::Approved],
                ['author_name' => 'Гость', 'rating' => 5, 'text' => 'Проверяю на модерацию.', 'status' => ReviewStatus::Pending],
            ]);
            $gastro->recalculateRating();
        }

        $coffee = Place::where('slug', 'coffee-room')->first();
        if ($coffee && $coffee->reviews()->count() === 0) {
            $coffee->reviews()->create([
                'author_name' => 'Алина', 'rating' => 5,
                'text' => 'Латте-арт как из pinterest, завтраки до вечера.',
                'status' => ReviewStatus::Approved,
            ]);
            $coffee->recalculateRating();
        }
    }
}
