<script setup>
import { ref, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import VButton from '@/Components/ui/VButton.vue'

const props = defineProps(['request', 'partner', 'existingReview'])

const form = useForm({
    request_id: props.request.id,
    request_type: props.request.type,
    rating: props.existingReview?.rating || 0,
    comment: props.existingReview?.comment || '',
    criteria: {},
})

const hoverRating = ref(0)
const displayRating = computed(() => hoverRating.value || form.rating)

const ratingLabels = {
    1: 'Ужасно',
    2: 'Плохо',
    3: 'Нормально',
    4: 'Хорошо',
    5: 'Отлично',
}

const submit = () => {
    if (form.rating === 0) {
        alert('Пожалуйста, оцените работу партнёра')
        return
    }
    form.post(route('account.review.store'), {
        preserveScroll: true,
    })
}

const fmtMoney = (n) => new Intl.NumberFormat('ru-RU').format(n) + ' ₽'
</script>

<template>
    <div>
        <div class="pg-head">
            <h1>⭐ Оставить отзыв</h1>
        </div>

        <div class="vcard" style="max-width:700px">
            <!-- Информация о заказе -->
            <div class="review-info">
                <div class="review-info__header">
                    <div>
                        <h3 style="margin:0 0 6px">{{ request.title }}</h3>
                        <p style="color:var(--mut);font-size:13px;margin:0">
                            Завершено {{ request.completed_at }}
                        </p>
                    </div>
                    <div style="text-align:right">
                        <div style="font-family:var(--disp);font-size:20px;font-weight:800;color:var(--cyan)">
                            {{ fmtMoney(request.amount) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Информация о партнёре -->
            <div class="partner-info">
                <div class="partner-info__avatar">
                    {{ partner.name[0].toUpperCase() }}
                </div>
                <div>
                    <b>{{ partner.name }}</b>
                    <div v-if="partner.reviews_count > 0" class="partner-info__rating">
                        <span class="stars">★</span>
                        {{ partner.rating.toFixed(1) }}
                        <small>({{ partner.reviews_count }} отзывов)</small>
                    </div>
                    <div v-else class="partner-info__rating">
                        <small style="color:var(--mut)">Ещё нет отзывов</small>
                    </div>
                </div>
            </div>

            <!-- Оценка звёздами -->
            <div class="rating-section">
                <label class="lbl">Ваша оценка</label>
                <div class="stars-input">
                    <button
                        v-for="i in 5"
                        :key="i"
                        type="button"
                        class="star-btn"
                        :class="{ active: i <= displayRating }"
                        @click="form.rating = i"
                        @mouseenter="hoverRating = i"
                        @mouseleave="hoverRating = 0"
                    >
                        ★
                    </button>
                    <span v-if="displayRating > 0" class="rating-label">
                        {{ ratingLabels[displayRating] }}
                    </span>
                </div>
            </div>

            <!-- Комментарий -->
            <div class="comment-section">
                <label class="lbl">Комментарий (необязательно)</label>
                <textarea
                    v-model="form.comment"
                    class="inp"
                    rows="5"
                    placeholder="Расскажите о вашем опыте работы с партнёром..."
                    maxlength="1000"
                ></textarea>
                <div class="char-counter">
                    {{ form.comment.length }} / 1000
                </div>
            </div>

            <!-- Кнопки -->
            <div class="review-actions">
                <VButton variant="ghost" @click="router.back()">Отмена</VButton>
                <VButton :busy="form.processing" @click="submit">
                    {{ existingReview ? 'Обновить отзыв' : 'Отправить отзыв' }}
                </VButton>
            </div>
        </div>
    </div>
</template>

<style scoped>
.review-info {
    padding: 18px;
    background: var(--panel2);
    border: 1px solid var(--line);
    border-radius: 14px;
    margin-bottom: 20px;
}

.review-info__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
}

.partner-info {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px;
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: 14px;
    margin-bottom: 24px;
}

.partner-info__avatar {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    background: linear-gradient(135deg, var(--cyan), var(--violet));
    color: #fff;
    font-size: 22px;
    font-weight: 800;
    font-family: var(--disp);
    flex-shrink: 0;
}

.partner-info b {
    display: block;
    font-size: 16px;
    margin-bottom: 4px;
}

.partner-info__rating {
    font-size: 14px;
    color: var(--mut);
}

.partner-info__rating .stars {
    color: var(--yellow);
    margin-right: 4px;
}

.partner-info__rating small {
    margin-left: 6px;
    font-size: 12px;
}

.rating-section {
    margin-bottom: 24px;
}

.stars-input {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 10px;
}

.star-btn {
    font-size: 36px;
    color: var(--line);
    background: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    padding: 0;
    line-height: 1;
}

.star-btn:hover,
.star-btn.active {
    color: var(--yellow);
    transform: scale(1.15);
}

.rating-label {
    margin-left: 12px;
    font-size: 14px;
    font-weight: 700;
    color: var(--yellow);
}

.comment-section {
    margin-bottom: 24px;
}

.char-counter {
    text-align: right;
    font-size: 12px;
    color: var(--mut);
    margin-top: 6px;
}

.review-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    padding-top: 20px;
    border-top: 1px solid var(--line);
}
</style>
