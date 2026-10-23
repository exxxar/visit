<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps(['reviews'])

const fmtDate = (d) => new Date(d).toLocaleDateString('ru-RU', {
    day: '2-digit', month: '2-digit', year: 'numeric'
})

const deleteReview = (id) => {
    if (!confirm('Удалить отзыв? Это действие нельзя отменить.')) return

    fetch(`/api/v1/places/reviews/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    }).then(() => {
        window.location.reload()
    })
}
</script>

<template>
    <div>
        <div class="pg-head">
            <h1>⭐ Мои отзывы</h1>
        </div>

        <div v-if="reviews.data.length">
            <div v-for="review in reviews.data" :key="review.id" class="review-card">
                <div class="review-card__head">
                    <Link :href="`/place/${review.place.slug}`" class="review-card__place">
                        {{ review.place.name }}
                    </Link>
                    <div class="review-card__stars">
                        {{ '★'.repeat(review.rating) }}{{ '☆'.repeat(5 - review.rating) }}
                    </div>
                </div>

                <p class="review-card__text">{{ review.content }}</p>

                <div v-if="review.photos?.length" class="review-card__photos">
                    <img v-for="(photo, i) in review.photos_urls" :key="i" :src="photo" alt="" class="review-card__photo">
                </div>

                <div class="review-card__footer">
                    <span :class="`status status--${review.status}`">
                        {{ review.status === 'approved' ? '✓ Опубликовано' : review.status === 'pending' ? '⏳ На модерации' : '✕ Отклонено' }}
                    </span>
                    <span class="review-card__date">{{ fmtDate(review.created_at) }}</span>
                    <button @click="deleteReview(review.id)" class="review-card__delete">
                        🗑 Удалить
                    </button>
                </div>

                <div v-if="review.reply" class="review-card__reply">
                    <b>Ответ владельца:</b>
                    <p>{{ review.reply.content }}</p>
                </div>
            </div>
        </div>

        <div v-else class="vcard" style="text-align:center;padding:60px 20px">
            <div style="font-size:48px;margin-bottom:16px">⭐</div>
            <h3 style="margin:0 0 8px">Вы пока не оставляли отзывов</h3>
            <p style="color:var(--mut);margin:0 0 20px">Поделитесь впечатлениями о заведениях</p>
            <Link href="/resources/js/Pages/Public" class="btn btn-grad">Перейти на карту</Link>
        </div>

        <div v-if="reviews.links.length > 3" class="pager" style="margin-top:24px">
            <Link v-for="l in reviews.links" :key="l.label" :href="l.url ?? '#'" preserve-scroll
                  :class="{ on: l.active, off: !l.url }" v-html="l.label" />
        </div>
    </div>
</template>

<style scoped>
.review-card {
    border:1px solid var(--line);
    border-radius:14px;
    padding:20px;
    margin-bottom:16px;
    background:var(--panel);
}
.review-card__head {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:12px;
}
.review-card__place {
    font-weight:700;
    font-size:16px;
    color:var(--cyan);
    text-decoration:none;
}
.review-card__stars {
    color:var(--yellow);
    font-size:18px;
}
.review-card__text {
    color:var(--txt);
    line-height:1.6;
    margin:0 0 12px;
}
.review-card__photos {
    display:flex;
    gap:8px;
    margin-bottom:12px;
    flex-wrap:wrap;
}
.review-card__photo {
    width:80px;
    height:80px;
    object-fit:cover;
    border-radius:8px;
    cursor:pointer;
}
.review-card__footer {
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding-top:12px;
    border-top:1px solid var(--line);
    font-size:13px;
}
.review-card__date {
    color:var(--mut);
}
.review-card__delete {
    background:none;
    border:none;
    color:var(--rose);
    cursor:pointer;
    font-size:13px;
    font-weight:600;
}
.review-card__delete:hover {
    text-decoration:underline;
}
.review-card__reply {
    margin-top:12px;
    padding:12px;
    background:rgba(34,211,238,.05);
    border-left:3px solid var(--cyan);
    border-radius:8px;
}
.review-card__reply b {
    display:block;
    margin-bottom:6px;
    color:var(--cyan);
    font-size:13px;
}
.review-card__reply p {
    margin:0;
    color:var(--mut);
    font-size:14px;
}

.status {
    padding:4px 10px;
    border-radius:6px;
    font-weight:600;
    font-size:12px;
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
