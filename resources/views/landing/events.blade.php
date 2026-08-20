@php $MONTHS = ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC']; @endphp

<section class="sec" id="events">
    <div class="wrap">
        <div class="s-head reveal">
            <div class="kicker">08 · Афиша</div><h2>Сегодня в городе</h2>
        </div>
        <div class="ev-grid">
            @foreach($events as $e)
                <article class="ev-card reveal">
                    <div class="ev-date"><b>{{ $e->starts_at->format('d') }}</b><span>{{ $MONTHS[$e->starts_at->month - 1] }}</span></div>
                    <div>
                        <h3>{{ $e->type->icon() }} {{ $e->title }}</h3>
                        <p class="ev-meta">{{ $e->place?->name ?? 'Донецк' }} · {{ $e->starts_at->format('H:i') }}</p>
                        <a href="/event/{{ $e->slug }}" class="ev-more">Подробнее</a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
