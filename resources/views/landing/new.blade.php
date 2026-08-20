<section class="sec" id="new">
    <div class="wrap">
        <div class="s-head reveal">
            <div class="kicker">06 · Свежие открытия</div>
            <h2>Новое в городе</h2>
            <p class="s-sub">Места, которые открылись совсем недавно.</p>
        </div>
        <div class="new-grid">
            @forelse($newPlaces as $p)
                <article class="n-card reveal">
                    <span class="new-badge">NEW</span>
                    <div class="n-img"><img src="{{ $p->cover_url }}" alt="{{ $p->name }}"></div>
                    <div class="n-body">
                        <h3>{{ $p->name }}</h3>
                        <p class="n-meta"><b>{{ $p->category?->name }}</b> · {{ strtoupper($p->created_at->format('d M')) }} · {{ $p->district?->name }}</p>
                    </div>
                </article>
            @empty
                <p class="s-sub">Скоро здесь появятся первые открытия — следите за обновлениями.</p>
            @endforelse
        </div>
    </div>
</section>
