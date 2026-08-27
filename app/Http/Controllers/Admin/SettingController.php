<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function edit()
    {
        $settings = Setting::all()
            ->pluck('value', 'key')
            ->map(function ($v) {
                if (is_array($v)) {
                    return $v;
                }
                if (is_string($v)) {
                    return json_decode($v, true) ?? [];
                }
                return [];
            })
            ->toArray();

        $defaults = [
            'hero' => [
                'title' => 'Гид по Донецку',
                'sub'   => 'Городской путеводитель',
                'add'   => '',
            ],
            'counters' => [
                'places'     => '1200+',
                'categories' => '48',
                'pages'      => '380',
            ],
            'socials' => [
                'telegram'  => '',
                'vk'        => '',
                'instagram' => '',
            ],
            'contacts' => [
                'email'   => '',
                'phone'   => '',
                'address' => '',
            ],
            'districts' => [
                'kicker'     => '04 · Районы',
                'title'      => 'Исследуйте город',
                'title_grad' => 'Выберите свой район',
                'sub'        => 'Все интересные места рядом с вами.',
                'defaults'   => [
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
            ],
        ];

        foreach ($defaults as $key => $section) {
            if (!isset($settings[$key]) || !is_array($settings[$key])) {
                $settings[$key] = $section;
            } else {
                $settings[$key] = array_merge($section, $settings[$key]);
            }
        }

        // ПРОВЕРЬ: путь должен совпадать с именем файла (без .vue)
        return Inertia::render('Admin/Settings', ['settings' => $settings]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'hero'               => ['array'],
            'counters'           => ['array'],
            'socials'            => ['array'],
            'contacts'           => ['array'],
            'districts'          => ['array'],
            'districts.defaults' => ['array'],
        ]);

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => json_encode($value)]
            );
        }

        return back()->with('success', 'Настройки сохранены');
    }

    public function recalculateDistricts()
    {
        $counts = \App\Models\Place::approved()
            ->whereNotNull('district_id')
            ->selectRaw('d.name, COUNT(*) as cnt')
            ->join('districts as d', 'd.id', '=', 'places.district_id')
            ->groupBy('d.name')
            ->pluck('cnt', 'name')
            ->toArray();

        $defaults = [];
        foreach (['Куйбышевский','Киевский','Калининский','Кировский','Ворошиловский','Будённовский','Петровский','Ленинский','Пролетарский'] as $name) {
            $defaults[$name] = $counts[$name] ?? 0;
        }

        $current = Setting::get('districts', []);
        if (!is_array($current)) {
            $current = [];
        }
        $current['defaults'] = $defaults;

        Setting::updateOrCreate(
            ['key' => 'districts'],
            ['value' => json_encode($current)]
        );

        return back()->with('success', 'Дефолты пересчитаны: всего ' . array_sum($defaults) . ' мест');
    }
}
