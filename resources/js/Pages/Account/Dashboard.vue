<script setup>
import { Link } from '@inertiajs/vue3'
import VCard from '@/Components/ui/VCard.vue'
import VBadge from '@/Components/ui/VBadge.vue'
import VButton from '@/Components/ui/VButton.vue'
import { useLabels } from '@/composables/useLabels'

const props = defineProps(['places', 'series', 'totals', 'newsCount', 'onModeration'])
const { badge, L } = useLabels()

const max = Math.max(...props.series.map((s) => +s.views), 1)
</script>

<template>
    <div>
        <div class="pg-head"><h1>Кабинет заведения</h1></div>

        <div class="grid grid--4">
            <VCard><b class="stat">{{ totals?.views ?? 0 }}</b><span>просмотров · 30 дней</span></VCard>
            <VCard><b class="stat" style="color:var(--cyan)">{{ totals?.clicks ?? 0 }}</b><span>кликов (телефон/маршрут)</span></VCard>
            <VCard><b class="stat" style="color:var(--lime)">{{ totals?.favs ?? 0 }}</b><span>в избранном</span></VCard>
            <VCard><b class="stat" style="color:var(--yellow)">{{ onModeration }}</b><span>на модерации</span></VCard>
        </div>

        <div class="grid grid--2" style="margin-top:16px">
            <VCard>
                <h3 style="margin-top:0">Просмотры · 14 дней</h3>
                <div class="bars">
                    <div v-for="s in series" :key="s.date" class="bar" :style="{ height: (s.views / max) * 100 + '%' }" :title="`${s.date}: ${s.views}`" />
                </div>
            </VCard>

            <VCard>
                <h3 style="margin-top:0">Мои заведения</h3>
                <div v-for="p in places" :key="p.id" class="row">
                    <div>
                        <b>{{ p.name }}</b><br>
                        <span style="color:var(--mut);font-size:12.5px">{{ p.category?.name }} · {{ p.district?.name }}</span>
                    </div>
                    <div style="display:flex;gap:8px;align-items:center">
                        <VBadge v-bind="badge(L.moderation, p.status)" />
                        <Link :href="`/account/places/${p.id}/edit`" class="tlink">✏️</Link>
                        <Link :href="`/account/places/${p.id}/analytics`" class="tlink">📈</Link>
                    </div>
                </div>
                <p v-if="!places.length" style="color:var(--mut)">Пока нет заведений — дождитесь одобрения заявки.</p>
                <Link href="/account/news" style="display:inline-block;margin-top:12px;color:var(--cyan);font-weight:800">📰 Новости ({{ newsCount }}) →</Link>
            </VCard>
        </div>
    </div>
</template>

<style scoped>
.stat{font-family:var(--disp);font-size:26px;display:block}
.vcard span{color:var(--mut);font-size:12.5px}
.row{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:10px 0;border-top:1px solid var(--line)}
</style>
