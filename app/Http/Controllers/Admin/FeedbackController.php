<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $feedbacks = Feedback::query()
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->subject, fn ($q, $s) => $q->where('subject', $s))
            ->when($request->q, fn ($q, $s) => $q->where(function ($w) use ($s) {
                $w->where('name', 'like', "%$s%")
                    ->orWhere('contact', 'like', "%$s%")
                    ->orWhere('message', 'like', "%$s%");
            }))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Feedback/Index', [
            'feedbacks' => $feedbacks,
            'subjects'  => Feedback::SUBJECTS,
            'counts'    => [
                'new'       => Feedback::new()->count(),
                'in_progress' => Feedback::where('status', Feedback::STATUS_PROGRESS)->count(),
                'resolved'  => Feedback::where('status', Feedback::STATUS_RESOLVED)->count(),
            ],
        ]);
    }

    public function update(Request $request, Feedback $feedback)
    {
        $request->validate([
            'status'     => ['required', 'in:new,in_progress,resolved'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $feedback->update($request->only(['status', 'admin_note']));

        return back()->with('success', 'Статус обновлён');
    }

    public function destroy(Feedback $feedback)
    {
        $feedback->delete();
        return back()->with('success', 'Обращение удалено');
    }
}
