<section class="sec" id="editorial">
    <div class="wrap">
        <div class="s-head reveal">
            <div class="kicker">07 · Редакционные подборки</div>
            <h2>Куда пойти?</h2>
            <p class="s-sub">Городской журнал ВИЗИТ ДОНЕЦК советует.</p>
        </div>
        <div class="ed-grid">
            @foreach($posts as $i => $post)
                @break($i >= 7)
                <a href="/post/{{ $post->slug }}"
                   class="ed-card @if($i === 0) big ed-a @elseif($i === 1) ed-b @elseif($i === 2) ed-c @elseif($i === 3) ed-d @elseif($i === 4) ed-e @elseif($i === 5) ed-f @else ed-g @endif reveal">
                    <img src="{{ safe_img($post->cover) }}" alt="{{ $post->title }}">
                    <div class="ed-in">
                        <span class="ed-tag">{{ $post->tag ?? 'Подборка' }}</span>
                        <h3>{{ $post->title }}</h3>
                        <div class="ed-more">Читать →</div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
