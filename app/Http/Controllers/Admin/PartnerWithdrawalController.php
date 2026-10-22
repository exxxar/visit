<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartnerWithdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PartnerWithdrawalController extends Controller
{
    /**
     * Список заявок на вывод
     */
    public function index(Request $request)
    {
        $query = PartnerWithdrawal::query()
            ->with(['user:id,name,email,phone'])
            ->latest();

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $withdrawals = $query->paginate(20)->withQueryString();

        $counts = [
            'pending'  => PartnerWithdrawal::where('status', 'pending')->count(),
            'approved' => PartnerWithdrawal::where('status', 'approved')->count(),
            'rejected' => PartnerWithdrawal::where('status', 'rejected')->count(),
        ];


        return Inertia::render('Admin/Partner/Withdrawals', [
            'withdrawals' => $withdrawals,
            'counts'      => $counts,
        ]);
    }

    /**
     * Подтверждение вывода
     */
    public function approve(PartnerWithdrawal $withdrawal)
    {
        DB::transaction(function () use ($withdrawal) {
            $withdrawal->update([
                'status'        => 'approved',
                'admin_comment' => request('admin_comment', $withdrawal->admin_comment),
            ]);
        });

        return back()->with('success', 'Заявка на вывод подтверждена');
    }

    /**
     * Отклонение вывода (с возвратом средств на баланс)
     */
    public function reject(Request $request, PartnerWithdrawal $withdrawal)
    {
        $data = $request->validate([
            'admin_comment' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($withdrawal, $data) {
            // ВАЖНО: возвращаем средства на баланс партнера
            $withdrawal->user->increment('partner_balance', $withdrawal->amount);

            $withdrawal->update([
                'status'        => 'rejected',
                'admin_comment' => $data['admin_comment'],
            ]);
        });

        return back()->with('success', 'Заявка отклонена, средства возвращены на баланс партнера');
    }
}
