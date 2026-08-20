<script setup>
import { Link } from '@inertiajs/vue3'
import VCard from '@/Components/ui/VCard.vue'

const props = defineProps(['place', 'myPlaces', 'series', 'sources'])

const maxViews = Math.max(...props.series.map((s) => s.views), 1)
const maxSrc   = Math.max(...props.sources.map((s) => s.c), 1)
</script>

<template>
    <div>
        <div class="pg-head">
            <h1 style="font-size:20px">📈 {{ place.name }}</h1>
            <select class="inp" style="margin-left:auto;width:auto" :value="place.id"
                    @change="Link.visit ? null : null; location.href = `/account/places/${$event.target.value}/analytics`">
                <option v-for="p in myPlaces" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
        </div>

        <div class="grid grid--4" style="margin-bottom:16px">
            <VCard><b class="stat">{{ place.views_count }}</b><span>просмотров всего</span></VCard>
            <VCard><b class="stat" style="color:var(--lime)">★ {{ place.rating }}</b><span>рейтинг</span></VCard>
            <VCard><b class="stat" style="color:var(--cyan)">{{ place.reviews_count }}</b><span>отзывов</span></VCard>
            <VCard><b class="stat" style="color:var(--yellow)">{{ series.reduce((a, s) => a + s.views, 0) }}</b><span>просмотров · 30 дней</span></VCard>
        </div>

        <div class="grid grid--2">
            <VCard>
                <h3 style="margin-top:0">Просмотры по дням</h3>
                <div class="bars" style="height:160px">
                    <div v-for="s in series" :key="s.date" class="bar" :style="{ height: (s.views / maxViews) * 100 + '%' }"
                         :title="`${s.date} · ${s.views} просм. · ${s.clicks} клик.`" />
                </div>
            </VCard>
            <VCard>
                <h3 style="margin-top:0">Откуда приходят</h3>
                <div v-for="s in sources" :key="s.source" style="margin-bottom:14px">
                    <div style="display:flex;justify-content:space-between;font-size:13.5px;margin-bottom:6px">
                        <span>{{ s.source ?? 'прямые' }}</span><b>{{ s.c }}</b>
                    </div>
                    <div style="height:8px;border-radius:99px;background:var(--panel2);overflow:hidden">
                        <div style="height:100%;background:linear-gradient(90deg,var(--cyan),var(--violet));border-radius:99px"
                             :style="{ width: (s.c / maxSrc) * 100 + '%' }"></div>
                    </div>
                </div>
                <p v-if="!sources.length" style="color:var(--mut)">Пока нет данных за 30 дней.</p>
            </VCard>
        </div>
    </div>
</template>

<style scoped>
.stat{font-family:var(--disp);font-size:26px;display:block}
.vcard span{color:var(--mut);font-size:12.5px}
</style>
