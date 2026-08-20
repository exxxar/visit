<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Admin/Leads/Index', [
            'leads' => Lead::query()
                ->when($request->interest, fn ($q, $i) => $q->where('interest', $i))
                ->latest()->paginate(20)->withQueryString(),
        ]);
    }

    public function export(): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="leads.csv"',
        ];

        return response()->streamDownload(function () {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['id', 'name', 'company', 'position', 'phone', 'email', 'interest', 'created_at'], ';');
            Lead::query()->orderBy('id')->each(fn ($l) => fputcsv($h, [
                $l->id, $l->name, $l->company, $l->position, $l->phone,
                $l->email, $l->interest?->value, $l->created_at->toDateTimeString(),
            ], ';'));
            fclose($h);
        }, 'leads.csv', $headers);
    }
}
