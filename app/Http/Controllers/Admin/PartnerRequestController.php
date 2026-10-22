<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DevelopmentRequest;
use App\Models\PromotionRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PartnerRequestController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'all'); // all, promotion, development

        $promoQuery = PromotionRequest::with(['client:id,name', 'partner:id,name', 'place:id,name']);
        $devQuery = DevelopmentRequest::with(['client:id,name', 'techPartner:id,name', 'place:id,name']);

        if ($type === 'promotion') $devQuery = null;
        if ($type === 'development') $promoQuery = null;

        $promotions = $promoQuery ? $promoQuery->latest()->get() : collect();
        $developments = $devQuery ? $devQuery->latest()->get() : collect();

        return Inertia::render('Admin/Partner/Requests', [
            'promotions' => $promotions,
            'developments' => $developments,
            'activeType' => $type,
        ]);
    }

    public function updateStatus(Request $request, $type, $id)
    {
        $model = $type === 'promotion' ? PromotionRequest::class : DevelopmentRequest::class;
        $req = $model::findOrFail($id);

        $req->update(['status' => $request->status]);
        return back()->with('success', 'Статус заявки обновлен');
    }
}
