<script setup>
import { ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import VButton from '@/Components/ui/VButton.vue'
import VBadge from '@/Components/ui/VBadge.vue'
import { useFlashStore } from '@/stores/flash' // <-- 1. Добавь этот импорт сюда

const props = defineProps(['invites', 'counts', 'phone'])
const page = usePage()
const flash = useFlashStore() // <-- 2. Инициализируй стейт здесь



const f = ref({ q: '', role: '', status: '' })
const apply = () => router.get('/account/referrals', { ...f.value }, { preserveScroll: true })

const roleBadge = {
    client:       { label: '🏢 Клиент',       color: 'cyan' },
    tech_partner: { label: '💻 Тех. партнёр', color: 'violet' },
    ad_partner:   { label: '📣 Рекламный',    color: 'magenta' },
}

const statusBadge = {
    pending:   { label: '⏳ Ожидает', color: 'yellow' },
    active:    { label: '✓ Активен',  color: 'lime' },
    completed: { label: '✓ Завершён', color: 'cyan' },
}

const fmtDate = (d) => new Date(d).toLocaleString('ru-RU', {
    day: '2-digit', month: '2-digit', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
})

const fmtPhone = (p) => {
    if (!p) return '—'
    const d = p.replace(/\D/g, '')
    if (d.length === 11) return `+${d[0]} (${d.slice(1,4)}) ${d.slice(4,7)}-${d.slice(7,9)}-${d.slice(9)}`
    return p
}

const refLink = () => {
    if (!props.phone) return ''
    const host = window.location.origin
    return `${host}/?ref=+${props.phone}`
}

const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(refLink())
        router.visit('/account/referrals', {
            preserveScroll: true,
            preserveState: true,
            onSuccess:async () => {
                // используем flash через store
                const { useFlashStore } = await import('@/stores/flash')
                useFlashStore().add({ type: 'success', message: 'Ссылка скопирована!' })
            },
        })
    } catch {
        // фолбэк
        const input = document.createElement('input')
        input.value = refLink()
        document.body.appendChild(input)
        input.select()
        document.execCommand('copy')
        document.body.removeChild(input)
    }
}
</script>

<template>
    <div>
        <div class="pg-head">
            <h1>🤝 Мои рефералы</h1>
        </div>

        <!-- сводка -->
        <div class="r-stats">
            <div class="r-stat">
                <b style="color:var(--cyan)">{{ counts.total }}</b>
                <span>всего приглашений</span>
            </div>
            <div class="r-stat">
                <b style="color:var(--yellow)">{{ counts.pending }}</b>
                <span>ожидают регистрации</span>
            </div>
            <div class="r-stat">
                <b style="color:var(--lime)">{{ counts.active }}</b>
                <span>активных</span>
            </div>
            <div class="r-stat">
                <b style="color:var(--magenta,#f050e0)">{{ counts.week }}</b>
                <span>за 7 дней</span>
            </div>
        </div>

        <!-- реферальная ссылка -->
        <div v-if="phone" class="r-link-card">
            <div class="r-link-card__head">
                <span class="r-link-card__ico">🔗</span>
                <div>
                    <b>Ваша реферальная ссылка</b>
                    <small>Делитесь ею — за каждого приведённого клиента/партнёра вы получите бонус</small>
                </div>
            </div>
            <div class="r-link-card__row">
                <input type="text" :value="refLink()" readonly class="inp r-link-card__field">
                <VButton @click="copyLink">📋 Копировать</VButton>
            </div>
        </div>

        <!-- фильтры -->
        <div class="vcard" style="margin:18px 0;display:flex;gap:12px;flex-wrap:wrap">
            <input v-model="f.q" class="inp" style="max-width:260px" placeholder="Имя, email, заведение…" @keyup.enter="apply">
            <select v-model="f.role" class="inp" style="max-width:180px" @change="apply">
                <option value="">Все роли</option>
                <option value="client">Клиент</option>
                <option value="tech_partner">Тех. партнёр</option>
                <option value="ad_partner">Рекламный партнёр</option>
            </select>
            <select v-model="f.status" class="inp" style="max-width:180px" @change="apply">
                <option value="">Все статусы</option>
                <option value="pending">Ожидает</option>
                <option value="active">Активен</option>
                <option value="completed">Завершён</option>
            </select>
            <VButton variant="ghost" @click="apply">Применить</VButton>
        </div>

        <!-- таблица -->
        <div class="vcard" style="padding:0;overflow:hidden">
            <table class="vtable">
                <thead>
                <tr>
                    <th>Реферал</th>
                    <th>Роль</th>
                    <th>Заведение</th>
                    <th>Статус</th>
                    <th>Дата</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="inv in invites.data" :key="inv.id">
                    <td>
                        <div v-if="inv.referred" class="r-user">
                            <div class="r-avatar">{{ (inv.referred.name || '?')[0].toUpperCase() }}</div>
                            <div>
                                <b>{{ inv.referred.name }}</b>
                                <small>{{ inv.referred.email }}</small>
                                <small v-if="inv.referred.phone" class="r-phone">{{ fmtPhone(inv.referred.phone) }}</small>
                            </div>
                        </div>
                        <div v-else class="r-user r-user--pending">
                            <div class="r-avatar r-avatar--ghost">?</div>
                            <div>
                                <b>Ещё не зарегистрирован</b>
                                <small class="r-phone">Перешёл по ссылке</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <VBadge v-bind="roleBadge[inv.role] ?? { label: inv.role, color: 'cyan' }" />
                    </td>
                    <td>
                        <Link v-if="inv.place" :href="`/place/${inv.place.slug}`" class="r-place-link">
                            🏢 {{ inv.place.name }}
                        </Link>
                        <span v-else style="color:var(--mut)">—</span>
                    </td>
                    <td>
                        <VBadge v-bind="statusBadge[inv.status] ?? { label: inv.status, color: 'cyan' }" />
                    </td>
                    <td style="color:var(--mut);font-size:12px;white-space:nowrap">{{ fmtDate(inv.created_at) }}</td>
                </tr>
                <tr v-if="!invites.data?.length">
                    <td colspan="5" style="text-align:center;padding:40px;color:var(--mut)">
                        <div style="font-size:36px;margin-bottom:10px">🤝</div>
                        <p>Рефералов пока нет</p>
                        <p style="font-size:12px;margin-top:6px">Поделитесь своей реферальной ссылкой, чтобы начать</p>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- пагинация -->
        <div v-if="invites.links?.length > 3" class="pager">
            <Link v-for="l in invites.links" :key="l.label" :href="l.url ?? '#'" preserve-scroll
                  :class="{ on: l.active, off: !l.url }" v-html="l.label" />
        </div>
    </div>
