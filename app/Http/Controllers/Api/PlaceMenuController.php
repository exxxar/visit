<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PlaceMenuController extends Controller
{
    public function show(Request $request, Place $place)
    {
        if (!$place->isImportedFromMypwa()) {
            return response()->json(['error' => 'Меню недоступно для этого заведения'], 404);
        }

        $cacheKey = "mypwa_menu_{$place->external_id}";

        $menu = Cache::remember($cacheKey, 900, function () use ($place) {
            try {
                $url = rtrim(config('services.mypwa.api_url'), '/') . "/tenants/{$place->external_id}/products/export";

                Log::info('[MYPWA] Fetching menu', [
                    'uuid' => $place->external_id,
                    'url'  => $url,
                ]);

                $http = Http::timeout(15)->acceptJson();

                $token = config('services.mypwa.token');
                if ($token) {
                    $http = $http->withToken($token);
                }

                $response = $http->get($url);

                if (!$response->ok()) {
                    Log::warning('[MYPWA] Menu fetch failed', [
                        'uuid'   => $place->external_id,
                        'status' => $response->status(),
                        'body'   => substr($response->body(), 0, 300),
                    ]);
                    return [];
                }

                $items = $response->json('data', []);

                Log::info('[MYPWA] Menu loaded', [
                    'uuid'  => $place->external_id,
                    'count' => count($items),
                ]);

                // Нормализация: цена из строки во float, картинка, категория из хэштегов
                return array_map([$this, 'normalizeItem'], $items);

            } catch (\Throwable $e) {
                Log::error('[MYPWA] Menu fetch error', [
                    'uuid'  => $place->external_id,
                    'error' => $e->getMessage(),
                ]);
                return [];
            }
        });

        return response()->json([
            'data'  => $menu,
            'count' => count($menu),
        ]);
    }

    /**
     * Нормализует один товар из mypwa
     */
    protected function normalizeItem(array $item): array
    {
        // цена: "100.00" → 100.0
        $price = isset($item['price']) ? (float) $item['price'] : null;

        // картинка: images[0].url
        $image = null;
        if (!empty($item['images']) && is_array($item['images'])) {
            $first = $item['images'][0] ?? null;
            $image = is_array($first) ? ($first['url'] ?? null) : $first;
        }

        // категория из хэштегов в описании
        $description = $item['description'] ?? '';
        $category    = $this->extractCategory($description);

        // вес: если null, пробуем вытащить из описания ("Вес: 180 грамм")
        $weight = $item['weight'] ?? $this->extractWeight($description);

        return [
            'id'          => $item['id'] ?? null,
            'name'        => $item['name'] ?? 'Без названия',
            'description' => $description,
            'price'       => $price,
            'weight'      => $weight,
            'image'       => $image,
            'category'    => $category,
        ];
    }

    /**
     * Вытаскивает категорию из #хэштегов в описании
     */
    protected function extractCategory(string $desc): ?string
    {
        if (empty($desc)) return null;

        // Маппинг хэштегов → русские названия категорий
        $map = [
            'кофе'     => 'Кофе',
            'чай'      => 'Чай',
            'напитки'  => 'Напитки',
            'десерт'   => 'Десерты',
            'бургер'   => 'Бургеры',
            'ролл'     => 'Роллы',
            'меню'     => 'Комбо-меню',
            'набор'    => 'Детские наборы',
            'гарнир'   => 'Гарниры',
            'снеки'    => 'Снеки',
            'тост'     => 'Тосты',
            'пита'     => 'Питы',
            'салат'    => 'Салаты',
        ];

        // Ищем первый #хэштег в описании
        if (preg_match('/#([a-zA-Zа-яА-ЯёЁ]+)/u', $desc, $m)) {
            $tag = mb_strtolower($m[1]);
            return $map[$tag] ?? ucfirst($tag);
        }

        return null;
    }

    /**
     * Вытаскивает вес из описания, если он там указан
     */
    protected function extractWeight(string $desc): ?string
    {
        if (empty($desc)) return null;

        // Паттерны: "Вес: 180 грамм", "180 г", "200 мл"
        if (preg_match('/(?:Вес[:\s]+)?(\d+(?:[.,]\d+)?)\s*(грамм|г|gr|мл|ml)\b/iu', $desc, $m)) {
            $value = $m[1];
            $unit  = mb_strtolower($m[2]);
            if (in_array($unit, ['грамм', 'г', 'gr'])) $unit = 'г';
            if (in_array($unit, ['мл', 'ml']))         $unit = 'мл';
            return $value . ' ' . $unit;
        }

        return null;
    }
}
