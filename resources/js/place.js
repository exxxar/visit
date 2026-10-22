/* ==========================================================================
   ВИЗИТ ДОНЕЦК · Логика страницы места
   ========================================================================== */
const $ = s => document.querySelector(s);
const $$ = s => document.querySelectorAll(s);

// Глобальная функция уведомлений
window.showToast = function(m) {
    const t = $('#toast');
    if (!t) return;
    t.textContent = m;
    t.classList.add('show');
    clearTimeout(window.toastT);
    window.toastT = setTimeout(() => t.classList.remove('show'), 3000);
};

/* reveal анимация */
const io = new IntersectionObserver(es => es.forEach(e => {
    if (e.isIntersecting) { e.target.classList.add('on'); io.unobserve(e.target); }
}), { threshold: .1 });
$$('.reveal').forEach(el => io.observe(el));

/* галерея */
window.setMain = (img) => {
    const mainImg = $('#plMain');
    if (mainImg && img) {
        mainImg.src = img.src;
        $$('.pl-thumbs img').forEach(i => i.classList.toggle('on', i === img));
    }
};

/* ==========================================================================
   1. ИЗБРАННОЕ (Сохраняем ПОЛНЫЙ объект места для автономной работы модалки)
   ========================================================================== */
const favBtn = document.getElementById('favBtn');
const LOCAL_FAV_KEY = 'vizit_local_favorites';

// Вспомогательные функции для localStorage
function getLocalFavs() {
    try {
        const raw = localStorage.getItem(LOCAL_FAV_KEY);
        if (!raw) return [];
        const parsed = JSON.parse(raw);
        // Миграция со старого формата (массив ID) на новый (массив объектов)
        if (Array.isArray(parsed) && parsed.length > 0 && typeof parsed[0] === 'number') {
            return parsed.map(id => ({ id, name: 'Заведение', slug: '', saved_at: new Date().toISOString() }));
        }
        return parsed;
    } catch {
        return [];
    }
}

function updateFavUI(isFav, isLocal = false) {
    const icon = document.getElementById('favIcon');
    const text = document.getElementById('favText');
    if (!icon || !text || !favBtn) return;

    if (isFav) {
        favBtn.classList.add('is-favorite');
        icon.textContent = '❤️';
        text.textContent = isLocal ? 'Сохранено локально' : 'В избранном';
    } else {
        favBtn.classList.remove('is-favorite');
        icon.textContent = '🤍';
        text.textContent = 'В избранное';
    }
}

if (favBtn) {
    const placeId = window.PLACE_DATA?.id;
    const currentUserId = window.PLACE_DATA?.currentUserId;

    if (placeId) {
        // 1. Проверка статуса при загрузке страницы
        if (currentUserId) {
            // Авторизован: проверяем через API
            fetch(`/api/v1/places/${placeId}/favorite/check`, {
                headers: { 'Accept': 'application/json' }
            })
                .then(r => r.json())
                .then(data => {
                    if (data.is_favorite) updateFavUI(true, false);
                })
                .catch(console.error);
        } else {
            // Не авторизован: проверяем localStorage (ищем по ID в массиве объектов)
            const localFavs = getLocalFavs();
            if (localFavs.some(f => f.id === placeId)) {
                updateFavUI(true, true);
            }
        }

        // 2. Обработка клика по кнопке
        favBtn.addEventListener('click', async () => {
            if (!currentUserId) {
                // Не авторизован
                const localFavs = getLocalFavs();
                const isAlreadyLocal = localFavs.some(f => f.id === placeId);

                if (isAlreadyLocal) {
                    // Если уже сохранено локально -> удаляем
                    const updatedFavs = localFavs.filter(f => f.id !== placeId);
                    localStorage.setItem(LOCAL_FAV_KEY, JSON.stringify(updatedFavs));
                    updateFavUI(false, false);
                    showToast('Удалено из локального избранного');
                } else {
                    // Если не сохранено -> открываем модалку выбора
                    const modal = document.getElementById('localFavModal');
                    if (modal) modal.classList.add('open');
                }
                return;
            }

            // Авторизован -> стандартный API запрос
            try {
                const response = await fetch(`/api/v1/places/${placeId}/favorite`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    updateFavUI(data.is_favorite, false);
                    showToast(data.is_favorite ? '❤️ Добавлено в избранное' : 'Удалено из избранного');
                }
            } catch (error) {
                console.error('Favorite error:', error);
                showToast('Ошибка сети');
            }
        });
    }
}

