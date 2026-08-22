<?php

namespace App\Http\Controllers;

use App\Enums\ModerationStatus;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AfishaController extends Controller
{
    const MONTHS_SHORT = ['ЯНВ', 'ФЕВ', 'МАР', 'АПР', 'МАЙ', 'ИЮН', 'ИЮЛ', 'АВГ', 'СЕН', 'ОКТ', 'НОЯ', 'ДЕК'];
    const MONTHS_FULL  = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
        'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
    const WEEKDAYS     = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];

    public function index(Request $request)
    {
        $type = $request->string('type')->toString();

        $events = Event::query()
            ->where('status', ModerationStatus::Approved)
            ->where('starts_at', '>=', now()->startOfDay())
            ->when($type, fn ($q) => $q->where('type', $type))
            ->with('place')
            ->orderBy('starts_at')
            ->get()
            ->groupBy(fn ($e) => $e->starts_at->toDateString());

        $days = [];
        foreach ($events as $date => $list) {
            $carbon = Carbon::parse($date);
            $days[] = [
                'label' => $this->formatDayLabel($carbon),
                'day'   => $carbon->format('d'),
                'month' => self::MONTHS_SHORT[$carbon->month - 1],
                'events'=> $list,
            ];
        }

        return view('afisha', [
            'days'   => $days,
            'types'  => \App\Enums\EventType::cases(),
            'active' => $type,
            'total'  => $events->flatten()->count(),
        ]);
    }

    protected function formatDayLabel(Carbon $date): string
    {
        return match (true) {
            $date->isToday()    => 'Сегодня',
            $date->isTomorrow() => 'Завтра',
            default => self::WEEKDAYS[$date->dayOfWeek] . ', '
                . $date->format('d') . ' ' . self::MONTHS_FULL[$date->month - 1],
        };
    }
}
