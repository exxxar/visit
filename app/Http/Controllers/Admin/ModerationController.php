<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\ModerationAction;
use App\Enums\ModerationStatus;
use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Event;
use App\Models\News;
use App\Models\Place;
use App\Models\Review;
use App\Services\ModerationService;
use App\Services\MypwaImportService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ModerationController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'places');
        abort_unless(array_key_exists($tab, ModerationService::TYPES), 404);

        $items = match ($tab) {
            'places' => Place::where('status', ModerationStatus::OnModeration)
                ->with(['category', 'district', 'owner'])->latest()->paginate(10),
            'news'   => News::where('status', ModerationStatus::OnModeration)
                ->with('place')->latest()->paginate(10),
            'events' => Event::where('status', ModerationStatus::OnModeration)
                ->with('place')->orderBy('starts_at')->paginate(10),
            'reviews'=> Review::where('status', ReviewStatus::Pending)
                ->with(['place', 'user'])->latest()->paginate(10),
            'applications' => Application::whereIn('status',
                [ApplicationStatus::New, ApplicationStatus::InReview])
                ->latest()->paginate(10),
        };

        return Inertia::render('Admin/Moderation/Queue', [
            'tab'   => $tab,
            'items' => $items,
            'counts' => [
                'places'       => Place::where('status', ModerationStatus::OnModeration)->count(),
                'news'         => News::where('status', ModerationStatus::OnModeration)->count(),
                'events'       => Event::where('status', ModerationStatus::OnModeration)->count(),
                'reviews'      => Review::where('status', ReviewStatus::Pending)->count(),
                'applications' => Application::where('status', ApplicationStatus::New)->count(),
            ],
        ]);
    }


    public function act(Request $request, ModerationService $service, string $type, int $id)
    {
        $request->validate([
            'action'  => ['required', 'in:approve,reject,return'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $action  = $request->action;
        $comment = $request->comment;

        if ($type === 'applications') {
            $application = Application::findOrFail($id);

            // защита от повторного одобрения
            if ($application->place_id) {
                return back()->with('warning', "Заявка уже одобрена.");
            }

            if ($action === 'approve') {
                $place = $application->approve();
                return back()->with('success', "Заявка одобрена. Заведение «{$place->name}» опубликовано.");
            }

            if ($action === 'reject') {
                $application->update([
                    'status' => ModerationStatus::Rejected,
                    'rejection_comment' => $comment,
                ]);
                return back()->with('success', 'Заявка отклонена');
            }

            if ($action === 'return') {
                $application->update([
                    'status' => ModerationStatus::OnModeration,
                    'return_comment' => $comment,
                ]);
                return back()->with('success', 'Заявка возвращена на правки');
            }
        }

        // для остальных типов (places, news, events, reviews)
        $entity = $service->resolve($type, $id);

        $status = match ($action) {
            'approve' => ModerationStatus::Approved,
            'reject'  => ModerationStatus::Rejected,
            'return'  => ModerationStatus::OnModeration,
        };

        $entity->update([
            'status' => $status,
            'moderation_comment' => $comment,
        ]);

        $label = match ($action) {
            'approve' => 'Одобрено',
            'reject'  => 'Отклонено',
            'return'  => 'Вернуто на правки',
        };

        return back()->with('success', "Готово: {$label}");
    }

    public function importMypwa(MypwaImportService $service)
    {
        try {
            $stats = $service->importAll();

            return back()->with('success', sprintf(
                'Импорт с mypwa.ru завершён: всего %d, создано заявок %d, пропущено (уже есть) %d, ошибок %d',
                $stats['total'], $stats['created'], $stats['skipped'], $stats['errors']
            ));
        } catch (\Throwable $e) {
            return back()->with('error', 'Ошибка импорта: ' . $e->getMessage());
        }
    }
}
