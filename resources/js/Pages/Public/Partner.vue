<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps(['partner', 'reviews', 'ratingDistribution'])

const maxCount = computed(() => Math.max(...Object.values(props.ratingDistribution), 1))

const stars = (rating) => '★'.repeat(rating) + '☆'.repeat(5 - rating)

const fmtDate = (d) => new Date(d).toLocaleDateString('ru-RU', {
    day: '2-digit', month: '2-digit', year: 'numeric'
})
</script>

<template>
    <div class="partner-page">
        <!-- Шапка профиля -->
        <div class="partner-hero">
            <div class="partner-avatar">
                {{ partner.name[0].toUpperCase() }}
            </div>
            <div class="partner-info">
                <h1>{{ partner.name }}</h1>
                <div class="partner-badges">
                    <span class="badge badge--level">🏆 {{ partner.level_name }}</span>
                    <span class="badge badge--rating">
                        <span class="stars">{{ stars(Math.round(partner.partner_rating)) }}</span>
                        {{ partner.partner_rating.toFixed(1) }}
                        <small>({{ partner.reviews_count }} отзывов)</small>
                    </span>
                    <span class="badge badge--orders">
                        ✓ {{ partner.completed_orders }} заказов
                    </span>
                </div>
                <p v-if="partner.bio" class="partner-bio">{{ partner.bio }}</p>
                <div class="partner-meta">
                    <span>Участник с {{ partner.member_since }}</span>
                    <a v-if="partner.portfolio_url" :href="partner.portfolio_url" target="_blank" rel="noopener" class="partner-portfolio">
                        Портфолио →
                    </a>
                </div>
            </div>
        </div>

        <!-- Распределение оценок -->
        <div v-if="partner.reviews_count > 0" class="rating-distribution">
            <h2>Распределение оценок</h2>
            <div class="rating-bars">
                <div v-for="i in 5" :key="i" class="rating-bar-row">
                    <span class="rating-bar-label">{{ 6 - i }} ★</span>
                    <div class="rating-bar-track">
                        <div
                            class="rating-bar-fill"
                            :style="{ width: (ratingDistribution[6 - i] / maxCount * 100) + '%' }"
                        ></div>
                    </div>
                    <span class="rating-bar-count">{{ ratingDistribution[6 - i] }}</span>
                </div>
            </div>
        </div>

        <!-- Отзывы -->
        <div v-if="reviews.data.length" class="partner-reviews">
            <h2>Отзывы клиентов</h2>
            <div v-for="r in reviews.data" :key="r.id" class="review-card">
                <div class="review-head">
                    <div class="review-avatar">
                        {{ r.client.name[0].toUpperCase() }}
                    </div>
                    <div class="review-meta">
                        <b>{{ r.client.name }}</b>
                        <small>{{ fmtDate(r.created_at) }}</small>
                    </div>
                    <div class="review-rating">
                        <span class="stars">{{ stars(r.rating) }}</span>
                    </div>
                </div>
                <p v-if="r.comment" class="review-text">{{ r.comment }}</p>
                <div v-if="r.partner_reply" class="review-reply">
                    <b>Ответ партнёра:</b>
                    <p>{{ r.partner_reply }}</p>
                </div>
            </div>

            <!-- Пагинация -->
            <div v-if="reviews.links.length > 3" class="pager" style="margin-top:24px">
                <Link v-for="l in reviews.links" :key="l.label" :href="l.url ?? '#'" preserve-scroll
                      :class="{ on: l.active, off: !l.url }" v-html="l.label" />
            </div>
        </div>

        <div v-else class="empty-state">
            <div class="empty-ico">💬</div>
            <h3>Отзывов пока нет</h3>
            <p>Партнёр только начинает свою работу на платформе</p>
        </div>
    </div>
</template>

<style scoped>
.partner-page {
    max-width: 900px;
    margin: 0 auto;
    padding: 120px 24px 60px;
}

.partner-hero {
    display: flex;
    gap: 24px;
    align-items: flex-start;
    margin-bottom: 40px;
    padding: 28px;
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: 20px;
}

