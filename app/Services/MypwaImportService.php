<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Category;
use App\Models\District;
use App\Models\Place;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MypwaImportService
{
    protected const SOURCE = 'mypwa.ru';
    protected const DAYS_RU = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];

    protected function apiUrl(): string
    {
        return config('services.mypwa.api_url', 'https://mypwa.ru/api/v1');
    }

    protected function token(): string
    {
        return config('services.mypwa.token', '');
    }

    protected function timeout(): int
    {
        return (int) config('services.mypwa.timeout', 30);
    }

    protected function http(): PendingRequest
    {
        $client = Http::baseUrl($this->apiUrl())
            ->timeout($this->timeout())
            ->connectTimeout(10)
            ->acceptJson();

        // Добавляем токен только если он не пустой
        $token = $this->token();
        if ($token && $token !== '') {
            $client = $client->withToken($token);
        }

        return $client;
    }

    public function fetchTenants(): array
    {
        $url = $this->apiUrl();
        $token = $this->token();

        Log::info('[MYPWA] === НАЧАЛО ИМПОРТА ===');
        Log::info('[MYPWA] Конфигурация', [
            'api_url' => $url,
            'token'   => $token ? substr($token, 0, 8) . '...' : 'EMPTY',
            'timeout' => $this->timeout(),
        ]);

        // Проверяем, что URL не пустой
        if (empty($url)) {
            Log::error('[MYPWA] api_url пустой! Проверь config/services.php и .env');
            throw new \RuntimeException('api_url не настроен');
        }

        $endpoint = $url . '/tenants/export';
        Log::info('[MYPWA] Отправляем запрос', [
            'endpoint' => $endpoint,
            'method'   => 'GET',
            'params'   => ['include' => 'settings', 'active' => 1],
        ]);

        $startTime = microtime(true);

        try {
            $response = $this->http()->get('/tenants/export', [
                'include' => 'settings',
                'active'  => 1,
            ]);

            $elapsed = round(microtime(true) - $startTime, 2);

            Log::info('[MYPWA] Ответ получен', [
                'status'      => $response->status(),
                'elapsed_sec' => $elapsed,
                'body_size'   => strlen($response->body()),
            ]);

            if (!$response->ok()) {
                Log::error('[MYPWA] HTTP ошибка', [
                    'status' => $response->status(),
                    'body'   => Str::limit($response->body(), 500),
                ]);
                throw new \RuntimeException('HTTP ' . $response->status());
            }

            $data = $response->json();
            $tenants = $data['data'] ?? $data;

            if (!is_array($tenants)) {
                Log::error('[MYPWA] Неверный формат ответа', ['type' => gettype($tenants)]);
                throw new \RuntimeException('Неверный формат ответа');
            }

            Log::info('[MYPWA] Получено тенантов: ' . count($tenants));

            return $tenants;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $elapsed = round(microtime(true) - $startTime, 2);
            Log::error('[MYPWA] ConnectionException (таймаут/сеть)', [
                'elapsed_sec' => $elapsed,
                'message'     => $e->getMessage(),
            ]);
            throw new \RuntimeException('Не удалось подключиться к mypwa.ru: ' . $e->getMessage());

        } catch (\Throwable $e) {
            $elapsed = round(microtime(true) - $startTime, 2);
            Log::error('[MYPWA] Ошибка запроса', [
                'elapsed_sec' => $elapsed,
                'class'       => get_class($e),
                'message'     => $e->getMessage(),
            ]);
            throw $e;
        }
    }
    /**
     * Импортирует все тенанты
     */
    public function importAll(): array
    {
        Log::info('[MYPWA] === НАЧАЛО ИМПОРТА ===');
        $startedAt = microtime(true);

        $tenants = $this->fetchTenants();
        $stats = [
            'total'   => count($tenants),
            'created' => 0,
            'skipped' => 0,
            'errors'  => 0,
            'details' => [],
        ];

        foreach ($tenants as $index => $tenant) {
            $uuid = $tenant['uuid'] ?? 'no-uuid-' . $index;
            $name = $tenant['name'] ?? 'Без названия';

            try {
                Log::info("[MYPWA] Обработка [$index/" . count($tenants) . "] $name (uuid: $uuid)");

                $result = $this->importOne($tenant);

                if ($result === true) {
                    $stats['created']++;
                    $stats['details'][] = ['uuid' => $uuid, 'name' => $name, 'status' => 'created'];
                    Log::info("[MYPWA] ✓ Создана заявка: $name");
                } elseif ($result === false) {
                    $stats['skipped']++;
                    $stats['details'][] = ['uuid' => $uuid, 'name' => $name, 'status' => 'skipped (duplicate)'];
                    Log::info("[MYPWA] ⊘ Пропущен (дубль): $name");
                } else {
                    $stats['skipped']++;
                    $stats['details'][] = ['uuid' => $uuid, 'name' => $name, 'status' => 'skipped: ' . $result];
                    Log::info("[MYPWA] ⊘ Пропущен: $name ($result)");
                }
            } catch (\Throwable $e) {
                $stats['errors']++;
                $stats['details'][] = [
                    'uuid'  => $uuid,
                    'name'  => $name,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
                Log::warning("[MYPWA] ✗ Ошибка при импорте $name", [
                    'uuid'    => $uuid,
                    'error'   => $e->getMessage(),
                    'class'   => get_class($e),
                ]);
            }
        }

        $elapsed = round(microtime(true) - $startedAt, 2);
        $stats['elapsed_sec'] = $elapsed;

        Log::info('[MYPWA] === КОНЕЦ ИМПОРТА ===', [
            'total'   => $stats['total'],
            'created' => $stats['created'],
            'skipped' => $stats['skipped'],
            'errors'  => $stats['errors'],
            'elapsed' => $elapsed . 's',
        ]);

        return $stats;
    }

    /**
     * Импортирует один тенант
     *
     * @return true|string  true — создана, string — причина пропуска
     */
    public function importOne(array $tenant): bool
    {
        $externalId = $tenant['uuid'] ?? null;
        if (!$externalId) {
            Log::warning('[MYPWA] Пропуск: нет uuid', ['tenant' => $tenant['name'] ?? 'unknown']);
            return false;
        }

        // проверка дубликата
        if (Application::where('external_source', self::SOURCE)->where('external_id', $externalId)->exists()) {
            return false;
        }
        if (Place::where('external_source', self::SOURCE)->where('external_id', $externalId)->exists()) {
            return false;
        }

        $settings = $tenant['meta'] ?? [];
        if (is_string($settings)) {
            $settings = json_decode($settings, true) ?? [];
        }

        $coords   = $this->parseCoords($settings['shop_coords'] ?? null);
        $schedule = $this->parseSchedule($settings['schedule'] ?? []);

        $defaultCategory = Category::first();
        if (!$defaultCategory) {
            Log::error('[MYPWA] В БД нет ни одной категории — импорт невозможен');
            throw new \RuntimeException('В БД нет ни одной категории');
        }

        // manager: пустые строки → null
        $manager = $settings['manager'] ?? [];
        $contactName  = !empty($manager['name'])  ? $manager['name']  : null;
        $contactPhone = !empty($manager['phone']) ? $manager['phone'] : null;
        $contactEmail = !empty($manager['email']) ? $manager['email'] : null;

        // адрес: пустая строка → null
        $address = !empty($settings['address']) ? $settings['address'] : null;

        // описание
        $shortName   = $tenant['short_name'] ?? null;
        $description = $tenant['description'] ?? null;
        $fullDesc    = trim(($shortName ? $shortName . ".\n" : '') . ($description ?? ''));

        try {
            $app = Application::create([
                'org_name'         => $tenant['name'] ?? 'Без названия',
                'category_id'      => $defaultCategory->id,
                'district_id'      => $this->guessDistrict($coords),
                'address'          => $address,
                'phone'            => $contactPhone,
                'email'            => $contactEmail,
                'site'             => "https://mypwa.ru/{$tenant['slug']}",
                'description'      => $fullDesc ?: null,
                'socials'          => ['mypwa_slug' => $tenant['slug'] ?? null],
                'lat'              => $coords['lat'],
                'lng'              => $coords['lng'],
                'working_hours'    => $schedule,
                'contact_name'     => $contactName,
                'contact_phone'    => $contactPhone,
                'contact_email'    => $contactEmail,
                'status'           => 'pending',
                'external_id'      => $externalId,
                'external_source'  => self::SOURCE,
            ]);

            Log::info("[MYPWA] ✓ Заявка создана id={$app->id}", [
                'uuid'    => $externalId,
                'name'    => $app->org_name,
                'address' => $app->address,
                'lat'     => $app->lat,
                'lng'     => $app->lng,
                'hours'   => count($app->working_hours ?? []),
            ]);

            return true;

        } catch (\Throwable $e) {
            Log::error("[MYPWA] Ошибка Application::create", [
                'uuid'  => $externalId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function parseSchedule(array $schedule): ?array
    {
        if (empty($schedule)) return null;

        $out = [];
        foreach (array_slice($schedule, 0, 7) as $i => $day) {
            // пропускаем закрытые дни
            if (!empty($day['closed'])) {
                continue;
            }

            $from = $day['start_at'] ?? null;
            $to   = $day['end_at']   ?? null;

            if (empty($from) || empty($to)) {
                continue;
            }

            // если есть day на русском — берём его, иначе используем индекс
            $dayName = $day['day'] ?? self::DAYS_RU[$i] ?? null;

            if (!$dayName) {
                continue;
            }

            $out[] = [
                'd'    => $dayName,
                'from' => $from,
                'to'   => $to,
            ];
        }

        return $out ?: null;
    }

    protected function parseCoords(?string $raw): array
    {
        if (!$raw || $raw === '0,0') {
            return ['lat' => null, 'lng' => null];
        }
        $parts = array_map('trim', explode(',', $raw));
        if (count($parts) < 2) {
            return ['lat' => null, 'lng' => null];
        }
        return [
            'lat' => is_numeric($parts[0]) ? (float) $parts[0] : null,
            'lng' => is_numeric($parts[1]) ? (float) $parts[1] : null,
        ];
    }



    protected function guessDistrict(array $coords): ?int
    {
        if (!$coords['lat'] || !$coords['lng']) return null;
        return District::first()?->id;
    }
}
