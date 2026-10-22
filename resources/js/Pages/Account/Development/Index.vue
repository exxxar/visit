<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import VButton from '@/Components/ui/VButton.vue'
import VBadge from '@/Components/ui/VBadge.vue'

const props = defineProps(['requests', 'counts', 'services'])

const filter = ref('')
const service = ref('')

const applyFilter = (f) => {
    filter.value = f
    router.get('/account/development', { filter: f, service: service.value }, { preserveScroll: true })
}

const claim = (req) => {
    if (!confirm(`Взять в работу заявку «${req.title}» для ${req.place?.name ?? 'клиента'}?`)) return
    router.post(`/account/development/${req.id}/claim`, {}, { preserveScroll: true })
}

const updateStatus = (req, status) => {
    router.post(`/account/development/${req.id}/status`, { status }, { preserveScroll: true })
}

const statusBadge = {
    new:         { label: '🆕 Новая',         color: 'cyan' },
    claimed:     { label: '👋 Взята',          color: 'yellow' },
    in_progress: { label: '⚙️ В процессе',     color: 'violet' },
    completed:   { label: '✅ Завершена',      color: 'lime' },
    cancelled:   { label: '❌ Отменена',       color: 'rose' },
}

const fmtDate = (d) => new Date(d).toLocaleDateString('ru-RU', {
    day: '2-digit', month: '2-digit', year: 'numeric'
})

const fmtMoney = (n) => new Intl.NumberFormat('ru-RU').format(n) + ' ₽'
</script>

<template>
    <div>
        <div class="pg-head">
            <h1>💻 Заявки на разработку</h1>
        </div>

        <!-- сводка -->
        <div class="stats-grid">
            <div class="stat-card">
                <b style="color:var(--cyan)">{{ counts.available }}</b>
                <span>доступных заявок</span>
            </div>
            <div class="stat-card">
                <b style="color:var(--yellow)">{{ counts.my }}</b>
                <span>в работе у вас</span>
            </div>
            <div class="stat-card">
                <b style="color:var(--lime)">{{ counts.completed }}</b>
                <span>завершено</span>
            </div>
            <div class="stat-card">
                <b style="color:var(--magenta,#f050e0)">{{ fmtMoney(counts.earnings) }}</b>
                <span>заработано</span>
            </div>
        </div>

        <!-- фильтры -->
        <div class="vcard" style="margin:18px 0;display:flex;gap:12px;flex-wrap:wrap;align-items:center">
            <button class="filter-chip" :class="{ on: filter === '' }" @click="applyFilter('')">Все</button>
            <button class="filter-chip" :class="{ on: filter === 'available' }" @click="applyFilter('available')">
                🆕 Доступные <span class="cnt">{{ counts.available }}</span>
            </button>
            <button class="filter-chip" :class="{ on: filter === 'my' }" @click="applyFilter('my')">
                👋 Мои <span class="cnt">{{ counts.my }}</span>
            </button>

            <select v-model="service" class="inp" style="max-width:220px;margin-left:auto"
                    @change="router.get('/account/development', { filter, service }, { preserveScroll: true })">
                <option value="">Все услуги</option>
                <option v-for="(svc, key) in services" :key="key" :value="key">
                    {{ svc.icon }} {{ svc.label }}
                </option>
            </select>
        </div>

        <!-- список заявок -->
        <div v-if="!requests.data.length" class="vcard" style="text-align:center;padding:40px;color:var(--mut)">
            📭 Заявок пока нет
        </div>

        <div v-for="req in requests.data" :key="req.id" class="vcard dev-card">
            <div class="dev-card__head">
                <div class="dev-card__svc">
                    <span class="dev-card__ico">{{ req.service_icon }}</span>
                    <div>
                        <b>{{ req.title }}</b>
                        <small>{{ fmtDate(req.created_at) }}</small>
                    </div>
                </div>
                <VBadge v-bind="statusBadge[req.status]" />
            </div>

            <div class="dev-card__body">
                <div class="dev-card__info">
                    <div class="dev-card__row">
                        <span>Заведение:</span>
                        <b>{{ req.place?.name ?? '—' }}</b>
                    </div>
                    <div class="dev-card__row">
                        <span>Клиент:</span>
                        <b>{{ req.client?.name }}</b>
                        <small>{{ req.client?.email }}</small>
                    </div>
                    <div class="dev-card__row">
                        <span>Бюджет:</span>
                        <b class="dev-card__amount">{{ fmtMoney(req.amount) }}</b>
                    </div>
                </div>

                <div v-if="req.requirements" class="dev-card__req">
                    <p>{{ req.requirements }}</p>
                </div>
            </div>

            <div class="dev-card__act">
                <template v-if="req.status === 'new' && !req.tech_partner_id">
                    <VButton @click="claim(req)">👋 Взять в работу</VButton>
                </template>

                <template v-else-if="req.tech_partner_id">
                    <span v-if="req.tech_partner_id === $page.props.auth?.user?.id" class="dev-card__mine">
                        ✓ Ваша заявка
                    </span>

                    <template v-if="req.tech_partner_id === $page.props.auth?.user?.id">
                        <select :value="req.status" class="inp" style="max-width:180px"
                                @change="updateStatus(req, $event.target.value)">
                            <option value="claimed">Взята</option>
                            <option value="in_progress">В процессе</option>
                            <option value="completed">Завершена</option>
                            <option value="cancelled">Отменена</option>
                        </select>
                    </template>
                    <span v-else style="color:var(--mut);font-size:13px">
                        В работе: {{ req.tech_partner?.name }}
                    </span>
                </template>
            </div>
        </div>

        <!-- пагинация -->
        <div class="pager">
            <Link v-for="l in requests.links" :key="l.label" :href="l.url ?? '#'" preserve-scroll
                  :class="{ on: l.active, off: !l.url }" v-html="l.label" />
        </div>
    </div>
