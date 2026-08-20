<section class="sec popular" id="popular">
    <div class="wrap">
        <div class="s-head rail-head reveal">
            <div><div class="kicker">02 · Выбор горожан</div><h2>Сейчас выбирают</h2></div>
            <div class="rail-btns">
                <button class="rail-btn" id="railPrev" aria-label="Назад">←</button>
                <button class="rail-btn" id="railNext" aria-label="Вперёд">→</button>
            </div>
        </div>
        <div class="rail reveal" id="rail">
            @forelse($popular as $p)
                <article class="p-card">
                    <div class="p-img">
                        <img src="{{ $p->cover_url }}" alt="{{ $p->name }}">
                        <span class="p-rate">★ {{ number_format($p->rating, 1, '.', '') }}</span>
                    </div>
                    <div class="p-body">
                        <h3>{{ $p->name }}</h3>
                        <p class="p-meta">{{ $p->category?->name }} · {{ $p->district?->name }} · {{ $p->reviews_count }} отзывов</p>
                        <div class="p-foot">
                            <span class="p-price">
                                @for ($i = 1; $i <= 3; $i++)
                                    @if ($i <= $p->price_level)<b>$</b>@else $@endif
                                @endfor
                            </span>
                            <a class="p-btn" href="/place/{{ $p->slug }}">Посмотреть место</a>
                        </div>
                    </div>
                </article>
            @empty
                <p class="s-sub">Скоро здесь появятся первые заведения.</p>
            @endforelse
        </div>
    </div>
</section>
