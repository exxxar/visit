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
        return Http::baseUrl($this->apiUrl())
            ->withToken($this->token())
            ->timeout($this->timeout())
            ->connectTimeout(10)
            ->retry(2, 500)   // 2 попытки с задержкой 500мс
            ->acceptJson();
    }

    /**
     * Получает список тенантов
     */
    public function fetchTenants(): array
    {
        $url = $this->apiUrl() . '/tenants/export';
        Log::info('[MYPWA] Начинаем запрос списка тенантов', [
            'url'     => $url,
            'token'   => $this->token() ? substr($this->token(), 0, 8) . '...' : 'EMPTY',
            'timeout' => $this->timeout(),
        ]);

        try {
            $response = $this->http()->get('/tenants/export', [
                'include' => 'settings',
                'active'  => 1,
            ]);

            Log::info('[MYPWA] Ответ получен', [
                'status'     => $response->status(),
                'size_bytes' => strlen($response->body()),
                'headers'    => $response->headers()->all(),
            ]);

            if (!$response->ok()) {
                Log::error('[MYPWA] Не-200 ответ', [
                    'status' => $response->status(),
                    'body'   => Str::limit($response->body(), 500),
                ]);
                throw new \RuntimeException(
                    'mypwa вернул HTTP ' . $response->status() . ': ' . Str::limit($response->body(), 200)
                );
            }

            $data = $response->json();
            Log::info('[MYPWA] JSON распарсен', [
                'has_data' => is_array($data),
                'keys'     => is_array($data) ? array_keys($data) : 'not array',
            ]);

            $tenants = $data['data'] ?? $data;
            if (!is_array($tenants)) {
                Log::error('[MYPWA] Неверный формат ответа', ['data_type' => gettype($tenants)]);
                throw new \RuntimeException('Неверный формат ответа от mypwa (ожидался массив)');
            }

            Log::info('[MYPWA] Получено тенантов: ' . count($tenants));

            return $tenants;

        } catch (RequestException $e) {
            Log::error('[MYPWA] HTTP RequestException', [
                'status'  => $e->response?->status(),
                'body'    => Str::limit($e->response?->body(), 300),
                'message' => $e->getMessage(),
            ]);
            throw $e;
        } catch (\Throwable $e) {
            Log::error('[MYPWA] Ошибка fetchTenants', [
                'class'   => get_class($e),
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
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
    public function importOne(array $tenant)
    {
        $uuid = $tenant['uuid'] ?? null;
        if (!$uuid) {
            return 'no uuid';
        }

        if (Application::where('external_source', self::SOURCE)->where('external_id', $uuid)->exists()) {
            return 'application exists';
        }
        if (Place::where('external_source', self::SOURCE)->where('external_id', $uuid)->exists()) {
            return 'place exists';
        }

        $settings = $tenant['meta'] ?? $tenant['settings'] ?? [];
        if (is_string($settings)) {
            $settings = json_decode($settings, true) ?? [];
        }

        $coords   = $this->parseCoords($settings['shop_coords'] ?? null);
        $schedule = $this->parseSchedule($settings['schedule'] ?? []);

        $defaultCategory = Category::first();
        if (!$defaultCategory) {
            Log::error('[MYPWA] В БД нет ни одной категории — импорт невозможен');
            return 'no categories in DB';
        }

        $address = $settings['address'] ?? '';
        if (empty($address)) {
            Log::warning("[MYPWA] Пустой адрес у $uuid — заявка будет без адреса");
        }

        try {
            $app = Application::create([
                'org_name'         => $tenant['name'] ?? 'Без названия',
                'category_id'      => $defaultCategory->id,
                'district_id'      => $this->guessDistrict($coords),
                'address'          => $address,
                'phone'            => $settings['manager']['phone'] ?? null,
                'email'            => $settings['manager']['email'] ?? null,
                'site'             => "https://mypwa.ru/{$tenant['slug']}",
                'description'      => trim(($tenant['short_name'] ? $tenant['short_name'] . ".\n" : '') . ($tenant['description'] ?? '')),
                'socials'          => ['mypwa_slug' => $tenant['slug'] ?? null],
                'lat'              => $coords['lat'],
                'lng'              => $coords['lng'],
                'working_hours'    => $schedule,
                'contact_name'     => $settings['manager']['name'] ?? null,
                'contact_phone'    => $settings['manager']['phone'] ?? null,
                'contact_email'    => $settings['manager']['email'] ?? null,
                'status'           => 'pending',
                'external_id'      => $uuid,
                'external_source'  => self::SOURCE,
            ]);

            Log::info("[MYPWA] Заявка создана id={$app->id} для uuid=$uuid");
            return true;

        } catch (\Throwable $e) {
            Log::error("[MYPWA] Ошибка Application::create для uuid=$uuid", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
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

    protected function parseSchedule(array $schedule): ?array
    {
        if (empty($schedule)) return null;
        $out = [];
        foreach (array_slice($schedule, 0, 7) as $i => $day) {
            $from = $day['start_at'] ?? null;
            $to   = $day['end_at']   ?? null;
            if ($from && $to) {
                $out[] = ['d' => self::DAYS_RU[$i] ?? ('Д' . ($i + 1)), 'from' => $from, 'to' => $to];
            }
        }
        return $out ?: null;
    }

    protected function guessDistrict(array $coords): ?int
    {
        if (!$coords['lat'] || !$coords['lng']) return null;
        return District::first()?->id;
    }
}