</template>

<style scoped>
.stats-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));
    gap:12px;
}
.stat-card{
    background:var(--panel);
    border:1px solid var(--line);
    border-radius:14px;
    padding:16px 18px;
    display:flex;flex-direction:column;gap:4px;
}
.stat-card b{font-family:var(--disp);font-size:26px;font-weight:800}
.stat-card span{color:var(--mut);font-size:12.5px}

.filter-chip{
    padding:8px 16px;border-radius:99px;
    background:var(--panel);border:1px solid var(--line);
    color:var(--txt);font-size:13px;font-weight:600;
    cursor:pointer;transition:.2s;
    display:inline-flex;align-items:center;gap:6px;
}
.filter-chip:hover{border-color:var(--cyan)}
.filter-chip.on{
    background:linear-gradient(90deg,var(--cyan),var(--violet));
    border-color:transparent;color:#fff;
}
.filter-chip .cnt{
    background:rgba(255,255,255,.2);
    padding:1px 8px;border-radius:99px;font-size:11px;
}

.dev-card{
    display:flex;flex-direction:column;gap:14px;
    padding:18px 22px;margin-bottom:12px;
}
.dev-card__head{
    display:flex;justify-content:space-between;align-items:center;gap:12px;
}
.dev-card__svc{display:flex;align-items:center;gap:12px}
.dev-card__ico{
    width:48px;height:48px;border-radius:12px;
    display:grid;place-items:center;font-size:24px;
    background:linear-gradient(135deg,rgba(34,211,238,.15),rgba(139,92,246,.15));
    border:1px solid rgba(34,211,238,.25);
    flex-shrink:0;
}
.dev-card__svc b{font-size:15px;display:block}
.dev-card__svc small{color:var(--mut);font-size:12px}

.dev-card__body{
    display:grid;grid-template-columns:1fr 1fr;gap:14px;
    padding:14px 0;border-top:1px solid var(--line);
}
.dev-card__info{display:flex;flex-direction:column;gap:8px}
.dev-card__row{
    display:flex;align-items:baseline;gap:8px;font-size:13px;
    flex-wrap:wrap;
}
.dev-card__row span{color:var(--mut);min-width:90px}
.dev-card__row b{color:var(--txt);font-weight:600}
.dev-card__row small{color:var(--mut);font-size:12px}
.dev-card__amount{
    font-family:var(--disp);font-size:18px;font-weight:800;
    color:var(--cyan);
}

.dev-card__req{
    background:var(--panel);
    border:1px solid var(--line);
    border-radius:12px;
    padding:12px 14px;
    font-size:13px;
    color:var(--mut);
    line-height:1.5;
    white-space:pre-wrap;
}
.dev-card__req p{margin:0}

.dev-card__act{
    display:flex;align-items:center;gap:12px;flex-wrap:wrap;
    padding-top:12px;border-top:1px solid var(--line);
}
.dev-card__mine{
    color:var(--lime);font-weight:700;font-size:13px;
}

@media(max-width:640px){
    .dev-card__body{grid-template-columns:1fr}
}
</style>
