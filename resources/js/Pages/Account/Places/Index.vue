<script setup>
// AccountLayout подключится автоматически благодаря app.js (префикс Account/)
import { Link } from '@inertiajs/vue3'

defineProps({
    places: {
        type: Object,
        default: () => ({ data: [], links: [] })
    }
})
</script>

<template>
    <div>
        <div class="pg-head">
            <h1>🏢 Мои заведения</h1>
        </div>

        <!-- Если заведения есть -->
        <div v-if="places.data.length" class="places-grid">
            <div v-for="place in places.data" :key="place.id" class="place-card">
                <Link :href="`/account/place/${place.slug}`" class="place-card__link">
                    <img :src="place.cover_url || '/assets/placeholder.jpg'" :alt="place.name" class="place-card__img">
                    <div class="place-card__body">
                        <h3>{{ place.name }}</h3>
                        <p class="place-card__meta">
                            {{ place.category?.icon }} {{ place.category?.name }} · {{ place.district?.name }}
                        </p>
                        <div class="place-card__footer">
                            <span class="place-card__rating">★ {{ place.rating?.toFixed(1) || '—' }}</span>
                            <span class="place-card__status" :class="place.status === 'active' ? 'status-active' : 'status-pending'">
                                {{ place.status === 'active' ? 'Активно' : 'На модерации' }}
                            </span>
                        </div>
                    </div>
                </Link>

                <div class="place-card__actions">
                    <Link :href="`/account/places/${place.slug}/edit`" class="btn-action btn-edit">
                        ✏️ Редактировать
                    </Link>
                    <Link :href="`/account/places/${place.slug}/analytics`" class="btn-action btn-analytics">
                        📊 Статистика
                    </Link>
                </div>
            </div>
        </div>

        <!-- Если заведений нет (пустое состояние) -->
        <div v-else class="empty-state">
            <div class="empty-state__icon">🏢</div>
            <h3>Пока нет добавленных заведений</h3>
            <p>Добавьте свое первое заведение, чтобы оно появилось в каталоге и стало доступно пользователям.</p>
            <Link href="/ref/choose" class="btn-grad">Добавить заведение</Link>
        </div>

        <!-- Пагинация -->
        <div v-if="places.links && places.links.length > 3" class="pager">
            <Link v-for="(l, index) in places.links" :key="index" :href="l.url ?? '#'" preserve-scroll
                  :class="{ on: l.active, off: !l.url }" v-html="l.label" />
        </div>
    </div>
</template>

<style scoped>
.pg-head {
    margin-bottom: 24px;
}

.pg-head h1 {
    font-family: var(--disp);
    font-size: clamp(22px, 3vw, 28px);
    font-weight: 800;
    color: var(--txt);
    margin: 0;
}

.places-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
}

.place-card {
    border: 1px solid var(--line);
    border-radius: 14px;
    overflow: hidden;
    background: var(--panel);
    transition: .2s;
    display: flex;
    flex-direction: column;
}

.place-card:hover {
    border-color: var(--cyan);
    transform: translateY(-2px);
}

.place-card__link {
    text-decoration: none;
    color: var(--txt);
    display: block;
    flex: 1;
}

.place-card__img {
    width: 100%;
    height: 160px;
    object-fit: cover;
}

.place-card__body {
    padding: 14px;
}

.place-card__body h3 {
    margin: 0 0 6px;
    font-size: 16px;
    font-weight: 700;
}

.place-card__meta {
    color: var(--mut);
    font-size: 13px;
    margin: 0 0 10px;
}

.place-card__footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.place-card__rating {
    color: var(--yellow);
    font-weight: 700;
    font-size: 15px;
}

.place-card__status {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 99px;
    text-transform: uppercase;
}

.status-active {
    background: rgba(34, 211, 238, 0.15);
    color: var(--cyan);
    border: 1px solid rgba(34, 211, 238, 0.3);
}

.status-pending {
    background: rgba(250, 204, 21, 0.15);
    color: var(--yellow);
    border: 1px solid rgba(250, 204, 21, 0.3);
}

.place-card__actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    border-top: 1px solid var(--line);
}

.btn-action {
    padding: 12px;
    text-align: center;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: .2s;
}

.btn-action:first-child {
    border-right: 1px solid var(--line);
}

.btn-edit {
    color: var(--txt);
    background: rgba(255, 255, 255, 0.02);
}

.btn-edit:hover {
    background: rgba(34, 211, 238, 0.1);
    color: var(--cyan);
}

.btn-analytics {
    color: var(--txt);
    background: rgba(255, 255, 255, 0.02);
}

.btn-analytics:hover {
    background: rgba(139, 92, 246, 0.1);
    color: var(--violet);
}

/* Пустое состояние */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: var(--panel);
    border: 1px dashed var(--line);
    border-radius: 14px;
}

.empty-state__icon {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
}

.empty-state h3 {
    margin: 0 0 8px;
    color: var(--txt);
}

.empty-state p {
    color: var(--mut);
    margin: 0 0 24px;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.btn-grad {
    display: inline-block;
    padding: 10px 24px;
    background: linear-gradient(135deg, var(--cyan), var(--violet));
    border-radius: 10px;
    color: #fff;
    font-weight: 700;
    text-decoration: none;
    transition: .2s;
}

.btn-grad:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(34, 211, 238, 0.3);
}

/* Пагинация */
.pager {
    margin-top: 24px;
    display: flex;
    justify-content: center;
    gap: 8px;
}

.pager a {
    padding: 8px 14px;
    border-radius: 8px;
    border: 1px solid var(--line);
    background: var(--panel);
    color: var(--mut);
    text-decoration: none;
    font-size: 13px;
    transition: .2s;
}

.pager a.on {
    background: var(--cyan);
    color: #000;
    border-color: var(--cyan);
    font-weight: 700;
}

.pager a.off {
    opacity: 0.4;
    cursor: not-allowed;
}

@media (max-width: 640px) {
    .places-grid {
        grid-template-columns: 1fr;
    }
}
</style>
