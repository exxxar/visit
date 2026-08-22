
<!-- Триггер историй -->
<button class="stories-trigger" id="storiesTrigger" aria-label="Истории города">
    <span class="stories-trigger__ring"></span>
    <span class="stories-trigger__icon">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <circle cx="12" cy="12" r="4"/>
        </svg>
    </span>
    <span class="stories-trigger__badge" id="storiesBadge" hidden></span>
</button>

<!-- Модалка историй -->
<div class="stories-modal" id="storiesModal">
    <div class="stories-modal__backdrop" data-close-stories></div>
    <div class="stories-modal__panel">
        <div class="stories-modal__head">
            <h3>Истории города</h3>
            <button class="stories-modal__close" data-close-stories>✕</button>
        </div>
        <div class="stories-tabs">
            <button class="stories-tab on" data-stab="actual">Актуальное</button>
            <button class="stories-tab" data-stab="archive">Архив</button>
        </div>
        <div class="stories-scroll" id="storiesActual" data-pane="actual"></div>
        <div class="stories-scroll" id="storiesArchive" data-pane="archive" hidden></div>
        <div class="stories-empty" id="storiesEmpty" hidden>Пока нет историй — загляните позже ✨</div>
        <div class="stories-loading" id="storiesLoading" hidden>Загружаем…</div>
    </div>
</div>

<!-- Просмотрщик -->
<div class="story-viewer" id="storyViewer">
    <div class="story-viewer__backdrop" data-close-viewer></div>
    <div class="story-viewer__panel">
        <div class="story-viewer__progress"><span id="storyProgress"></span></div>
        <button class="story-viewer__close" data-close-viewer>✕</button>
        <button class="story-viewer__nav story-viewer__nav--prev" data-nav="prev">‹</button>
        <button class="story-viewer__nav story-viewer__nav--next" data-nav="next">›</button>

        <div class="story-viewer__body">
            <div class="story-viewer__media" id="storyMedia"></div>
            <aside class="story-viewer__side" id="storySide">
                <div class="story-viewer__place" id="storyPlace"></div>
                <div class="story-viewer__text-scroll" id="storyTextScroll">
                    <h4 id="storyTitle"></h4>
                    <p id="storyText"></p>
                </div>
            </aside>
        </div>
    </div>
</div>
