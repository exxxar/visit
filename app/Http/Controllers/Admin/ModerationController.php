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
            'action'  => ['required', Rule::enum(ModerationAction::class)],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $action = $request->action;
        $comment = $request->comment;

        if ($type === 'applications') {
            $application = Application::findOrFail($id);

            if ($action === 'approve') {
                $place = $application->approve();
                return back()->with('success', "Заявка одобрена. Заведение «{$place->name}» опубликовано, владельцу отправлено письмо.");
            }

            // отклонение
            $application->update(['status' => 'rejected']);
            return back()->with('success', 'Заявка отклонена');
        }

        $entity  = $service->resolve($type, $id);
        $service->handle($entity, ModerationAction::from($action), $comment);

        return back()->with('success', 'Готово: ' . ModerationAction::from($action)->label());
    }
}