</template>

<style scoped>
.r-stats{
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));
    gap:12px;
}
.r-stat{
    background:var(--panel);
    border:1px solid var(--line);
    border-radius:14px;
    padding:16px 18px;
    display:flex;flex-direction:column;gap:4px;
}
.r-stat b{font-family:var(--disp);font-size:26px;font-weight:800}
.r-stat span{color:var(--mut);font-size:12.5px}

/* карточка с ссылкой */
.r-link-card{
    margin-top:18px;
    padding:20px 22px;
    background:linear-gradient(135deg, rgba(34,211,238,.08), rgba(139,92,246,.08));
    border:1px solid rgba(34,211,238,.25);
    border-radius:16px;
}
.r-link-card__head{
    display:flex;align-items:center;gap:14px;margin-bottom:14px;
}
.r-link-card__ico{
    width:44px;height:44px;border-radius:12px;
    display:grid;place-items:center;font-size:22px;
    background:linear-gradient(135deg,rgba(34,211,238,.2),rgba(139,92,246,.2));
    border:1px solid rgba(34,211,238,.3);
    flex-shrink:0;
}
.r-link-card__head b{display:block;font-size:15px;margin-bottom:2px}
.r-link-card__head small{color:var(--mut);font-size:12.5px}
.r-link-card__row{
    display:flex;gap:10px;align-items:center;
}
.r-link-card__field{
    flex:1;font-family:monospace;font-size:13px;
    background:rgba(0,0,0,.2);
    color:var(--cyan);
}

/* пользователь */
.r-user{display:flex;align-items:center;gap:10px}
.r-avatar{
    width:36px;height:36px;border-radius:50%;flex-shrink:0;
    display:grid;place-items:center;
    background:linear-gradient(135deg,var(--cyan),var(--violet));
    color:#fff;font-weight:800;font-size:14px;
}
.r-avatar--ghost{
    background:var(--panel);
    border:1px dashed var(--line);
    color:var(--mut);
}
.r-user--pending{opacity:.7}
.r-user b{display:block;font-size:13px}
.r-user small{display:block;color:var(--mut);font-size:11px;line-height:1.4}
.r-phone{color:var(--cyan);font-weight:600}

.r-place-link{
    color:var(--cyan);font-size:13px;font-weight:600;
    text-decoration:none;transition:.2s;
}
.r-place-link:hover{text-decoration:underline}

@media(max-width:640px){
    .r-link-card__row{flex-direction:column;align-items:stretch}
    .r-link-card__field{width:100%}
}
</style>
