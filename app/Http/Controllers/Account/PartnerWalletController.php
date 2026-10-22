<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;

use App\Services\PartnerService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PartnerWalletController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $levels = config('partners.levels');
        $currentLevel = $levels[$user->partner_level] ?? $levels[1];
        $nextLevel = $levels[$user->partner_level + 1] ?? null;

        $transactions = PartnerTransaction::where('user_id', $user->id)
            ->latest()
            ->paginate(15);

        $withdrawals = PartnerWithdrawal::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        // Получаем список приглашенных (1 уровень)
        $referrals = User::where('referred_by', $user->id)
            ->select('id', 'name', 'email', 'created_at', 'meta')
            ->latest()
            ->get()
            ->map(function ($u) {
                $u->partner_type = $u->meta['partner_type'] ?? null;
                return $u;
            });

        // Считаем общий сетевой доход
        $mlmEarningsTotal = PartnerTransaction::where('user_id', $user->id)
            ->where('type', 'mlm')
            ->sum('amount');

        return Inertia::render('Account/Partner/Wallet', [
            'balance'          => (float) $user->partner_balance,
            'level'            => $currentLevel,
            'nextLevel'        => $nextLevel,
            'ordersCount'      => $user->completed_orders_count,
            'transactions'     => $transactions,
            'withdrawals'      => $withdrawals,
            'referrals'        => $referrals,            // <-- НОВОЕ
            'mlmEarningsTotal' => (float) $mlmEarningsTotal, // <-- НОВОЕ
        ]);
    }

    public function withdraw(Request $request, PartnerService $service)
    {
        $data = $request->validate([
            'amount'          => ['required', 'numeric', 'min:100'],
            'payment_details' => ['required', 'string', 'max:255'],
        ]);

        $success = $service->requestWithdrawal(
            $request->user(),
            (float) $data['amount'],
            $data['payment_details']
        );

        if (!$success) {
            return back()->with('error', 'Недостаточно средств на балансе');
        }

        return back()->with('success', 'Заявка на вывод создана и ожидает подтверждения администратора');
    }
}
