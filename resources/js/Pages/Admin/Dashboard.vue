<script setup>
import { Link } from '@inertiajs/vue3'
import VCard from '@/Components/ui/VCard.vue'

const props = defineProps(['queues', 'viewsSeries', 'topPlaces', 'counters'])

const queueTotal = Object.values(props.queues).reduce((a, b) => a + b, 0)
const max = Math.max(...props.viewsSeries.map((v) => v.views), 1)
</script>

<template>
    <div>
        <div class="pg-head"><h1>Дашборд</h1></div>

        <div class="grid grid--4">
            <VCard><b class="stat">{{ counters.places }}</b><span>мест опубликовано</span></VCard>
            <VCard><b class="stat" style="color:var(--yellow)">{{ queueTotal }}</b><span>в очереди модерации</span></VCard>
            <VCard><b class="stat" style="color:var(--cyan)">{{ counters.leads_week }}</b><span>лидов за 7 дней</span></VCard>
            <VCard><b class="stat" style="color:var(--lime)">{{ counters.subscribers }}</b><span>подписчиков</span></VCard>
        </div>

        <div class="grid grid--2" style="margin-top:16px">
            <VCard>
                <h3 style="margin-top:0">Просмотры · 14 дней</h3>
                <div class="bars">
                    <div v-for="v in viewsSeries" :key="v.date" class="bar"
                         :style="{ height: (v.views / max) * 100 + '%' }" :title="`${v.date}: ${v.views}`" />
                </div>
            </VCard>

            <VCard>
                <h3 style="margin-top:0">Очередь модерации</h3>
                <div class="tabs">
                    <Link class="tab" href="/admin/moderation?tab=places">Заведения<span class="cnt">{{ queues.places }}</span></Link>
                    <Link class="tab" href="/admin/moderation?tab=news">Новости<span class="cnt">{{ queues.news }}</span></Link>
                    <Link class="tab" href="/admin/moderation?tab=events">Афиша<span class="cnt">{{ queues.events }}</span></Link>
                    <Link class="tab" href="/admin/moderation?tab=reviews">Отзывы<span class="cnt">{{ queues.reviews }}</span></Link>
                    <Link class="tab" href="/admin/moderation?tab=applications">Заявки<span class="cnt">{{ queues.applications }}</span></Link>
                </div>

                <h3>Топ по просмотрам</h3>
                <div v-for="p in topPlaces" :key="p.id" class="toprow">
                    <span>{{ p.category?.icon }} {{ p.name }}</span>
                    <b>{{ p.views_count }}</b>
                </div>
            </VCard>
        </div>
    </div>
</template>

<style scoped>
.stat{font-family:var(--disp);font-size:28px;display:block}
.vcard span{color:var(--mut);font-size:12.5px}
.toprow{display:flex;justify-content:space-between;padding:8px 0;border-top:1px solid var(--line);font-size:14px}
</style>
