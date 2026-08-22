@php
    $MONTHS_RU = ['ЯНВ','ФЕВ','МАР','АПР','МАЙ','ИЮН','ИЮЛ','АВГ','СЕН','ОКТ','НОЯ','ДЕК'];
    $WEEKDAYS_RU = ['ВС','ПН','ВТ','СР','ЧТ','ПТ','СБ'];
    $today = now()->startOfDay();
@endphp

<section class="sec" id="events">
    <div class="wrap">
        <div class="s-head reveal">
            <div class="kicker">08 · Афиша</div>
            <h2>Сегодня в городе</h2>
            <p class="s-sub">События на {{ $today->format('d') }} {{ $MONTHS_RU[$today->month - 1] }} — выбирайте, куда пойти</p>
        </div>

        <div class="ev-grid">
            @foreach($events as $e)
                <article class="ev-card reveal">
                    <div class="ev-date">
                        <b>{{ $e->starts_at->format('d') }}</b>
                        <span>{{ $MONTHS_RU[$e->starts_at->month - 1] }}</span>
                        <small>{{ $WEEKDAYS_RU[$e->starts_at->dayOfWeek] }}</small>
                    </div>
                    <div>
                        <h3>{{ $e->type->icon() }} {{ $e->title }}</h3>
                        <p class="ev-meta">{{ $e->place?->name ?? 'Донецк' }} · {{ $e->starts_at->format('H:i') }}</p>
                        <a href="/event/{{ $e->slug }}" class="ev-more">Подробнее</a>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- кнопка к полной афише --}}
        <div class="ev-cta reveal">
            <a href="{{ route('afisha') }}" class="ev-cta__btn">
                <span>Вся афиша города</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
        </div>
    </div>
</section>
