<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\ReferralInvite;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReferralController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $phone = $user->phone;

        if (!$phone) {
            return Inertia::render('Account/Referrals/Index', [
                'invites' => [],
                'counts'  => $this->emptyCounts(),
                'phone'   => null,
            ]);
        }

        $query = ReferralInvite::query()
            ->with([
                'referred:id,name,email,phone',
                'place:id,name,slug',
            ])
            ->where('referrer_phone', $phone);

        // фильтры
        $query->when($request->role, fn ($q, $r) => $q->where('role', $r));
        $query->when($request->status, fn ($q, $s) => $q->where('status', $s));
        $query->when($request->q, fn ($q, $s) => $q->where(function ($w) use ($s) {
            $w->whereHas('referred', fn ($u) => $u->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"))
                ->orWhereHas('place', fn ($p) => $p->where('name', 'like', "%$s%"));
        }));

        $invites = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('Account/Referrals/Index', [
            'invites' => $invites,
            'counts'  => [
                'total'     => ReferralInvite::where('referrer_phone', $phone)->count(),
                'pending'   => ReferralInvite::where('referrer_phone', $phone)->where('status', 'pending')->count(),
                'active'    => ReferralInvite::where('referrer_phone', $phone)->where('status', 'active')->count(),
                'clients'   => ReferralInvite::where('referrer_phone', $phone)->where('role', 'client')->count(),
                'tech'      => ReferralInvite::where('referrer_phone', $phone)->where('role', 'tech_partner')->count(),
                'ad'        => ReferralInvite::where('referrer_phone', $phone)->where('role', 'ad_partner')->count(),
                'week'      => ReferralInvite::where('referrer_phone', $phone)->where('created_at', '>=', now()->subDays(7))->count(),
            ],
            'phone' => $phone,
        ]);
    }

    private function emptyCounts(): array
    {
        return [
            'total' => 0, 'pending' => 0, 'active' => 0,
            'clients' => 0, 'tech' => 0, 'ad' => 0, 'week' => 0,
        ];
    }
}
