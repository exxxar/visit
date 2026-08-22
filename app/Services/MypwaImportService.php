<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Category;
use App\Models\District;
use App\Models\Place;
use Illuminate\Http\Client\PendingRequest;
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
            ->acceptJson();
    }

    /**
     * Получает список всех активных тенантов с mypwa.ru
     */
    public function fetchTenants(): array
    {
        $response = $this->http()->get('/tenants/export', [
            'include' => 'settings',
            'active'  => 1,
        ]);

        if (!$response->ok()) {
            Log::error('mypwa fetch failed', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('Не удалось получить список заведений с mypwa.ru (HTTP ' . $response->status() . ')');
        }

        return $response->json('data', []);
    }

    /**
     * Импортирует все тенанты, возвращает статистику
     */
    public function importAll(): array
    {
        $tenants = $this->fetchTenants();
        $stats = ['created' => 0, 'skipped' => 0, 'errors' => 0];

        foreach ($tenants as $tenant) {
            try {
                if ($this->importOne($tenant)) {
                    $stats['created']++;
                } else {
                    $stats['skipped']++;
                }
            } catch (\Throwable $e) {
                $stats['errors']++;
                Log::warning('mypwa import error', [
                    'uuid' => $tenant['uuid'] ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $stats + ['total' => count($tenants)];
    }

    /**
     * Импортирует один тенант. true — создана заявка, false — пропущен (дубль)
     */
    public function importOne(array $tenant): bool
    {
        $externalId = $tenant['uuid'] ?? null;
        if (!$externalId) return false;

        // проверка дубликата: уже есть заявка или заведение с этим uuid
        if (Application::where('external_source', self::SOURCE)->where('external_id', $externalId)->exists()) {
            return false;
        }
        if (Place::where('external_source', self::SOURCE)->where('external_id', $externalId)->exists()) {
            return false;
        }

        $settings = $tenant['meta'] ?? $tenant['settings'] ?? [];
        if (is_string($settings)) $settings = json_decode($settings, true) ?? [];

        $coords = $this->parseCoords($settings['shop_coords'] ?? null);
        $schedule = $this->parseSchedule($settings['schedule'] ?? []);

        $defaultCategory = Category::first();

        Application::create([
            'org_name'         => $tenant['name'] ?? 'Без названия',
            'category_id'      => $defaultCategory?->id,
            'district_id'      => $this->guessDistrict($coords),
            'address'          => $settings['address'] ?? '',
            'phone'            => $settings['manager']['phone'] ?? null,
            'email'            => $settings['manager']['email'] ?? null,
            'site'             => "https://mypwa.ru/{$tenant['slug']}",
            'description'      => trim(($tenant['short_name'] ? $tenant['short_name'] . ".\n" : '') . ($tenant['description'] ?? '')),
            'socials'          => ['mypwa_slug' => $tenant['slug']],
            'lat'              => $coords['lat'],
            'lng'              => $coords['lng'],
            'working_hours'    => $schedule,
            'contact_name'     => $settings['manager']['name'] ?? null,
            'contact_phone'    => $settings['manager']['phone'] ?? null,
            'contact_email'    => $settings['manager']['email'] ?? null,
            'status'           => 'pending',
            'external_id'      => $externalId,
            'external_source'  => self::SOURCE,
        ]);

        return true;
    }

    protected function parseCoords(?string $raw): array
    {
        if (!$raw) return ['lat' => null, 'lng' => null];
        $parts = array_map('trim', explode(',', $raw));
        if (count($parts) < 2) return ['lat' => null, 'lng' => null];
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
