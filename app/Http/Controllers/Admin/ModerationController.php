<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\ModerationAction;
use App\Enums\ModerationStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Event;
use App\Models\News;
use App\Models\Place;
use App\Models\PlaceReview; // <-- ЗАМЕНЕНО с Review на PlaceReview
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

        // Используем строку 'pending', так как в миграции PlaceReview мы задали string enum.
        // Если ты используешь PHP Enum, замени 'pending' на ReviewStatus::Pending->value
        $reviewStatusPending = 'pending';

        $items = match ($tab) {
            'places' => Place::where('status', ModerationStatus::OnModeration)
                ->with(['category', 'district', 'owner'])->latest()->paginate(15),
            'news'   => News::where('status', ModerationStatus::OnModeration)
                ->with('place')->latest()->paginate(15),
            'events' => Event::where('status', ModerationStatus::OnModeration)
                ->with('place')->orderBy('starts_at')->paginate(15),

            // <-- ОБНОВЛЕННЫЙ ЗАПРОС ДЛЯ ОТЗЫВОВ -->
            'reviews'=> PlaceReview::where('status', $reviewStatusPending)
                ->with(['place:id,name,slug', 'user:id,name'])
                ->latest()
                ->paginate(15),

            'applications' => Application::whereIn('status',
                [ApplicationStatus::New, ApplicationStatus::InReview])
                ->latest()->paginate(15),
        };

        return Inertia::render('Admin/Moderation/Queue', [ // Или 'Admin/Moderation/Index', как у тебя в роутах
            'tab'   => $tab,
            'items' => $items,
            'counts' => [
                'places'       => Place::where('status', ModerationStatus::OnModeration)->count(),
                'news'         => News::where('status', ModerationStatus::OnModeration)->count(),
                'events'       => Event::where('status', ModerationStatus::OnModeration)->count(),

                // <-- ОБНОВЛЕННЫЙ СЧЁТЧИК -->
                'reviews'      => PlaceReview::where('status', $reviewStatusPending)->count(),

                'applications' => Application::whereIn('status', [ApplicationStatus::New, ApplicationStatus::InReview])->count(),
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

        $moderationAction = match ($action) {
            'approve' => ModerationAction::Approved,
            'reject'  => ModerationAction::Rejected,
            'return'  => ModerationAction::Returned,
        };

        $service = new ModerationService(auth()->user());

        // Специальная логика для applications
        if ($type === 'applications') {
            $application = Application::findOrFail($id);

            if ($application->place_id) {
                return back()->with('warning', "Заявка уже одобрена и привязана к заведению.");
            }

            $service->handle($application, $moderationAction, $comment);

            $label = match ($action) {
                'approve' => "Заявка одобрена. Заведение «{$application->org_name}» опубликовано.",
                'reject'  => 'Заявка отклонена',
                'return'  => 'Заявка возвращена на правки',
            };

            return back()->with('success', $label);
        }

        // Для остальных типов (places, news, events, reviews)
        $entity = $service->resolve($type, $id);

        // ВАЖНО: Для отзывов действие "return" (вернуть на правки) не имеет смысла,
        // так как авторы не редактируют отзывы после отправки. Конвертируем в reject.
        if ($type === 'reviews' && $action === 'return') {
            $action = 'reject';
            $moderationAction = ModerationAction::Rejected;
            if (empty($comment)) {
                $comment = 'Отзыв отклонен модератором (не соответствует правилам площадки).';
            }
        }

        // Выполняем действие через сервис
        $service->handle($entity, $moderationAction, $comment);

        // ПРИМЕЧАНИЕ: Благодаря хуку booted() в модели PlaceReview,
        // при смене статуса на 'approved' или 'rejected' рейтинг заведения пересчитается АВТОМАТИЧЕСКИ.

        $label = match ($action) {
            'approve' => 'Отзыв одобрен и опубликован',
            'reject'  => 'Отзыв отклонен',
            'return'  => 'Отзыв возвращен на правки',
            default   => 'Действие выполнено'
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
