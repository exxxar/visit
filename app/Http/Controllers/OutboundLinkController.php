<?php

namespace App\Http\Controllers;

use App\Models\OutboundClick;
use App\Models\OutboundDailyStat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OutboundLinkController extends Controller
{
    /**
     * Логирование + редирект. Вызывается через JS beacon или напрямую.
     */
    public function track(Request $request)
    {
        $data = $request->validate([
            'url'      => ['required', 'url', 'max:500'],
            'context'  => ['nullable', 'string', 'max:50'],
            'place_id' => ['nullable', 'integer', 'exists:places,id'],
            'page_url' => ['nullable', 'string', 'max:500'],
        ]);

        // Защита от логирования внутренних ссылок
        $ownHost = parse_url(config('app.url'), PHP_URL_HOST);
        $targetHost = OutboundClick::extractDomain($data['url']);

        if ($targetHost === strtolower(preg_replace('/^www\./', '', $ownHost))) {
            return response()->json(['ok' => false, 'reason' => 'internal'], 400);
        }

        // Асинхронная запись (в очередь, если настроена)
        try {
            $this->logClick($data, $request, $targetHost);
        } catch (\Throwable $e) {
            Log::warning('Outbound click log failed: ' . $e->getMessage());
        }

        return response()->json(['ok' => true, 'redirect' => $data['url']]);
    }

    /**
     * Прямой редирект (для <a href="/out?url=..."> без JS)
     */
    public function redirect(Request $request)
    {
        $url = $request->query('url');
        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            return redirect('/');
        }

        // Логируем синхронно (быстро, без очереди)
        $targetHost = OutboundClick::extractDomain($url);
        $ownHost = parse_url(config('app.url'), PHP_URL_HOST);

        if ($targetHost !== strtolower(preg_replace('/^www\./', '', $ownHost))) {
            try {
                $this->logClick([
                    'url'      => $url,
                    'context'  => $request->query('context'),
                    'place_id' => $request->query('place_id'),
                    'page_url' => $request->header('referer'),
                ], $request, $targetHost);
            } catch (\Throwable $e) {
                // не блокируем редирект
            }
        }

        return redirect()->away($url);
    }

    /**
     * Запись клика + обновление дневной статистики
     */
    private function logClick(array $data, Request $request, string $domain): void
    {
        $context = $data['context'] ?? OutboundClick::guessContext($data['page_url'] ?? '');

        OutboundClick::create([
            'url'        => $data['url'],
            'domain'     => $domain,
            'context'    => $context,
            'place_id'   => $data['place_id'] ?? null,
            'user_id'    => $request->user()?->id,
            'ip'         => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 300),
            'referer'    => substr($request->header('referer') ?? '', 0, 500),
            'page_url'   => $data['page_url'] ?? null,
        ]);

        // Агрегатная статистика (UPSERT)
        OutboundDailyStat::updateOrCreate(
            [
                'date'    => now()->toDateString(),
                'domain'  => $domain,
                'context' => $context,
            ],
            [
                'clicks'     => DB::raw('COALESCE(clicks, 0) + 1'),
                'unique_ips' => DB::raw('COALESCE(unique_ips, 0) + 1'), // упрощённо
            ]
        );
    }
}
