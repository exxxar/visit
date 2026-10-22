<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OutboundClick;
use App\Models\OutboundDailyStat;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OutboundController extends Controller
{
    public function index(Request $request)
    {
        $days = $request->get('days', 14);

        // Топ доменов за период
        $topDomains = OutboundClick::select('domain', \DB::raw('count(*) as clicks'))
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('domain')
            ->orderByDesc('clicks')
            ->limit(20)
            ->get();

        // Статистика по контекстам
        $byContext = OutboundClick::select('context', \DB::raw('count(*) as clicks'))
            ->where('created_at', '>=', now()->subDays($days))
            ->whereNotNull('context')
            ->groupBy('context')
            ->orderByDesc('clicks')
            ->get();

        // Дневная динамика
        $daily = OutboundDailyStat::select('date', \DB::raw('sum(clicks) as clicks'))
            ->where('date', '>=', now()->subDays($days)->toDateString())
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Последние клики
        $recent = OutboundClick::with('place:id,name')
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn($c) => [
                'id'         => $c->id,
                'url'        => $c->url,
                'domain'     => $c->domain,
                'context'    => $c->context,
                'place_name' => $c->place?->name,
                'ip'         => $c->ip,
                'page_url'   => $c->page_url,
                'created_at' => $c->created_at->toDateTimeString(),
            ]);

        $totals = [
            'all'     => OutboundClick::where('created_at', '>=', now()->subDays($days))->count(),
            'unique'  => OutboundClick::where('created_at', '>=', now()->subDays($days))->distinct('ip')->count('ip'),
            'domains' => OutboundClick::where('created_at', '>=', now()->subDays($days))->distinct('domain')->count('domain'),
        ];

        return Inertia::render('Admin/Outbound/Index', [
            'topDomains' => $topDomains,
            'byContext'  => $byContext,
            'daily'      => $daily,
            'recent'     => $recent,
            'totals'     => $totals,
            'days'       => $days,
        ]);
    }
}
