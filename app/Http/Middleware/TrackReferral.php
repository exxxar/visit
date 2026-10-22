<?php

namespace App\Http\Middleware;

use App\Helpers\PhoneNormalizer;
use Closure;
use Illuminate\Http\Request;

class TrackReferral
{
    public function handle(Request $request, Closure $next)
    {
        if ($ref = $request->query('ref')) {
            $normalized = PhoneNormalizer::normalize($ref);

            if ($normalized) {
                // сохраняем в сессию на 30 дней
                session()->put('referral_phone', $normalized);
                session()->put('referral_captured_at', now()->timestamp);

                // редиректим на страницу выбора (только если ещё не там)
                if (!$request->is('ref/*') && !$request->is('admin/*') && !$request->is('api/*')) {
                    return redirect('/ref/choose?phone=' . $normalized);
                }
            }
        }

        return $next($request);
    }
}