// 3. Обработка кнопки "Сохранить локально" внутри модалки
const saveLocalFavBtn = document.getElementById('saveLocalFavBtn');
if (saveLocalFavBtn) {
    saveLocalFavBtn.addEventListener('click', () => {
        const pd = window.PLACE_DATA || {};
        const favs = getLocalFavs();

        // УМНЫЙ ФОЛБЭК: берем данные из PLACE_DATA, а если их нет — вытаскиваем со страницы
        const currentSlug = pd.slug || window.location.pathname.split('/').filter(Boolean).pop() || 'unknown';
        const currentName = pd.name || document.querySelector('h1')?.textContent || 'Заведение';
        const kickerText = document.querySelector('.kicker')?.textContent || '';
        const [currentCat, currentDist] = kickerText.split('·').map(s => s.trim());
        const currentCover = pd.cover_url || document.querySelector('.pl-main')?.src || '/assets/placeholder.jpg';
        const currentRating = pd.rating || parseFloat(document.querySelector('.pl-rate b')?.textContent) || 0;

        // Формируем полный объект места для сохранения
        const placeObj = {
            id: pd.id,
            name: currentName,
            slug: currentSlug,
            cover_url: currentCover,
            category: pd.category || currentCat || 'Место',
            district: pd.district || currentDist || '',
            rating: currentRating,
            saved_at: new Date().toISOString()
        };

        // Проверяем, нет ли уже такого места (по ID)
        const existingIndex = favs.findIndex(f => f.id === placeObj.id);
        if (existingIndex !== -1) {
            // Обновляем существующую запись, чтобы исправить старый slug, если он был пустым
            favs[existingIndex] = placeObj;
        } else {
            favs.push(placeObj);
        }

        localStorage.setItem(LOCAL_FAV_KEY, JSON.stringify(favs));
        updateFavUI(true, true);

        const modal = document.getElementById('localFavModal');
        if (modal) modal.classList.remove('open');

        showToast('💾 Сохранено локально в этом браузере');
    });
}
// 4. Закрытие модалки локального избранного
document.querySelectorAll('[data-close-local-fav]').forEach(el => {
    el.addEventListener('click', () => {
        const modal = document.getElementById('localFavModal');
        if (modal) modal.classList.remove('open');
    });
});

/* ==========================================================================
   2. ПОДЕЛИТЬСЯ
   ========================================================================== */
const shareBtn = $('#shareBtn');
if (shareBtn) {
    shareBtn.addEventListener('click', async () => {
        const shareData = {
            title: `${window.PLACE_DATA?.name || 'Заведение'} — ВИЗИТ ДОНЕЦК`,
            text: window.PLACE_DATA?.short_description || '',
            url: window.location.href
        };
        if (navigator.share) {
            try { await navigator.share(shareData); }
            catch (err) { if (err.name !== 'AbortError') copyToClipboard(shareData.url); }
        } else {
            copyToClipboard(shareData.url);
        }
    });
}
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => showToast('🔗 Ссылка скопирована'))
        .catch(() => showToast('❌ Не удалось скопировать'));
}

/* ==========================================================================
   3. МАРШРУТ
   ========================================================================== */
const routeModal = $('#routeModal');
const openRouteBtn = $('#openRouteBtn');
if (openRouteBtn && routeModal && window.PLACE_DATA?.lat && window.PLACE_DATA?.lng) {
    // Динамически подставляем координаты в ссылки
    $('#routeYandex').href = `https://yandex.ru/maps/?rtext=~${window.PLACE_DATA.lat},${window.PLACE_DATA.lng}`;
    $('#route2gis').href = `https://2gis.ru/directions?m=${window.PLACE_DATA.lng},${window.PLACE_DATA.lat}`;
    $('#routeGoogle').href = `https://www.google.com/maps/dir/?api=1&destination=${window.PLACE_DATA.lat},${window.PLACE_DATA.lng}`;

    openRouteBtn.addEventListener('click', () => {
        routeModal.classList.add('open');
        document.body.style.overflow = 'hidden';
    });
    routeModal.querySelectorAll('[data-close-route]').forEach(el => {
        el.addEventListener('click', () => {
            routeModal.classList.remove('open');
            document.body.style.overflow = '';
        });
    });
}

