<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\DevelopmentRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DevelopmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(
                $request->user()->hasRole('tech_partner') || $request->user()->hasRole('admin'),
                403,
                'Раздел доступен только техническим партнёрам'
            );
            return $next($request);
        });
    }

    /**
     * Список заявок на разработку
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = DevelopmentRequest::query()
            ->with(['client:id,name,email,phone', 'place:id,name,slug', 'techPartner:id,name']);

        $query->when($request->filter === 'my', fn ($q) =>
        $q->where('tech_partner_id', $user->id)
        );
        $query->when($request->filter === 'available', fn ($q) =>
        $q->where('status', DevelopmentRequest::STATUS_NEW)->whereNull('tech_partner_id')
        );
        $query->when($request->service, fn ($q, $s) => $q->where('service', $s));

        $requests = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('Account/Development/Index', [
            'requests' => $requests,
            'counts'   => [
                'available' => DevelopmentRequest::where('status', DevelopmentRequest::STATUS_NEW)
                    ->whereNull('tech_partner_id')->count(),
                'my'        => DevelopmentRequest::where('tech_partner_id', $user->id)
                    ->whereIn('status', [DevelopmentRequest::STATUS_CLAIMED, DevelopmentRequest::STATUS_IN_PROGRESS])->count(),
                'completed' => DevelopmentRequest::where('tech_partner_id', $user->id)
                    ->where('status', DevelopmentRequest::STATUS_COMPLETED)->count(),
                'earnings'  => (float) DevelopmentRequest::where('tech_partner_id', $user->id)
                    ->where('status', DevelopmentRequest::STATUS_COMPLETED)->sum('amount'),
            ],
            'services' => DevelopmentRequest::SERVICES,
        ]);
    }

    /**
     * Взять заявку в работу
     */
    public function claim(Request $request, DevelopmentRequest $development)
    {
        abort_unless($request->user()->hasRole('tech_partner') || $request->user()->hasRole('admin'), 403);

        if ($development->status !== DevelopmentRequest::STATUS_NEW || $development->tech_partner_id) {
            return back()->with('error', 'Заявка уже взята другим партнёром');
        }

        $development->update([
            'tech_partner_id' => $request->user()->id,
            'status'          => DevelopmentRequest::STATUS_CLAIMED,
        ]);

        return back()->with('success', "Заявка «{$development->title}» взята в работу");
    }

    /**
     * Обновить статус заявки
     */
    public function updateStatus(Request $request, DevelopmentRequest $development)
    {
        $user = $request->user();

        // только тот, кто взял, или админ
        abort_unless($development->tech_partner_id === $user->id || $user->hasRole('admin'), 403);

        $data = $request->validate([
            'status' => ['required', 'in:claimed,in_progress,completed,cancelled'],
        ]);

        $development->update(['status' => $data['status']]);

        $label = $development->statusLabel();
        return back()->with('success', "Статус заявки обновлён: {$label}");
    }
}
