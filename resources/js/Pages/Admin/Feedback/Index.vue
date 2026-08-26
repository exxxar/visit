<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import VButton from '@/Components/ui/VButton.vue'
import VBadge from '@/Components/ui/VBadge.vue'

const props = defineProps(['feedbacks', 'subjects', 'counts'])

const f = ref({ q: '', status: '', subject: '' })

const apply = () => router.get('/admin/feedback', { ...f.value }, { preserveScroll: true })

const setStatus = (fb, status) => {
    router.put(`/admin/feedback/${fb.id}`, { status, admin_note: fb.admin_note }, { preserveScroll: true })
}

const destroy = (fb) => {
    if (confirm('Удалить обращение?')) {
        router.delete(`/admin/feedback/${fb.id}`, { preserveScroll: true })
    }
}

const subjectBadge = {
    general:     { label: 'Вопрос',    color: 'cyan' },
    partnership: { label: 'Партнёрство', color: 'violet' },
    ads:         { label: 'Реклама',   color: 'yellow' },
    bug:         { label: 'Баг',       color: 'rose' },
    suggestion:  { label: 'Идея',      color: 'lime' },
    complaint:   { label: 'Жалоба',    color: 'orange' },
}

const statusBadge = {
    new:         { label: 'Новое',     color: 'cyan' },
    in_progress: { label: 'В работе',  color: 'yellow' },
    resolved:    { label: 'Закрыто',   color: 'lime' },
}

const formatDate = (d) => new Date(d).toLocaleString('ru-RU', {
    day: '2-digit', month: '2-digit', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
})
</script>

<template>
    <div>
        <div class="pg-head">
            <h1>Обратная связь</h1>
            <div style="display:flex;gap:10px;margin-left:auto">
                <span class="fb-stat fb-stat--new">● {{ counts.new }} новых</span>
                <span class="fb-stat fb-stat--progress">● {{ counts.in_progress }} в работе</span>
                <span class="fb-stat fb-stat--resolved">● {{ counts.resolved }} закрыто</span>
            </div>
        </div>

        <div class="vcard" style="margin-bottom:18px;display:flex;gap:12px;flex-wrap:wrap">
            <input v-model="f.q" class="inp" style="max-width:260px" placeholder="Поиск по имени, контакту, тексту…" @keyup.enter="apply">
            <select v-model="f.status" class="inp" style="max-width:180px" @change="apply">
                <option value="">Все статусы</option>
                <option value="new">Новое</option>
                <option value="in_progress">В работе</option>
                <option value="resolved">Закрыто</option>
            </select>
            <select v-model="f.subject" class="inp" style="max-width:200px" @change="apply">
                <option value="">Все темы</option>
                <option v-for="(label, key) in subjects" :key="key" :value="key">{{ label }}</option>
            </select>
            <VButton variant="ghost" @click="apply">Применить</VButton>
        </div>

        <div v-if="!feedbacks.data.length" class="vcard" style="text-align:center;color:var(--mut);padding:40px">
            📭 Нет обращений
        </div>

        <div v-for="fb in feedbacks.data" :key="fb.id" class="fb-card">
            <div class="fb-card__head">
                <div class="fb-card__who">
                    <b>{{ fb.name }}</b>
                    <a v-if="fb.contact.includes('@')" :href="`mailto:${fb.contact}`" class="tlink">{{ fb.contact }}</a>
                    <a v-else :href="`tel:${fb.contact.replace(/[^+\d]/g, '')}`" class="tlink">{{ fb.contact }}</a>
                </div>
                <div class="fb-card__meta">
                    <span class="fb-badge" :class="'fb-badge--' + subjectBadge[fb.subject]?.color">
                        {{ subjectBadge[fb.subject]?.label ?? fb.subject }}
                    </span>
                    <span class="fb-badge" :class="'fb-badge--' + statusBadge[fb.status]?.color">
                        {{ statusBadge[fb.status]?.label }}
                    </span>
                    <span class="fb-date">{{ formatDate(fb.created_at) }}</span>
                </div>
            </div>

            <p class="fb-card__msg">{{ fb.message }}</p>

            <div v-if="fb.admin_note" class="fb-card__note">
                <b>📝 Заметка:</b> {{ fb.admin_note }}
            </div>

            <div class="fb-card__act">
                <select :value="fb.status" class="inp inp--sm" @change="setStatus(fb, $event.target.value)">
                    <option value="new">Новое</option>
                    <option value="in_progress">В работе</option>
                    <option value="resolved">Закрыто</option>
                </select>
                <button class="tlink tlink--del" @click="destroy(fb)">🗑</button>
            </div>
        </div>

        <div class="pager">
            <Link v-for="l in feedbacks.links" :key="l.label" :href="l.url ?? '#'" preserve-scroll
                  :class="{ on: l.active, off: !l.url }" v-html="l.label" />
        </div>
    </div>
</template>

<style scoped>
.fb-stat{font-size:13px;color:var(--mut);font-weight:600}
.fb-stat--new{color:var(--cyan)}
.fb-stat--progress{color:var(--yellow,#facc15)}
.fb-stat--resolved{color:var(--lime,#a3e635)}

.fb-card{
    background:var(--panel);border:1px solid var(--line);
    border-radius:16px;padding:18px 22px;margin-bottom:14px;
    transition:border-color .2s;
}
.fb-card:hover{border-color:var(--cyan)}

.fb-card__head{
    display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;
    align-items:flex-start;margin-bottom:12px;
}
.fb-card__who{display:flex;flex-direction:column;gap:4px}
.fb-card__who b{font-size:15px}
.fb-card__meta{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.fb-date{font-size:12px;color:var(--mut)}

.fb-badge{
    font-size:11px;font-weight:700;padding:3px 10px;border-radius:99px;
    text-transform:uppercase;letter-spacing:.05em;
}
.fb-badge--cyan   {background:rgba(34,211,238,.15);color:#22d3ee;border:1px solid rgba(34,211,238,.3)}
.fb-badge--violet {background:rgba(139,92,246,.15);color:#a78bfa;border:1px solid rgba(139,92,246,.3)}
.fb-badge--yellow {background:rgba(250,204,21,.15);color:#facc15;border:1px solid rgba(250,204,21,.3)}
.fb-badge--rose   {background:rgba(244,63,94,.15);color:#fb7185;border:1px solid rgba(244,63,94,.3)}
.fb-badge--lime   {background:rgba(163,230,53,.15);color:#a3e635;border:1px solid rgba(163,230,53,.3)}
.fb-badge--orange {background:rgba(255,138,60,.15);color:#ff8a3c;border:1px solid rgba(255,138,60,.3)}

.fb-card__msg{
    color:var(--txt);font-size:14px;line-height:1.6;
    margin:0 0 12px;white-space:pre-wrap;word-break:break-word;
}

.fb-card__note{
    background:rgba(139,92,246,.08);border-left:3px solid var(--violet,#8b5cf6);
    padding:10px 14px;border-radius:8px;font-size:13px;color:var(--mut);
    margin-bottom:12px;
}

.fb-card__act{display:flex;gap:10px;align-items:center}
.inp--sm{padding:6px 10px;font-size:13px;max-width:160px}
.tlink--del{margin-left:auto}
</style>
