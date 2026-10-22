<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps(['topDomains', 'byContext', 'daily', 'recent', 'totals', 'days'])

const days = ref(props.days)
const changeDays = (d) => {
    days.value = d
    router.get('/admin/outbound', { days: d }, { preserveScroll: true })
}

const maxClicks = Math.max(...props.topDomains.map(d => d.clicks), 1)
const maxDaily = Math.max(...props.daily.map(d => d.clicks), 1)

const contextLabel = {
    place: '🏢 Заведения',
    news: '📰 Новости',
    event: '🎭 События',
    story: '🎞 Истории',
    category: '📂 Категории',
    referral: '🤝 Рефералы',
}

const fmt = n => new Intl.NumberFormat('ru-RU').format(n)
</script>

<template>
    <div>
        <div class="pg-head">
            <h1>🔗 Внешние переходы</h1>
            <div style="margin-left:auto;display:flex;gap:8px">
                <button v-for="d in [7, 14, 30, 90]" :key="d"
                        class="chip" :class="{ on: days === d }"
                        @click="changeDays(d)">
                    {{ d }} дн
                </button>
            </div>
        </div>

        <!-- Сводка -->
        <div class="grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px">
            <div class="vcard" style="text-align:center">
                <b style="color:var(--cyan);font-size:28px;font-family:var(--disp)">{{ fmt(totals.all) }}</b>
                <span style="color:var(--mut);font-size:13px">всего кликов</span>
            </div>
            <div class="vcard" style="text-align:center">
                <b style="color:var(--lime);font-size:28px;font-family:var(--disp)">{{ fmt(totals.unique) }}</b>
                <span style="color:var(--mut);font-size:13px">уникальных IP</span>
            </div>
            <div class="vcard" style="text-align:center">
                <b style="color:var(--violet);font-size:28px;font-family:var(--disp)">{{ fmt(totals.domains) }}</b>
                <span style="color:var(--mut);font-size:13px">внешних доменов</span>
            </div>
        </div>

        <div class="grid" style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:20px">
            <!-- Топ доменов -->
            <div class="vcard">
                <h3 style="margin-top:0">Топ доменов</h3>
                <div v-for="d in topDomains" :key="d.domain" class="domain-row">
                    <div class="domain-row__info">
                        <b>{{ d.domain }}</b>
                        <div class="domain-row__bar">
                            <div class="domain-row__fill" :style="{ width: (d.clicks / maxClicks * 100) + '%' }"></div>
                        </div>
                    </div>
                    <span class="domain-row__count">{{ fmt(d.clicks) }}</span>
                </div>
            </div>

            <!-- По контекстам -->
            <div class="vcard">
                <h3 style="margin-top:0">Источники кликов</h3>
                <div v-for="c in byContext" :key="c.context" class="ctx-row">
                    <span>{{ contextLabel[c.context] || c.context }}</span>
                    <b>{{ fmt(c.clicks) }}</b>
                </div>
                <p v-if="!byContext.length" style="color:var(--mut);text-align:center;padding:20px">Нет данных</p>
            </div>
        </div>

        <!-- Дневная динамика -->
        <div class="vcard" style="margin-bottom:20px">
            <h3 style="margin-top:0">Динамика по дням</h3>
            <div class="daily-chart">
                <div v-for="d in daily" :key="d.date" class="daily-bar"
                     :style="{ height: (d.clicks / maxDaily * 100) + '%' }"
                     :title="`${d.date}: ${fmt(d.clicks)} кликов`">
                </div>
            </div>
        </div>

        <!-- Последние клики -->
        <div class="vcard" style="padding:0;overflow:hidden">
            <div style="padding:16px 20px;border-bottom:1px solid var(--line);font-weight:700">
                Последние переходы
            </div>
            <table class="vtable">
                <thead>
                <tr>
                    <th>Домен</th>
                    <th>Контекст</th>
                    <th>Заведение</th>
                    <th>Страница</th>
                    <th>IP</th>
                    <th>Время</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="r in recent" :key="r.id">
                    <td>
                        <a :href="r.url" target="_blank" rel="noopener" style="color:var(--cyan);font-weight:600">
                            {{ r.domain }}
                        </a>
                    </td>
                    <td>
                            <span v-if="r.context" style="font-size:12px;padding:3px 8px;background:var(--panel2);border-radius:6px">
                                {{ contextLabel[r.context] || r.context }}
                            </span>
                        <span v-else style="color:var(--mut)">—</span>
                    </td>
                    <td style="font-size:13px">{{ r.place_name || '—' }}</td>
                    <td style="font-size:12px;color:var(--mut);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        {{ r.page_url || '—' }}
                    </td>
                    <td style="font-size:12px;color:var(--mut);font-family:monospace">{{ r.ip }}</td>
                    <td style="font-size:12px;color:var(--mut);white-space:nowrap">{{ r.created_at }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>
.domain-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid var(--line);
}
.domain-row:last-child { border-bottom: none; }
.domain-row__info { flex: 1; }
.domain-row__info b { font-size: 14px; display: block; margin-bottom: 4px; }
.domain-row__bar {
    height: 6px;
    background: var(--panel2);
    border-radius: 99px;
    overflow: hidden;
}
.domain-row__fill {
    height: 100%;
    background: linear-gradient(90deg, var(--cyan), var(--violet));
    border-radius: 99px;
    transition: width .5s;
}
.domain-row__count {
    font-family: var(--disp);
    font-weight: 700;
    color: var(--cyan);
    font-size: 14px;
    min-width: 50px;
    text-align: right;
}

.ctx-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid var(--line);
    font-size: 14px;
}
.ctx-row:last-child { border-bottom: none; }
.ctx-row b { color: var(--cyan); font-family: var(--disp); }

.daily-chart {
    display: flex;
    align-items: flex-end;
    gap: 4px;
    height: 140px;
    padding: 10px 0;
}
.daily-bar {
    flex: 1;
    min-width: 8px;
    background: linear-gradient(180deg, var(--cyan), var(--violet));
    border-radius: 4px 4px 0 0;
    transition: height .5s;
    cursor: pointer;
}
.daily-bar:hover {
    opacity: .8;
}
</style>
