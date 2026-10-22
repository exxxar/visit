<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralInvite;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReferralController extends Controller
{
    public function index(Request $request)
    {
        $query = ReferralInvite::query()
            ->with(['referrer:id,name,email,phone', 'referred:id,name,email,phone', 'place:id,name,slug']);

        $query->when($request->q, fn ($q, $s) => $q->where(function ($w) use ($s) {
            $w->where('referrer_phone', 'like', "%$s%")
                ->orWhereHas('referrer', fn ($u) => $u->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"))
                ->orWhereHas('referred', fn ($u) => $u->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"))
                ->orWhereHas('place', fn ($p) => $p->where('name', 'like', "%$s%"));
        }));

        $query->when($request->role, fn ($q, $r) => $q->where('role', $r));
        $query->when($request->status, fn ($q, $s) => $q->where('status', $s));

        $invites = $query->latest()->paginate(25)->withQueryString();

        return Inertia::render('Admin/Referrals/Index', [
            'invites' => $invites,
            'counts'  => [
                'total'       => ReferralInvite::count(),
                'pending'     => ReferralInvite::where('status', ReferralInvite::STATUS_PENDING)->count(),
                'active'      => ReferralInvite::where('status', ReferralInvite::STATUS_ACTIVE)->count(),
                'completed'   => ReferralInvite::where('status', ReferralInvite::STATUS_COMPLETED)->count(),
                'clients'     => ReferralInvite::where('role', ReferralInvite::ROLE_CLIENT)->count(),
                'tech'        => ReferralInvite::where('role', ReferralInvite::ROLE_TECH)->count(),
                'ad'          => ReferralInvite::where('role', ReferralInvite::ROLE_AD)->count(),
                'week'        => ReferralInvite::where('created_at', '>=', now()->subDays(7))->count(),
            ],
        ]);
    }
}
