<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps(['stats', 'recentFavorites', 'recentReviews'])

const fmtDate = (d) => new Date(d).toLocaleDateString('ru-RU', {
    day: '2-digit', month: '2-digit', year: 'numeric'
})
</script>

<template>
    <div>
        <div class="pg-head">
            <h1>🧳 Добро пожаловать!</h1>
        </div>

        <!-- Статистика -->
        <div class="grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:24px">
            <div class="vcard" style="text-align:center">
                <b style="color:var(--rose);font-size:28px;font-family:var(--disp)">{{ stats.favorites_count }}</b>
                <span style="color:var(--mut);font-size:13px">Избранных мест</span>
            </div>
            <div class="vcard" style="text-align:center">
                <b style="color:var(--yellow);font-size:28px;font-family:var(--disp)">{{ stats.reviews_count }}</b>
                <span style="color:var(--mut);font-size:13px">Отзывов написано</span>
            </div>
            <div class="vcard" style="text-align:center">
                <b style="color:var(--lime);font-size:28px;font-family:var(--disp)">{{ stats.approved_reviews }}</b>
                <span style="color:var(--mut);font-size:13px">Опубликовано</span>
            </div>
        </div>

        <!-- Недавние избранные -->
        <div v-if="recentFavorites.length" class="vcard" style="margin-bottom:24px">
            <h3 style="margin-top:0;display:flex;justify-content:space-between;align-items:center">
                ❤️ Недавно добавленные
                <Link href="/account/favorites" class="btn btn-ghost btn-sm">Все →</Link>
            </h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;margin-top:16px">
                <Link v-for="place in recentFavorites" :key="place.id" :href="`/place/${place.slug}`" class="fav-card">
                    <img :src="place.cover_url || '/assets/placeholder.jpg'" :alt="place.name" class="fav-card__img">
                    <div class="fav-card__info">
                        <b>{{ place.name }}</b>
                        <span>★ {{ place.rating?.toFixed(1) || '—' }}</span>
                    </div>
                </Link>
            </div>
        </div>

        <!-- Недавние отзывы -->
        <div v-if="recentReviews.length" class="vcard">
            <h3 style="margin-top:0;display:flex;justify-content:space-between;align-items:center">
                ⭐ Мои последние отзывы
                <Link href="/account/reviews" class="btn btn-ghost btn-sm">Все →</Link>
            </h3>
            <div style="margin-top:16px">
                <div v-for="review in recentReviews" :key="review.id" class="review-item">
                    <div class="review-item__head">
                        <Link :href="`/place/${review.place.slug}`" class="review-item__place">
                            {{ review.place.name }}
                        </Link>
                        <span class="review-item__stars">
                            {{ '★'.repeat(review.rating) }}{{ '☆'.repeat(5 - review.rating) }}
                        </span>
                    </div>
                    <p class="review-item__text">{{ review.content.substring(0, 100) }}{{ review.content.length > 100 ? '...' : '' }}</p>
                    <div class="review-item__meta">
                        <span :class="`status status--${review.status}`">
                            {{ review.status === 'approved' ? '✓ Опубликовано' : review.status === 'pending' ? '⏳ На модерации' : '✕ Отклонено' }}
                        </span>
                        <span>{{ fmtDate(review.created_at) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="!recentFavorites.length && !recentReviews.length" class="vcard" style="text-align:center;padding:60px 20px">
            <div style="font-size:48px;margin-bottom:16px">🗺️</div>
            <h3 style="margin:0 0 8px">Начните исследовать Донецк!</h3>
            <p style="color:var(--mut);margin:0 0 20px">Добавляйте места в избранное и оставляйте отзывы</p>
            <Link href="/resources/js/Pages/Public" class="btn btn-grad">Перейти на карту</Link>
        </div>
    </div>
</template>

<style scoped>
.fav-card {
    display:block;
    border:1px solid var(--line);
    border-radius:12px;
    overflow:hidden;
    transition:.2s;
    text-decoration:none;
    color:var(--txt);
}
.fav-card:hover {
    border-color:var(--cyan);
    transform:translateY(-2px);
}
.fav-card__img {
    width:100%;
    height:120px;
    object-fit:cover;
}
.fav-card__info {
    padding:10px;
}
.fav-card__info b {
    display:block;
    font-size:14px;
    margin-bottom:4px;
}
.fav-card__info span {
    color:var(--yellow);
    font-size:13px;
}

.review-item {
    padding:16px;
    border:1px solid var(--line);
    border-radius:12px;
    margin-bottom:12px;
}
.review-item__head {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:8px;
}
.review-item__place {
    font-weight:700;
    color:var(--cyan);
    text-decoration:none;
}
.review-item__stars {
    color:var(--yellow);
    font-size:16px;
}
.review-item__text {
    color:var(--mut);
    font-size:14px;
    line-height:1.5;
    margin:0 0 10px;
}
.review-item__meta {
    display:flex;
    justify-content:space-between;
    font-size:12px;
    color:var(--mut);
}
.status {
    padding:2px 8px;
    border-radius:6px;
    font-weight:600;
}
.status--approved {
    background:rgba(163,230,53,.15);
    color:var(--lime);
}
.status--pending {
    background:rgba(250,204,21,.15);
    color:var(--yellow);
}
.status--rejected {
    background:rgba(255,92,122,.15);
    color:var(--rose);
}
</style>
