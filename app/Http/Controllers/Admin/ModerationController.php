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

    public function act(Request $request, string $type, int $id)
    {
        $request->validate([
            'action'  => ['required', 'in:approve,reject,return'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $action  = $request->action;
        $comment = $request->comment;

        // Преобразуем строку в enum
        $moderationAction = match ($action) {
            'approve' => ModerationAction::Approved,
            'reject'  => ModerationAction::Rejected,
            'return'  => ModerationAction::Returned,
        };

        // Создаём сервис с текущим пользователем
        $service = new ModerationService(auth()->user());

        // Специальная логика для applications
        if ($type === 'applications') {
            $application = Application::findOrFail($id);

            if ($application->place_id) {
                return back()->with('warning', "Заявка уже одобрена.");
            }

            $service->handle($application, $moderationAction, $comment);

            $label = match ($action) {
                'approve' => "Заявка одобрена. Заведение «{$application->org_name}» опубликовано.",
                'reject'  => 'Заявка отклонена',
                'return'  => 'Заявка возвращена на правки',
            };

            return back()->with('success', $label);
        }

        // Для остальных типов используем сервис
        $entity = $service->resolve($type, $id);
        $service->handle($entity, $moderationAction, $comment);

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
