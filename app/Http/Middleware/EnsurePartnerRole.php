<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePartnerRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = $request->user();
        abort_unless($user, 403);

        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole('admin') || $user->hasRole($role)) {
                return $next($request);
            }
        }

        // фолбэк для аккаунтов, созданных через реферальную форму без Spatie-роли
        $map = ['tech_partner' => 'tech', 'ad_partner' => 'ad'];
        $meta = $user->meta ?? [];

        if (($meta['partner_type'] ?? null) === ($map[$role] ?? null)) {
            return $next($request);
        }

        abort(403, 'Раздел доступен только партнёрам соответствующего типа.');
    }
}
