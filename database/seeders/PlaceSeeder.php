<?php

namespace Database\Seeders;

use App\Enums\ModerationStatus;
use App\Models\Category;
use App\Models\District;
use App\Models\Place;
use App\Models\PlaceReview;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlaceSeeder extends Seeder
{
    public function run(): void
    {
        // Используем новые email из UserSeeder
        $owner = User::where('email', 'owner@code-novorossii.ru')->first();
        $tourist = User::where('email', 'tourist@code-novorossii.ru')->first();

        // Если турист не найден, берем ID 1 (обычно это админ или первый пользователь)
        $touristId = $tourist ? $tourist->id : 1;

        $places = [
            // slug, name, cat, district, address, lat, lng, price, img, rating, hasOwner
            ['gastro-bar', 'GASTRO BAR', 'food', 'Ворошиловский', 'ул. Артёма, 12', 48.044, 37.804, 3, 'assets/restaurant.jpg', 4.8, true],
            ['coffee-room', 'COFFEE ROOM', 'coffee', 'Ворошиловский', 'бул. Пушкина, 3', 48.062, 37.828, 2, 'assets/coffee.jpg', 4.9, true],
            ['neon-bar', 'NEON BAR', 'bars', 'Ворошиловский', 'ул. Университетская, 21', 48.016, 37.784, 3, 'assets/bar.jpg', 4.7, false],
            ['sakura', 'SAKURA', 'food', 'Киевский', 'пр-т Киевский, 64', 48.036, 37.872, 2, 'assets/sushi.jpg', 4.6, false],
            ['aura-spa', 'AURA SPA', 'beauty', 'Ворошиловский', 'ул. Челюскинцев, 8', 48.064, 37.764, 3, 'assets/spa.jpg', 4.9, false],
            ['powerhouse-gym', 'POWERHOUSE GYM', 'sport', 'Калининский', 'ул. Горбатова, 14', 47.992, 37.852, 2, 'assets/gym.jpg', 4.7, false],
            ['titan-detailing', 'TITAN DETAILING', 'auto', 'Куйбышевский', 'ул. Кирова, 101', 47.988, 37.700, 2, 'assets/car.jpg', 4.9, false],
            ['del-mar', 'DEL MAR', 'leisure', 'Ворошиловский', 'ул. Набережная, 1', 48.030, 37.820, 3, 'assets/hotel.jpg', 4.8, false],
            ['apteka-24', 'АПТЕКА «ЗДОРОВЬЕ»', 'med', 'Ленинский', 'ул. Ляшко, 15', 47.995, 37.810, 1, 'assets/pharmacy.jpg', 4.5, false],
            ['fleur', 'FLEUR', 'shop', 'Киевский', 'пр-т Киевский, 12', 48.016, 37.844, 2, 'assets/flowers.jpg', 4.8, false],
            ['burger-joint', 'BURGER JOINT', 'food', 'Ленинский', 'ул. 50-летия СССР, 34', 47.985, 37.830, 2, 'assets/burger.jpg', 4.6, false],
            ['vintage-barber', 'VINTAGE BARBER', 'beauty', 'Кировский', 'ул. Богдана Хмельницкого, 55', 48.050, 37.750, 2, 'assets/barber.jpg', 4.8, false],
            ['skate-zone', 'SKATE ZONE', 'sport', 'Будённовский', 'ул. Щербакова, 15', 48.020, 37.900, 1, 'assets/skate.jpg', 4.5, false],
            ['auto-profi', 'AUTO PROFI', 'auto', 'Пролетарский', 'ул. Прокофьева, 88', 48.010, 37.920, 2, 'assets/auto-service.jpg', 4.7, false],
            ['knigi-i-kofe', 'КНИГИ И КОФЕ', 'coffee', 'Петровский', 'ул. Петровского, 112', 48.080, 37.720, 1, 'assets/bookstore.jpg', 4.9, false],
            ['planetarium', 'ДОНЕЦКИЙ ПЛАНЕТАРИЙ', 'leisure', 'Калининский', 'ул. Постышева, 37', 48.005, 37.840, 1, 'assets/planetarium.jpg', 4.8, false],
        ];

        $descriptions = [
            'food' => 'Авторская кухня, свежие продукты и уютная атмосфера для ваших лучших вечеров.',
            'coffee' => 'Спешелти зерно, авторские десерты и идеальное место для утренних встреч или работы с ноутбуком.',
            'bars' => 'Крафтовые коктейли, живая музыка по выходным и атмосфера, которая заряжает энергией.',
            'beauty' => 'Профессиональный уход, премиальная косметика и полный релакс для вашего тела и души.',
            'sport' => 'Современное оборудование, опытные тренеры и дружелюбное комьюнити для достижения ваших целей.',
            'auto' => 'Полный спектр услуг: от планового ТО до сложного ремонта и детейлинга с гарантией качества.',
            'leisure' => 'Яркие впечатления, комфортный сервис и незабываемые эмоции для всей семьи.',
            'shop' => 'Широкий ассортимент, внимательные консультанты и только проверенные бренды.',
            'med' => 'Квалифицированные фармацевты и широкий выбор медикаментов по доступным ценам.',
        ];

        foreach ($places as [$slug, $name, $cat, $district, $address, $lat, $lng, $price, $img, $rating, $hasOwner]) {
            $place = Place::firstOrCreate(['slug' => $slug], [
                'name'              => $name,
                'category_id'       => Category::where('slug', $cat)->first()?->id,
                'district_id'       => District::where('name', $district)->first()?->id,
                'owner_id'          => $hasOwner ? $owner?->id : null,
                'address'           => $address,
                'lat'               => $lat,
                'lng'               => $lng,
                'price_level'       => $price,
                'short_description' => "$name — проверено горожанами. Рейтинг: $rating",
                'description'       => $descriptions[$cat] ?? 'Отличное место в сердце Донецка с высоким уровнем сервиса.',
                'phone'             => '+7 856 ' . rand(100, 999) . '-00-00',
                'status'            => ModerationStatus::Approved,
                'rating'            => $rating,
                'working_hours'     => [['d' => 'ежедневно', 'from' => '09:00', 'to' => '22:00']],
                'socials'           => ['telegram' => '@' . $slug, 'vk' => 'vk.com/' . $slug],
            ]);

            $place->photos()->firstOrCreate(['is_cover' => true], ['path' => $img, 'sort' => 0]);
        }

        /* Отзывы для живой карточки */
        $this->addReviews('gastro-bar', $touristId, [
            ['rating' => 5, 'content' => 'Лучший ужин в городе, винная карта просто огонь!', 'status' => 'approved'],
            ['rating' => 4, 'content' => 'Отлично, но вечером лучше бронировать столик заранее.', 'status' => 'approved'],
            ['rating' => 5, 'content' => 'Проверяю, как работает модерация новых отзывов.', 'status' => 'pending'],
        ]);

        $this->addReviews('coffee-room', $touristId, [
            ['rating' => 5, 'content' => 'Латте-арт как из Pinterest, завтраки подают до самого вечера.', 'status' => 'approved'],
            ['rating' => 5, 'content' => 'Мое любимое место для утренних планерок.', 'status' => 'approved'],
        ]);

        $this->addReviews('neon-bar', $touristId, [
            ['rating' => 5, 'content' => 'Атмосфера нереальная, бармен знает свое дело!', 'status' => 'approved'],
        ]);

        $this->addReviews('vintage-barber', $touristId, [
            ['rating' => 5, 'content' => 'Стригут отлично, виски наливают щедро. Рекомендую.', 'status' => 'approved'],
        ]);
    }

    /**
     * ✅ ИСПРАВЛЕННЫЙ МЕТОД: теперь он добавляет user_id к каждому отзыву
     */
    private function addReviews(string $slug, int $userId, array $reviewsData): void
    {
        $place = Place::where('slug', $slug)->first();

        if ($place && $place->reviews()->count() === 0) {
            // Добавляем user_id в каждый массив данных перед сохранением
            $reviewsWithUser = collect($reviewsData)->map(function ($review) use ($userId) {
                return array_merge($review, ['user_id' => $userId]);
            })->toArray();

            $place->reviews()->createMany($reviewsWithUser);
        }
    }
}
