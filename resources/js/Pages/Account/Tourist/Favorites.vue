<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps(['favorites'])

const removeFromFavorites = (placeId) => {
    if (!confirm('Удалить из избранного?')) return

    fetch(`/api/v1/places/${placeId}/favorite`, {
        method: 'POST',
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
            <h1>❤️ Избранные места</h1>
        </div>

        <div v-if="favorites.data.length" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px">
            <div v-for="fav in favorites.data" :key="fav.id" class="place-card">
                <Link :href="`/place/${fav.place.slug}`" class="place-card__link">
                    <img :src="fav.place.cover_url || '/assets/placeholder.jpg'" :alt="fav.place.name" class="place-card__img">
                    <div class="place-card__body">
                        <h3>{{ fav.place.name }}</h3>
                        <p class="place-card__meta">
                            {{ fav.place.category?.icon }} {{ fav.place.category?.name }} · {{ fav.place.district?.name }}
                        </p>
                        <div class="place-card__footer">
                            <span class="place-card__rating">★ {{ fav.place.rating?.toFixed(1) || '—' }}</span>
                            <span class="place-card__reviews">{{ fav.place.reviews_count }} отзывов</span>
                        </div>
                    </div>
                </Link>
                <button @click="removeFromFavorites(fav.place.id)" class="place-card__remove">
                    ✕ Удалить
                </button>
            </div>
        </div>

        <div v-else class="vcard" style="text-align:center;padding:60px 20px">
            <div style="font-size:48px;margin-bottom:16px">❤️</div>
            <h3 style="margin:0 0 8px">Пока пусто</h3>
            <p style="color:var(--mut);margin:0 0 20px">Добавляйте места в избранное на карте</p>
            <Link href="/resources/js/Pages/Public" class="btn btn-grad">Перейти на карту</Link>
        </div>

        <div v-if="favorites.links.length > 3" class="pager" style="margin-top:24px">
            <Link v-for="l in favorites.links" :key="l.label" :href="l.url ?? '#'" preserve-scroll
                  :class="{ on: l.active, off: !l.url }" v-html="l.label" />
        </div>
    </div>
</template>

<style scoped>
.place-card {
    border:1px solid var(--line);
    border-radius:14px;
    overflow:hidden;
    background:var(--panel);
    transition:.2s;
}
.place-card:hover {
    border-color:var(--cyan);
    transform:translateY(-2px);
}
.place-card__link {
    text-decoration:none;
    color:var(--txt);
    display:block;
}
.place-card__img {
    width:100%;
    height:160px;
    object-fit:cover;
}
.place-card__body {
    padding:14px;
}
.place-card__body h3 {
    margin:0 0 6px;
    font-size:16px;
}
.place-card__meta {
    color:var(--mut);
    font-size:13px;
    margin:0 0 10px;
}
.place-card__footer {
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.place-card__rating {
    color:var(--yellow);
    font-weight:700;
    font-size:15px;
}
.place-card__reviews {
    color:var(--mut);
    font-size:12px;
}
.place-card__remove {
    width:100%;
    padding:10px;
    background:rgba(255,92,122,.1);
    border:none;
    border-top:1px solid var(--line);
    color:var(--rose);
    font-weight:600;
    cursor:pointer;
    transition:.2s;
}
.place-card__remove:hover {
    background:rgba(255,92,122,.2);
}
</style>
