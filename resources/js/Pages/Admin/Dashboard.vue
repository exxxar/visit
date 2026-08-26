<script setup>
import { Link } from '@inertiajs/vue3'
import VCard from '@/Components/ui/VCard.vue'

const props = defineProps(['queues', 'viewsSeries', 'topPlaces', 'counters', 'feedback'])

// защита от отсутствующего ключа
const feedback = props.feedback ?? {
    new: 0,
    in_progress: 0,
    resolved: 0,
    week: 0,
    recent: [],
}

const queueTotal = Object.values(props.queues).reduce((a, b) => a + b, 0)
const max = Math.max(...props.viewsSeries.map((v) => v.views), 1)

const subjectIcon = (s) => ({
    general: '💬', partnership: '🤝', ads: '📣',
    bug: '🐛', suggestion: '💡', complaint: '⚠️',
}[s] ?? '📌')

const timeAgo = (iso) => {
    const diff = Date.now() - new Date(iso).getTime()
    const min = Math.floor(diff / 60000)
    if (min < 1) return 'только что'
    if (min < 60) return `${min} мин назад`
    const h = Math.floor(min / 60)
    if (h < 24) return `${h} ч назад`
    const d = Math.floor(h / 24)
    if (d === 1) return 'вчера'
    if (d < 7) return `${d} дн назад`
    return new Date(iso).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit' })
}
</script>

<template>
    <div>
        <div class="pg-head"><h1>Дашборд</h1></div>

        <div class="grid grid--4">
            <VCard><b class="stat">{{ counters.places }}</b><span>мест опубликовано</span></VCard>
            <VCard><b class="stat" style="color:var(--yellow)">{{ queueTotal }}</b><span>в очереди модерации</span></VCard>
            <VCard><b class="stat" style="color:var(--cyan)">{{ counters.leads_week }}</b><span>лидов за 7 дней</span></VCard>
            <VCard>
                <b class="stat" style="color:var(--magenta,#f050e0)">{{ feedback.new }}</b>
                <span>новых обращений</span>
            </VCard>
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

                <!-- ===== ОБРАТНАЯ СВЯЗЬ ===== -->
                <h3 style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                    <span>✉ Обратная связь</span>
                    <span class="fb-week">+{{ feedback.week }} за 7 дней</span>
                </h3>

                <div class="fb-chips">
                    <span class="fb-chip fb-chip--new">● {{ feedback.new }} новых</span>
                    <span class="fb-chip fb-chip--progress">● {{ feedback.in_progress }} в работе</span>
                    <span class="fb-chip fb-chip--resolved">● {{ feedback.resolved }} закрыто</span>
                </div>

                <div v-if="feedback.recent.length">
                    <Link v-for="f in feedback.recent" :key="f.id"
                          href="/admin/feedback" class="fb-recent">
                        <span class="fb-recent__ico">{{ subjectIcon(f.subject) }}</span>
                        <span class="fb-recent__name">{{ f.name }}</span>
                        <span class="fb-recent__subject">{{ f.subjectLabel }}</span>
                        <b class="fb-recent__time">{{ timeAgo(f.created_at) }}</b>
                    </Link>
                </div>
                <p v-else class="fb-empty">Пока нет обращений</p>

                <Link href="/admin/feedback" class="fb-all">
                    Все обращения <span style="opacity:.7">→</span>
                </Link>

                <!-- ===== ТОП ПО ПРОСМОТРАМ ===== -->
                <h3 style="margin-top:18px">Топ по просмотрам</h3>
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

/* ===== обратная связь ===== */
.fb-week{
    font-size:11px;color:var(--mut);font-weight:600;
    padding:3px 10px;background:var(--panel);border:1px solid var(--line);
    border-radius:99px;
}

.fb-chips{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px}
.fb-chip{
    font-size:12px;font-weight:700;padding:5px 12px;border-radius:99px;
    letter-spacing:.03em;
}
.fb-chip--new      {background:rgba(34,211,238,.12); color:#22d3ee;border:1px solid rgba(34,211,238,.3)}
.fb-chip--progress {background:rgba(250,204,21,.12);color:#facc15;border:1px solid rgba(250,204,21,.3)}
.fb-chip--resolved {background:rgba(163,230,53,.12);color:#a3e635;border:1px solid rgba(163,230,53,.3)}

.fb-recent{
    display:flex;align-items:center;gap:8px;
    padding:9px 10px;margin:0 -10px;
    border-radius:10px;text-decoration:none;color:var(--txt);
    font-size:13px;transition:.2s;
}
.fb-recent:hover{background:var(--panel)}
.fb-recent__ico{font-size:16px;flex-shrink:0}
.fb-recent__name{font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.fb-recent__subject{
    color:var(--mut);font-size:12px;
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;flex:1;
}
.fb-recent__time{
    color:var(--mut);font-size:11px;font-weight:600;
    white-space:nowrap;flex-shrink:0;
}

.fb-empty{color:var(--mut);font-size:13px;text-align:center;padding:16px 0}

.fb-all{
    display:block;text-align:center;
    padding:10px;margin-top:10px;
    background:linear-gradient(135deg,rgba(240,80,224,.12),rgba(139,92,246,.12));
    border:1px solid rgba(240,80,224,.25);
    border-radius:12px;text-decoration:none;
    color:var(--magenta,#f050e0);font-weight:700;font-size:13px;
    transition:.2s;
}
.fb-all:hover{
    background:linear-gradient(135deg,rgba(240,80,224,.2),rgba(139,92,246,.2));
    transform:translateY(-1px);
}
</style>
