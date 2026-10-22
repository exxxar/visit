<!-- Плавающее меню (FAB) -->
<div class="fab-container" id="fabContainer">
    <div class="fab-sub-menu">
        <button class="fab-sub" id="fabFavorites" title="Избранное">
            <span class="fab-sub__ico">❤️</span>
            <span class="fab-sub__txt">Избранное</span>
        </button>
        <button class="fab-sub" id="fabStories" title="Истории города">
            <span class="fab-sub__ico">📸</span>
            <span class="fab-sub__txt">Истории</span>
            <span class="stories-trigger__badge" id="storiesBadge" hidden></span>
        </button>
    </div>
    <button class="fab-main" id="fabMain" aria-label="Открыть меню">
        <span class="fab-main__icon">✦</span>
    </button>
</div>

<!-- Модалка Избранного -->
<div class="menu-modal" id="favoritesModal">
    <div class="menu-modal__backdrop" data-close-fav></div>
    <div class="menu-modal__panel" style="max-width: 560px;">
        <div class="menu-modal__head">
            <div>
                <div class="menu-modal__kicker">Ваши закладки</div>
                <h3 class="menu-modal__title">Избранное</h3>
            </div>
            <button class="menu-modal__close" data-close-fav>✕</button>
        </div>

        <div class="menu-modal__body" style="padding: 20px 24px 24px;">
            <!-- Секция: Локальные (браузер) -->
            <div id="favLocalSection" hidden>
                <h4 class="fav-section-title">💾 Сохранено в этом браузере</h4>
                <div class="fav-list" id="favLocalList"></div>
            </div>

            <!-- Секция: Глобальные (сервер) -->
            <div id="favGlobalSection" hidden>
                <h4 class="fav-section-title">☁️ Синхронизировано с аккаунтом</h4>
                <div class="fav-list" id="favGlobalList"></div>
            </div>

            <!-- Пустое состояние -->
            <div id="favEmptyState" class="menu-modal__state" hidden>
                <div class="menu-modal__ico">🤍</div>
                <h4>Пока пусто</h4>
                <p>Добавляйте места в избранное, чтобы не потерять их</p>
            </div>

            <!-- Блок авторизации -->
            <div id="favAuthPrompt" class="fav-auth-prompt" hidden>
                <p>Войдите в аккаунт, чтобы сохранять избранное на всех устройствах</p>
                <a href="/login?redirect={{ urlencode(request()->fullUrl()) }}" class="btn btn-grad" style="width: 100%; justify-content: center;">
                    🔑 Авторизоваться
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Модалка историй (остаётся без изменений, просто привяжем к новой кнопке) -->
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

<!-- Просмотрщик историй (без изменений) -->
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
