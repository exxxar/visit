<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartnerTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PartnerTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = PartnerTransaction::with(['user:id,name,email', 'request:id,title'])
            ->when($request->search, fn($q, $s) => $q->whereHas('user', fn($u) => $u->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%")))
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->latest();

        return Inertia::render('Admin/Partner/Transactions', [
            'transactions' => $query->paginate(30)->withQueryString(),
            'filters' => $request->only(['search', 'type']),
        ]);
    }
}
