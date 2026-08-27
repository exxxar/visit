<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\ModerationAction;
use App\Enums\ModerationStatus;
use App\Enums\ReviewStatus;
use App\Models\Application;
use App\Models\Category;
use App\Models\District;
use App\Models\Event;
use App\Models\News;
use App\Models\Place;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ModerationService
{
    public function __construct(private readonly User $moderator) {}

    public const TYPES = [
        'places'       => [Place::class,       'moderate places'],
        'news'         => [News::class,        'moderate news'],
        'events'       => [Event::class,       'moderate events'],
        'reviews'      => [Review::class,      'moderate reviews'],
        'applications' => [Application::class, 'moderate places'],
    ];

    public function resolve(string $type, int $id): Model
    {
        abort_unless(array_key_exists($type, self::TYPES), 404);

        // Проверяем права только если модератор НЕ админ
        if (!$this->moderator->is_admin) {
            abort_unless($this->moderator->can(self::TYPES[$type][1]), 403);
        }

        return self::TYPES[$type][0]::findOrFail($id);
    }

    public function handle(Model $entity, ModerationAction $action, ?string $comment): Model
    {
        return match (true) {
            $entity instanceof Place,
                $entity instanceof News,
                $entity instanceof Event  => $entity->moderate($action, $this->moderator, $comment),
            $entity instanceof Review => $this->review($entity, $action, $comment),
            $entity instanceof Application => $this->application($entity, $action, $comment),
            default => throw new \InvalidArgumentException('Unknown entity type'),
        };
    }

    private function review(Review $review, ModerationAction $action, ?string $comment): Review
    {
        $review->log($action, $this->moderator, $comment);

        $review->update(['status' => match ($action) {
            ModerationAction::Approved => ReviewStatus::Approved,
            ModerationAction::Rejected => ReviewStatus::Rejected,
            ModerationAction::Returned => ReviewStatus::Pending,
        }]);

        return $review;
    }

    private function application(Application $a, ModerationAction $action, ?string $comment): Application
    {
        $a->log($action, $this->moderator, $comment);

        $a->update(['status' => match ($action) {
            ModerationAction::Approved => ApplicationStatus::Approved,
            ModerationAction::Rejected => ApplicationStatus::Rejected,
            ModerationAction::Returned => ApplicationStatus::InReview,
        }]);

        // одобрили заявку → заводим карточку заведения
        if ($action === ModerationAction::Approved) {
            Place::create([
                'name'              => $a->org_name,
                'slug'              => Str::slug($a->org_name) . '-' . Str::lower(Str::random(4)),
                'category_id'       => Category::firstOrCreate(
                    ['name' => $a->category],
                    ['slug' => Str::slug($a->category)]
                )->id,
                'district_id'       => District::where('name', $a->district)->first()?->id
                        ?? District::first()?->id,
                'address'           => $a->address ?? 'уточнить у владельца',
                'phone'             => $a->contact_phone,
                'email'             => $a->contact_email,
                'site'              => $a->site,
                'short_description' => $a->description,
                'socials'           => $a->socials,
                'status'            => ModerationStatus::Approved,
            ]);
        }

        return $a;
    }
}
