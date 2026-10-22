<?php

namespace App\Http\Middleware;

use App\Models\DevelopmentRequest;
use App\Models\PromotionRequest;
use App\Models\ReferralInvite;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'csrfToken' => csrf_token(),

            'auth.user' => $request->user() ? [
                ...$request->user()->only([
                    'id', 'name', 'email', 'phone_normalized',
                    'partner_level', 'partner_balance', 'completed_orders_count'
                ]),
                'roles'       => $request->user()->getRoleNames()->values()->toArray(),
                'permissions' => $request->user()->getAllPermissions()->pluck('name')->values()->toArray(),
            ] : null,

            'feedbackNewCount' => fn () => auth()->check() && auth()->user()->hasRole('admin')
                ? \App\Models\Feedback::where('status', 'new')->count() // Безопаснее, чем ::new()
                : 0,

            'flash' => [
                'success' => $request->session()->get('success'),
                'error'   => $request->session()->get('error'),
            ],

            // ✅ ИСПРАВЛЕНО: убран параметр $request, используется auth()->user()
            'partner' => function () {
                $user = auth()->user();
                if (!$user) {
                    return null;
                }

                // Вызываем метод, если он существует (защита от ошибки, если вы его ещё не добавили в User.php)
                $type = method_exists($user, 'partnerType') ? $user->partnerType() : null;

                return [
                    'type' => $type,
                    'dev_available' => $type === 'tech'
                        ? DevelopmentRequest::whereNull('tech_partner_id')->where('status', 'new')->count()
                        : 0,
                    'ad_available' => $type === 'ad'
                        ? PromotionRequest::whereNull('partner_id')->where('status', 'new')->count()
                        : 0,
                    'referrals_count' => $user->phone_normalized
                        ? ReferralInvite::where('referrer_phone', $user->phone_normalized)->count()
                        : 0,
                ];
            },
        ];
    }
}
