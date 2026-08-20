<script setup>
import VCard from '@/Components/ui/VCard.vue'
import VBadge from '@/Components/ui/VBadge.vue'

const props = defineProps(['place', 'series', 'sources'])

const maxViews = Math.max(...props.series.map((s) => s.views), 1)
const maxSrc   = Math.max(...props.sources.map((s) => s.c), 1)
</script>

<template>
    <div>
        <div class="pg-head">
            <h1 style="font-size:20px">📈 {{ place.name }}</h1>
            <a class="vbtn vbtn--ghost" :href="`/admin/places/${place.id}/edit`" style="margin-left:auto">✏️ Редактировать</a>
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
                    <div v-for="s in series" :key="s.date" class="bar"
                         :style="{ height: (s.views / maxViews) * 100 + '%' }"
                         :title="`${s.date} · ${s.views} просм. · ${s.clicks} клик. · ${s.favorites} в избранное`" />
                </div>
            </VCard>

            <VCard>
                <h3 style="margin-top:0">Источники трафика</h3>
                <div v-for="s in sources" :key="s.source" class="srcrow">
                    <div class="srcrow__head"><span>{{ s.source ?? 'прямые' }}</span><b>{{ s.c }}</b></div>
                    <div class="srcrow__track"><div class="srcrow__fill" :style="{ width: (s.c / maxSrc) * 100 + '%' }" /></div>
                </div>
                <p v-if="!sources.length" style="color:var(--mut)">Пока нет данных за 30 дней.</p>
            </VCard>
        </div>
    </div>
</template>

<style scoped>
.stat{font-family:var(--disp);font-size:26px;display:block}
.vcard span{color:var(--mut);font-size:12.5px}
.srcrow{margin-bottom:14px}
.srcrow__head{display:flex;justify-content:space-between;font-size:13.5px;margin-bottom:6px}
.srcrow__track{height:8px;border-radius:99px;background:var(--panel2);overflow:hidden}
.srcrow__fill{height:100%;border-radius:99px;background:linear-gradient(90deg,var(--cyan),var(--violet))}
</style>