/* ==========================================================================
   4. СООБЩИТЬ О НЕТОЧНОСТИ (с записью голоса)
   ========================================================================== */
const placeReportModal = $('#placeReportModal');
const openPlaceReportBtn = $('#openPlaceReportBtn');
const placeReportForm = $('#placeReportForm');
const recordBtn = $('#recordBtn');
const recordStatus = $('#recordStatus');
const audioPreview = $('#audioPreview');
const audioInput = $('#audioInput');
const placeReportSubmit = $('#placeReportSubmit');

let mediaRecorder;
let audioChunks = [];
let isRecording = false;

if (openPlaceReportBtn && placeReportModal) {
    openPlaceReportBtn.addEventListener('click', () => {
        placeReportModal.classList.add('open');
        document.body.style.overflow = 'hidden';
    });
    placeReportModal.querySelectorAll('[data-close-place-report]').forEach(el => {
        el.addEventListener('click', () => {
            placeReportModal.classList.remove('open');
            document.body.style.overflow = '';
            stopRecording();
        });
    });

    recordBtn.addEventListener('click', async () => {
        if (!isRecording) {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                mediaRecorder = new MediaRecorder(stream);
                audioChunks = [];
                mediaRecorder.ondataavailable = event => audioChunks.push(event.data);
                mediaRecorder.onstop = () => {
                    const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                    const audioFile = new File([audioBlob], 'voice_report.webm', { type: 'audio/webm' });
                    audioPreview.src = URL.createObjectURL(audioBlob);
                    audioPreview.style.display = 'block';
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(audioFile);
                    audioInput.files = dataTransfer.files;
                    recordStatus.textContent = '✅ Запись готова к отправке';
                    recordBtn.classList.remove('recording');
                    stream.getTracks().forEach(track => track.stop());
                };
                mediaRecorder.start();
                isRecording = true;
                recordBtn.classList.add('recording');
                recordStatus.textContent = '🔴 Идет запись... (нажмите для остановки)';
                audioPreview.style.display = 'none';
            } catch (err) {
                console.error('Mic error:', err);
                showToast('❌ Нет доступа к микрофону');
            }
        } else {
            stopRecording();
        }
    });

    function stopRecording() {
        if (isRecording && mediaRecorder && mediaRecorder.state !== 'inactive') {
            mediaRecorder.stop();
            isRecording = false;
        }
    }

    placeReportForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        placeReportSubmit.disabled = true;
        placeReportSubmit.textContent = 'Отправляем…';
        const formData = new FormData(placeReportForm);
        try {
            const response = await fetch('/api/v1/places/report', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json'
                },
                body: formData
            });
            const data = await response.json();
            if (response.ok) {
                showToast('✅ Сообщение отправлено на модерацию');
                placeReportModal.classList.remove('open');
                document.body.style.overflow = '';
                placeReportForm.reset();
                audioPreview.style.display = 'none';
                recordStatus.textContent = 'Нажмите, чтобы записать';
            } else {
                showToast('❌ ' + (data.message || 'Ошибка отправки'));
            }
        } catch (err) {
            showToast('❌ Ошибка сети');
        } finally {
            placeReportSubmit.disabled = false;
            placeReportSubmit.textContent = 'Отправить';
        }
    });
}

/* ==========================================================================
   5. ЕДИНЫЙ МЕНЕДЖЕР ОТЗЫВОВ
   ========================================================================== */
