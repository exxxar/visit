<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PartnerNetworkController extends Controller
{
    public function index(Request $request)
    {
        // Получаем всех пользователей, у кого есть рефералы или кто является партнером
        $query = User::withCount(['referrals as direct_referrals_count'])
            ->with(['referrer:id,name'])
            ->where(function ($q) {
                $q->whereHas('referrals')
                    ->orWhereNotNull('meta->partner_type')
                    ->orWhere('partner_level', '>', 1);
            })
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('phone', 'like', "%$s%"));

        $partners = $query->orderByDesc('partner_balance')->paginate(25)->withQueryString();

        return Inertia::render('Admin/Partner/Network', ['partners' => $partners]);
    }
}
