<?php

namespace App\Http\Controllers;

use App\Enums\ModerationStatus;
use App\Enums\PostStatus;
use App\Models\District;
use App\Models\Event;
use App\Models\Place;
use App\Models\Post;
use App\Models\Setting;

class LandingController extends Controller
{
    public function __invoke()
    {

        $places = Place::approved()->with(['category', 'district', 'photos'])->get();

        // payload для клиентской карты/районов/поиска
        $payload = [
            'places' => $places->map(fn ($p) => [
                'id'    => $p->id,
                'name'  => $p->name,
                'slug'  => $p->slug,
                'cat'   => $p->category?->slug,
                'label' => $p->category?->name,
                'color' => $p->category?->color,
                'icon'  => $p->category?->icon,
                'd'     => $p->district?->name,
                'r'     => (float) $p->rating,
                'desc'  => $p->short_description,
                'cover' => $p->cover_url,
                'xy'    => $p->mapXY(),
                'lat'   => (float) $p->lat,   // ← добавили
                'lng'   => (float) $p->lng,   // ← добавили
            ])->filter(fn ($p) => $p['xy'] !== null)->values(),

            'districts' => District::orderBy('sort')->get(['name', 'slug', 'image', 'stats']),
        ];

        return view('landing', [
            'settings' => [
                'hero'      => Setting::get('hero', []),
                'counters'  => Setting::get('counters', []),
                'socials'   => Setting::get('socials', []),
                'contacts'  => Setting::get('contacts', []),
                'districts' => Setting::get('districts', [
                    'kicker'      => '04 · Районы',
                    'title'       => 'Исследуйте город',
                    'title_grad'  => 'Выберите свой район',
                    'sub'         => 'Все интересные места рядом с вами.',
                    'defaults'    => [
                        'Куйбышевский'  => 97,
                        'Киевский'      => 126,
                        'Калининский'   => 184,
                        'Кировский'     => 112,
                        'Ворошиловский' => 248,
                        'Будённовский'  => 83,
                        'Петровский'    => 64,
                        'Ленинский'     => 145,
                        'Пролетарский'  => 91,
                    ],
                ]),
            ],
            'popular'   => $places->sortByDesc('rating')->take(8)->values(),
            'newPlaces' => $places->sortByDesc('created_at')->take(4)->values(),
            'events'    => Event::where('status', ModerationStatus::Approved)
                ->where('starts_at', '>=', now()->subDay())
                ->with('place')->orderBy('starts_at')->take(6)->get(),
            'posts'     => Post::where('status', PostStatus::Published)
                ->orderByDesc('published_at')->take(7)->get(),
            'districts' => District::orderBy('sort')->get(),   // ← добавили
            'payload'   => $payload,

        ]);
    }
}
