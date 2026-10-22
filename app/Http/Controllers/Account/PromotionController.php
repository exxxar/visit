<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\PromotionRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = PromotionRequest::query()
            ->with(['client:id,name,email', 'place:id,name,slug', 'partner:id,name']);

        if ($request->filter === 'my') {
            $query->where('partner_id', $user->id);
        } elseif ($request->filter === 'available') {
            $query->whereNull('partner_id')->where('status', 'new');
        }

        $query->when($request->service, fn($q, $s) => $q->where('service', $s));

        $requests = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('Account/Promotions/Index', [
            'requests' => $requests,
            'services' => PromotionRequest::SERVICES,
            'counts'   => [
                'available' => PromotionRequest::whereNull('partner_id')->where('status','new')->count(),
                'my'        => PromotionRequest::where('partner_id', $user->id)
                    ->whereIn('status', ['claimed','in_progress'])->count(),
                'completed' => PromotionRequest::where('partner_id', $user->id)
                    ->where('status','completed')->count(),
                'earnings'  => (float) PromotionRequest::where('partner_id', $user->id)
                    ->where('status','completed')->sum('amount'),
            ],
        ]);
    }

    public function claim(Request $request, PromotionRequest $promotion)
    {
        if ($promotion->status !== 'new' || $promotion->partner_id) {
            return back()->with('error', 'Заявка уже взята другим партнёром');
        }

        $promotion->update([
            'partner_id' => $request->user()->id,
            'status'     => 'claimed',
        ]);

        return back()->with('success', "Заявка «{$promotion->title}» взята в работу");
    }

    public function updateStatus(Request $request, PromotionRequest $promotion)
    {
        $user = $request->user();
        abort_unless($promotion->partner_id === $user->id || $user->hasRole('admin'), 403);

        $data = $request->validate([
            'status' => ['required', 'in:claimed,in_progress,completed,cancelled'],
        ]);

        $oldStatus = $promotion->status;
        $promotion->update(['status' => $data['status']]);

        // Если заказ завершен и это первый раз, когда он переходит в этот статус
        if ($data['status'] === 'completed' && $oldStatus !== 'completed' && $promotion->partner_id) {
            $partner = User::find($promotion->partner_id);
            if ($partner) {
                // Вызываем обновленный метод, который сделает и прямое начисление, и MLM
                app(\App\Services\PartnerService::class)->rewardPartnerAndMlm(
                    $partner,
                    (float) $promotion->amount,
                    $promotion->id
                );
            }
        }

        return back()->with('success', 'Статус заявки обновлён');
    }


}