const ReviewsManager = {
    placeId: window.PLACE_DATA?.id || null,
    currentPage: 1,
    isOwner: window.PLACE_DATA?.isOwner || false,
    currentUserId: window.PLACE_DATA?.currentUserId || null,

    init() {
        if (!this.placeId) return;
        this.loadReviews();
        this.bindEvents();
    },

    bindEvents() {
        const openReviewBtn = $('#openReviewForm');
        if (openReviewBtn) {
            openReviewBtn.addEventListener('click', () => $('#reviewModal').classList.add('open'));
        }

        // Звёздный рейтинг
        $$('#starRating .star').forEach(star => {
            star.addEventListener('click', () => {
                const value = star.dataset.value;
                $('#ratingInput').value = value;
                $$('#starRating .star').forEach(s => s.classList.toggle('active', s.dataset.value <= value));
            });
            star.addEventListener('mouseenter', () => {
                const value = star.dataset.value;
                $$('#starRating .star').forEach(s => s.classList.toggle('hover', s.dataset.value <= value));
            });
        });
        $('#starRating')?.addEventListener('mouseleave', () => {
            $$('#starRating .star').forEach(s => s.classList.remove('hover'));
        });

        // Отправка форм
        $('#reviewForm')?.addEventListener('submit', async (e) => { e.preventDefault(); await this.submitReview($('#reviewForm')); });
        $('#replyForm')?.addEventListener('submit', async (e) => { e.preventDefault(); await this.submitReply($('#replyForm')); });
        $('#reviewReportForm')?.addEventListener('submit', async (e) => { e.preventDefault(); await this.submitReport($('#reviewReportForm')); });

        // Закрытие модалок
        $$('[data-close]').forEach(btn => {
            btn.addEventListener('click', () => btn.closest('.menu-modal')?.classList.remove('open'));
        });
        $$('.menu-modal__backdrop').forEach(backdrop => {
            backdrop.addEventListener('click', () => backdrop.closest('.menu-modal')?.classList.remove('open'));
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') $$('.menu-modal.open').forEach(m => m.classList.remove('open'));
        });
    },

    async loadReviews(page = 1) {
        const container = $('#reviewsList');
        if (!container) return;
        container.innerHTML = '<div style="text-align:center; padding:20px; color:var(--mut)">Загрузка отзывов...</div>';
        try {
            const response = await fetch(`/api/v1/places/${this.placeId}/reviews?page=${page}`);
            if (!response.ok) throw new Error('HTTP error');
            const data = await response.json();
            this.renderReviews(data.data);
            this.renderPagination(data);
            this.currentPage = page;
        } catch (error) {
            console.error('Error loading reviews:', error);
            container.innerHTML = '<p class="no-reviews">Не удалось загрузить отзывы.</p>';
        }
    },

    renderReviews(reviews) {
        const container = $('#reviewsList');
        if (!container) return;
        if (!reviews || reviews.length === 0) {
            container.innerHTML = '<p class="no-reviews">Пока нет одобренных отзывов. Будьте первым!</p>';
            return;
        }
        container.innerHTML = reviews.map(review => this.renderReview(review)).join('');
        this.bindReviewActions();
    },

    renderReview(review) {
        const isAuthor = review.user_id === this.currentUserId;
        const canReply = this.isOwner && !review.reply;
        const stars = '★'.repeat(review.rating) + '☆'.repeat(5 - review.rating);
        const photos = (review.photos_urls || []).map(url => `<img src="${url}" alt="Фото" class="review-photo" onclick="window.open('${url}', '_blank')">`).join('');
        const reply = review.reply ? `
            <div class="review-reply">
                <div class="reply-header"><strong>${this.escapeHtml(review.reply.user.name)}</strong><span class="reply-badge">Владелец</span></div>
                <p>${this.escapeHtml(review.reply.content)}</p>
                <small class="reply-date">${this.formatDate(review.reply.created_at)}</small>
            </div>` : '';

        const actions = [];
        if (isAuthor) actions.push(`<button class="btn-link text-danger" data-action="delete" data-id="${review.id}">Удалить</button>`);
        if (canReply) actions.push(`<button class="btn-link" data-action="reply" data-id="${review.id}">Ответить</button>`);
        if (!isAuthor && this.currentUserId) actions.push(`<button class="btn-link text-muted" data-action="report" data-id="${review.id}">Пожаловаться</button>`);

        return `
            <div class="review-card" data-review-id="${review.id}">
                <div class="review-header">
                    <div class="review-author">
                        <img src="${review.user?.avatar || '/assets/default-avatar.png'}" alt="" class="avatar">
                        <div class="review-author-info">
                            <strong>${this.escapeHtml(review.user?.name || 'Аноним')}</strong>
                            <div class="review-meta"><span class="stars">${stars}</span><span class="review-date">${this.formatDate(review.created_at)}</span></div>
                        </div>
                    </div>
                    <div class="review-actions">${actions.join('')}</div>
                </div>
                <div class="review-content">
                    <p>${this.escapeHtml(review.content)}</p>
                    ${photos ? `<div class="review-photos">${photos}</div>` : ''}
                </div>
                ${reply}
            </div>`;
    },

    renderPagination(data) {
        const container = $('#reviewsPagination');
        if (!container || data.last_page <= 1) { container.innerHTML = ''; return; }
        let html = '<div class="pagination">';
        if (data.current_page > 1) html += `<button class="btn btn-ghost" data-page="${data.current_page - 1}">← Назад</button>`;
        html += `<span class="page-info">Стр. ${data.current_page} из ${data.last_page}</span>`;
        if (data.current_page < data.last_page) html += `<button class="btn btn-ghost" data-page="${data.current_page + 1}">Вперёд →</button>`;
        html += '</div>';
        container.innerHTML = html;
        container.querySelectorAll('[data-page]').forEach(btn => {
            btn.addEventListener('click', () => this.loadReviews(parseInt(btn.dataset.page)));
        });
    },

    bindReviewActions() {
        $$('[data-action]').forEach(btn => {
            btn.addEventListener('click', async () => {
                const action = btn.dataset.action;
                const id = btn.dataset.id;
                if (action === 'delete' && confirm('Удалить этот отзыв?')) await this.deleteReview(id);
                else if (action === 'reply') { $('#replyReviewId').value = id; $('#replyModal').classList.add('open'); }
                else if (action === 'report') { $('#reviewReportReviewId').value = id; $('#reviewReportModal').classList.add('open'); }
            });
        });
    },

    async submitReview(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true; submitBtn.textContent = 'Отправка...';
        try {
            const response = await fetch(`/api/v1/places/${this.placeId}/reviews`, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'Accept': 'application/json' }
            });
            const data = await response.json();
            if (response.ok) {
                showToast(data.message || 'Отзыв отправлен на модерацию!');
                $('#reviewModal').classList.remove('open');
                form.reset();
                $$('#starRating .star').forEach(s => s.classList.remove('active'));
            } else {
                showToast(data.message || 'Ошибка при отправке');
            }
        } catch (error) {
            showToast('Ошибка сети');
        } finally {
            submitBtn.disabled = false; submitBtn.textContent = 'Отправить на модерацию';
        }
    },

    async submitReply(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true; submitBtn.textContent = 'Отправка...';
        const reviewId = form.querySelector('[name="review_id"]').value;
        try {
            const response = await fetch(`/api/v1/places/${this.placeId}/reviews/${reviewId}/reply`, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'Accept': 'application/json' }
            });
            const data = await response.json();
            if (response.ok) {
                showToast('Ответ опубликован');
                $('#replyModal').classList.remove('open');
                form.reset();
                this.loadReviews(this.currentPage);
            } else {
                showToast(data.message || 'Ошибка');
            }
        } catch (error) {
            showToast('Ошибка сети');
        } finally {
            submitBtn.disabled = false; submitBtn.textContent = 'Опубликовать ответ';
        }
    },

    async submitReport(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true; submitBtn.textContent = 'Отправка...';
        const reviewId = form.querySelector('[name="review_id"]').value;
        try {
            const response = await fetch(`/api/v1/places/${this.placeId}/reviews/${reviewId}/report`, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'Accept': 'application/json' }
            });
            const data = await response.json();
            if (response.ok) {
                showToast('Жалоба отправлена');
                $('#reviewReportModal').classList.remove('open');
                form.reset();
            } else {
                showToast(data.message || 'Ошибка');
            }
        } catch (error) {
            showToast('Ошибка сети');
        } finally {
            submitBtn.disabled = false; submitBtn.textContent = 'Отправить жалобу';
        }
    },

    async deleteReview(id) {
        try {
            const response = await fetch(`/api/v1/places/${this.placeId}/reviews/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'Accept': 'application/json' }
            });
            const data = await response.json();
            if (response.ok) {
                showToast('Отзыв удалён');
                this.loadReviews(this.currentPage);
            } else {
                showToast(data.message || 'Ошибка');
            }
        } catch (error) {
            showToast('Ошибка сети');
        }
    },

    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    formatDate(dateString) {
        if (!dateString) return '';
        return new Date(dateString).toLocaleDateString('ru-RU', { year: 'numeric', month: 'long', day: 'numeric' });
    }
};

// Инициализация отзывов
document.addEventListener('DOMContentLoaded', () => {
    if ($('#reviews')) ReviewsManager.init();
});

/* ==========================================================================
   6. МОДАЛКА МЕНЮ
   ========================================================================== */
(function() {
    const modal = $('#menuModal');
    const body = $('#menuBody');
    const meta = $('#menuMeta');
    const search = $('#menuSearch');
    const sClear = $('#menuSearchClear');
    const openBtn = $('#openMenuBtn');

    if (!modal || !openBtn) return;

    let items = [];
    let loaded = false;

    openBtn.addEventListener('click', async () => {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
        if (!loaded) await loadMenu(openBtn.dataset.placeId);
    });

    $$('.menu-modal__backdrop').forEach(el => el.addEventListener('click', closeMenu));
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.classList.contains('open')) closeMenu(); });

    search.addEventListener('input', e => {
        const q = e.target.value.trim().toLowerCase();
        sClear.hidden = !q;
        renderMenu(q);
    });
    sClear.addEventListener('click', () => {
        search.value = '';
        sClear.hidden = true;
        renderMenu('');
    });

    function closeMenu() {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }

    async function loadMenu(placeId) {
        body.innerHTML = `<div class="menu-modal__state"><div class="menu-modal__spinner"></div><p>Загружаем меню…</p></div>`;
        try {
            const r = await fetch(`/api/v1/places/${placeId}/menu`, { headers: { Accept: 'application/json' } });
            if (!r.ok) throw new Error('HTTP ' + r.status);
            const data = await r.json();
            items = Array.isArray(data.data) ? data.data : [];
            loaded = true;
            meta.textContent = items.length ? `${items.length} ${pluralize(items.length, ['позиция','позиции','позиций'])}` : 'Меню пока пустое';
            renderMenu('');
        } catch (err) {
            body.innerHTML = `<div class="menu-modal__state menu-modal__state--error"><div class="menu-modal__ico">⚠</div><h4>Не удалось загрузить меню</h4><p>Попробуйте обновить страницу</p></div>`;
        }
    }

    function renderMenu(query) {
        if (!items.length) {
            body.innerHTML = `<div class="menu-modal__state"><div class="menu-modal__ico">📋</div><h4>Меню пока не добавлено</h4></div>`;
            return;
        }
        const q = (query || '').trim().toLowerCase();
        const filtered = q ? items.filter(it => (it.name || '').toLowerCase().includes(q) || (it.description || '').toLowerCase().includes(q) || (it.category || '').toLowerCase().includes(q)) : items;

        if (!filtered.length) {
            body.innerHTML = `<div class="menu-modal__state"><div class="menu-modal__ico">🔍</div><h4>Ничего не найдено</h4></div>`;
            return;
        }

        const grouped = {};
        filtered.forEach(it => { (grouped[it.category || 'Без категории'] ||= []).push(it); });

        body.innerHTML = Object.entries(grouped).map(([cat, list]) => `
            <section class="menu-modal__group">
                <h4 class="menu-modal__group-title">${escape(cat)}</h4>
                <div class="menu-modal__grid">${list.map(renderItem).join('')}</div>
            </section>`).join('');
    }

    function renderItem(it) {
        const price = typeof it.price === 'number' ? new Intl.NumberFormat('ru-RU').format(it.price) + ' ₽' : '—';
        const desc = (it.description || '').replace(/Цена:\s*\d+\s*(?:руб(?:лей)?|₽)\.?/gi, '').replace(/Вес:\s*\d+\s*(?:грамм|г|гр|мл|ml)[^.]*/gi, '').replace(/#[a-zA-Zа-яА-ЯёЁ]+/g, '').trim();
        const fallback = '/assets/placeholder.jpg';
        return `<article class="menu-item">
            <div class="menu-item__img"><img src="${escapeAttr(it.image || fallback)}" alt="${escapeAttr(it.name)}" loading="lazy" onerror="this.onerror=null;this.src='${fallback}';this.classList.add('img-fallback')"></div>
            <div class="menu-item__body">
                <h5 class="menu-item__name">${escape(it.name)}</h5>
                ${desc ? `<p class="menu-item__desc">${escape(desc)}</p>` : ''}
                <div class="menu-item__foot">
                    <span class="menu-item__price">${price}</span>
                    ${it.weight ? `<span class="menu-item__weight">${escape(it.weight)}</span>` : ''}
                </div>
            </div>
        </article>`;
    }

    function escape(s) { return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
    function escapeAttr(s) { return escape(s); }
    function pluralize(n, forms) {
        n = Math.abs(n) % 100;
        const n1 = n % 10;
        if (n > 10 && n < 20) return forms[2];
        if (n1 > 1 && n1 < 5) return forms[1];
        if (n1 === 1) return forms[0];
        return forms[2];
    }
})();
