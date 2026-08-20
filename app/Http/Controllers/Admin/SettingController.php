<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    private const KEYS = ['hero', 'counters', 'socials', 'contacts'];

    public function edit()
    {
        $settings = collect(self::KEYS)
            ->mapWithKeys(fn ($k) => [$k => Setting::get($k, [])]);

        return Inertia::render('Admin/Settings/Edit', ['settings' => $settings]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'hero'     => ['nullable', 'array'],
            'counters' => ['nullable', 'array'],
            'socials'  => ['nullable', 'array'],
            'contacts' => ['nullable', 'array'],
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        return back()->with('success', 'Настройки сохранены');
    }
}
