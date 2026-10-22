<script setup>
import { ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import VButton from '@/Components/ui/VButton.vue'
import VBadge from '@/Components/ui/VBadge.vue'

const props = defineProps(['requests', 'counts', 'services'])
const userId = usePage().props.auth?.user?.id

const filter  = ref('')
const service = ref('')

const apply = () => router.get('/account/promotions',
    { filter: filter.value, service: service.value },
    { preserveScroll: true }
)

const claim = (r) => {
    if (!confirm(`Взять заявку «${r.title}»?`)) return
    router.post(`/account/promotions/${r.id}/claim`, {}, { preserveScroll: true })
}

const setStatus = (r, status) =>
    router.post(`/account/promotions/${r.id}/status`, { status }, { preserveScroll: true })

const badge = {
    new:         { label: '🆕 Новая',      color: 'cyan' },
    claimed:     { label: '👋 Взята',       color: 'yellow' },
    in_progress: { label: '⚙️ В процессе',  color: 'violet' },
    completed:   { label: '✅ Завершена',   color: 'lime' },
    cancelled:   { label: '❌ Отменена',    color: 'rose' },
}

const money = n => new Intl.NumberFormat('ru-RU').format(n) + ' ₽'
const date  = d => new Date(d).toLocaleDateString('ru-RU')
</script>

<template>
    <div>
        <div class="pg-head"><h1>📣 Заявки на продвижение</h1></div>

        <div class="p-stats">
            <div class="p-stat"><b style="color:var(--cyan)">{{ counts.available }}</b><span>доступных</span></div>
            <div class="p-stat"><b style="color:var(--yellow)">{{ counts.my }}</b><span>в работе</span></div>
            <div class="p-stat"><b style="color:var(--lime)">{{ counts.completed }}</b><span>завершено</span></div>
            <div class="p-stat"><b style="color:var(--magenta,#f050e0)">{{ money(counts.earnings) }}</b><span>заработано</span></div>
        </div>

        <div class="vcard p-filters">
            <button class="p-chip" :class="{ on: filter === '' }" @click="filter=''; apply()">Все</button>
            <button class="p-chip" :class="{ on: filter === 'available' }" @click="filter='available'; apply()">
                🆕 Доступные
            </button>
            <button class="p-chip" :class="{ on: filter === 'my' }" @click="filter='my'; apply()">
                👋 Мои
            </button>
            <select v-model="service" class="inp" style="max-width:230px;margin-left:auto" @change="apply">
                <option value="">Все услуги</option>
                <option v-for="(s, key) in services" :key="key" :value="key">{{ s.icon }} {{ s.label }}</option>
            </select>
        </div>

        <div v-if="!requests.data.length" class="vcard" style="text-align:center;padding:40px;color:var(--mut)">
            📭 Заявок пока нет
        </div>

        <div v-for="r in requests.data" :key="r.id" class="vcard p-card">
            <div class="p-card__head">
                <div class="p-card__svc">
                    <span class="p-card__ico">{{ r.service_icon }}</span>
                    <div>
                        <b>{{ r.title }}</b>
                        <small>{{ date(r.created_at) }}</small>
                    </div>
                </div>
                <VBadge v-bind="badge[r.status]" />
            </div>

            <div class="p-card__info">
                <div><span>Заведение:</span> <b>{{ r.place?.name ?? '—' }}</b></div>
                <div><span>Клиент:</span> <b>{{ r.client?.name }}</b> <small>{{ r.client?.email }}</small></div>
                <div><span>Бюджет:</span> <b class="p-money">{{ money(r.amount) }}</b></div>
            </div>

            <p v-if="r.requirements" class="p-req">{{ r.requirements }}</p>

            <div class="p-card__act">
                <template v-if="r.status === 'new' && !r.partner_id">
                    <VButton @click="claim(r)">👋 Взять в работу</VButton>
                </template>

                <template v-else-if="r.partner_id === userId">
                    <span class="p-mine">✓ Ваша заявка</span>
                    <select :value="r.status" class="inp" style="max-width:180px"
                            @change="setStatus(r, $event.target.value)">
                        <option value="claimed">Взята</option>
                        <option value="in_progress">В процессе</option>
                        <option value="completed">Завершена</option>
                        <option value="cancelled">Отменена</option>
                    </select>
                </template>

                <span v-else style="color:var(--mut);font-size:13px">
                    В работе: {{ r.partner?.name ?? '—' }}
                </span>
            </div>
        </div>

        <div class="pager">
            <Link v-for="l in requests.links" :key="l.label" :href="l.url ?? '#'" preserve-scroll
                  :class="{ on: l.active, off: !l.url }" v-html="l.label" />
        </div>
    </div>
</template>

<style scoped>
.p-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px}
.p-stat{background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:16px 18px;
    display:flex;flex-direction:column;gap:4px}
.p-stat b{font-family:var(--disp);font-size:24px;font-weight:800}
.p-stat span{color:var(--mut);font-size:12px}

.p-filters{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin:18px 0}
.p-chip{padding:8px 16px;border-radius:99px;background:var(--panel);border:1px solid var(--line);
    color:var(--txt);font-size:13px;font-weight:600;cursor:pointer;transition:.2s}
.p-chip:hover{border-color:var(--magenta,#f050e0)}
.p-chip.on{background:linear-gradient(90deg,#f050e0,#8b5cf6);border-color:transparent;color:#fff}

.p-card{padding:18px 22px;margin-bottom:12px;display:flex;flex-direction:column;gap:14px}
.p-card__head{display:flex;justify-content:space-between;align-items:center;gap:12px}
.p-card__svc{display:flex;align-items:center;gap:12px}
.p-card__ico{width:48px;height:48px;border-radius:12px;display:grid;place-items:center;font-size:24px;
    background:linear-gradient(135deg,rgba(240,80,224,.15),rgba(139,92,246,.15));
    border:1px solid rgba(240,80,224,.25)}
.p-card__svc b{display:block;font-size:15px}
.p-card__svc small{color:var(--mut);font-size:12px}

.p-card__info{display:flex;flex-direction:column;gap:8px;padding-top:12px;border-top:1px solid var(--line)}
.p-card__info>div{display:flex;align-items:baseline;gap:8px;font-size:13px;flex-wrap:wrap}
.p-card__info span{color:var(--mut);min-width:85px}
.p-card__info small{color:var(--mut);font-size:12px}
.p-money{font-family:var(--disp);font-size:18px;font-weight:800;color:var(--magenta,#f050e0)}

.p-req{margin:0;padding:12px 14px;background:var(--panel);border:1px solid var(--line);
    border-radius:12px;font-size:13px;color:var(--mut);line-height:1.5;white-space:pre-wrap}

.p-card__act{display:flex;align-items:center;gap:12px;flex-wrap:wrap;padding-top:12px;border-top:1px solid var(--line)}
.p-mine{color:var(--lime);font-weight:700;font-size:13px}
</style>