.partner-avatar {
    width: 96px;
    height: 96px;
    border-radius: 24px;
    flex-shrink: 0;
    display: grid;
    place-items: center;
    background: linear-gradient(135deg, var(--cyan), var(--violet));
    color: #fff;
    font-size: 40px;
    font-weight: 800;
    font-family: var(--disp);
}

.partner-info h1 {
    font-family: var(--disp);
    font-size: 32px;
    margin: 0 0 12px;
}

.partner-badges {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 14px;
}

.badge {
    padding: 6px 14px;
    border-radius: 99px;
    font-size: 13px;
    font-weight: 700;
    background: var(--panel2);
    border: 1px solid var(--line);
}

.badge--level {
    background: linear-gradient(90deg, var(--cyan), var(--violet));
    color: #fff;
    border: none;
}

.badge--rating {
    color: var(--yellow);
}

.badge--rating .stars {
    letter-spacing: 2px;
    margin-right: 4px;
}

.badge--rating small {
    color: var(--mut);
    margin-left: 4px;
    font-weight: 500;
}

.badge--orders {
    color: var(--lime);
}

.partner-bio {
    color: var(--mut);
    font-size: 15px;
    line-height: 1.6;
    margin: 0 0 14px;
}

.partner-meta {
    display: flex;
    gap: 20px;
    font-size: 13px;
    color: var(--mut);
}

.partner-portfolio {
    color: var(--cyan);
    font-weight: 600;
}

/* Распределение оценок */
.rating-distribution {
    margin-bottom: 40px;
    padding: 24px;
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: 20px;
}

.rating-distribution h2 {
    font-family: var(--disp);
    font-size: 20px;
    margin: 0 0 20px;
}

.rating-bars {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.rating-bar-row {
    display: flex;
    align-items: center;
    gap: 12px;
}

.rating-bar-label {
    width: 40px;
    font-size: 14px;
    font-weight: 700;
    color: var(--yellow);
    text-align: right;
}

.rating-bar-track {
    flex: 1;
    height: 8px;
    background: var(--panel2);
    border-radius: 99px;
    overflow: hidden;
}

.rating-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--yellow), var(--orange));
    border-radius: 99px;
    transition: width 0.5s;
}

.rating-bar-count {
    width: 30px;
    font-size: 13px;
    color: var(--mut);
    text-align: right;
}

/* Отзывы */
.partner-reviews h2 {
    font-family: var(--disp);
    font-size: 22px;
    margin: 0 0 20px;
}

.review-card {
    padding: 20px;
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: 16px;
    margin-bottom: 14px;
}

.review-head {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.review-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    background: linear-gradient(135deg, var(--cyan), var(--violet));
    color: #fff;
    font-weight: 800;
    font-size: 16px;
    flex-shrink: 0;
}

.review-meta {
    flex: 1;
}

.review-meta b {
    display: block;
    font-size: 14px;
    margin-bottom: 2px;
}

.review-meta small {
    color: var(--mut);
    font-size: 12px;
}

.review-rating {
    font-size: 16px;
    color: var(--yellow);
    letter-spacing: 2px;
}

.review-text {
    color: var(--txt);
    font-size: 14px;
    line-height: 1.6;
    margin: 0;
}

.review-reply {
    margin-top: 14px;
    padding: 12px;
    background: var(--panel2);
    border-left: 3px solid var(--cyan);
    border-radius: 8px;
    font-size: 13px;
}

.review-reply b {
    display: block;
    margin-bottom: 6px;
    color: var(--cyan);
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.review-reply p {
    margin: 0;
    color: var(--mut);
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--mut);
}

.empty-ico {
    font-size: 56px;
    margin-bottom: 16px;
    opacity: 0.5;
}

.empty-state h3 {
    font-family: var(--disp);
    font-size: 22px;
    margin: 0 0 8px;
    color: var(--txt);
}

@media (max-width: 768px) {
    .partner-hero {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .partner-badges {
        justify-content: center;
    }

    .partner-meta {
        justify-content: center;
    }
}
</style>
