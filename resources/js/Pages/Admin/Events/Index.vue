<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import VButton from '@/Components/ui/VButton.vue'
import VBadge from '@/Components/ui/VBadge.vue'
import { useLabels } from '@/composables/useLabels'

const props = defineProps(['events', 'types'])
const { L, badge } = useLabels()

const f = ref({ q: '', status: '', type: '' })
const apply = () => router.get('/admin/events', { ...f.value }, { preserveScroll: true })

const destroy = (e) => {
    if (confirm(`Удалить событие «${e.title}»?`)) {
        router.delete(`/admin/events/${e.id}`, { preserveScroll: true })
    }
}

const fmtDate = (d) => new Date(d).toLocaleString('ru-RU', {
    day: '2-digit', month: 'long', hour: '2-digit', minute: '2-digit'
})

/* type приходит как объект {value, label} после сериализации enum */
const typeLabel = (t) => t?.label ?? t?.name ?? t
</script>

<template>
    <div>
        <div class="pg-head">
            <h1>Афиша</h1>
            <Link href="/admin/events/create">
                <VButton style="margin-left:auto">+ Событие</VButton>
            </Link>
        </div>

        <div class="vcard" style="margin-bottom:18px;display:flex;gap:12px;flex-wrap:wrap">
            <input v-model="f.q" class="inp" style="max-width:260px" placeholder="Поиск по названию…" @keyup.enter="apply">
            <select v-model="f.status" class="inp" style="max-width:180px" @change="apply">
                <option value="">Все статусы</option>
                <option v-for="(m, k) in L.moderation" :key="k" :value="k">{{ m.label }}</option>
            </select>
            <select v-model="f.type" class="inp" style="max-width:180px" @change="apply">
                <option value="">Все типы</option>
                <option v-for="t in types" :key="t.value" :value="t.value">{{ t.label }}</option>
            </select>
            <VButton variant="ghost" @click="apply">Применить</VButton>
        </div>

        <div class="vcard" style="padding:0;overflow:hidden">
            <table class="vtable">
                <thead>
                <tr>
                    <th>Название</th>
                    <th>Тип</th>
                    <th>Дата</th>
                    <th>Место</th>
                    <th>Цена</th>
                    <th>Статус</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="e in events.data" :key="e.id">
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <img v-if="e.image" :src="`/storage/${e.image}`" style="width:48px;height:36px;object-fit:cover;border-radius:8px">
                            <b>{{ e.title }}</b>
                        </div>
                    </td>
                    <td>{{ typeLabel(e.type) }}</td>
                    <td style="color:var(--mut)">{{ fmtDate(e.starts_at) }}</td>
                    <td style="color:var(--mut)">{{ e.place?.name ?? '—' }}</td>
                    <td style="color:var(--mut)">{{ e.price ?? '—' }}</td>
                    <td><VBadge v-bind="badge(L.moderation, e.status)" /></td>
                    <td style="text-align:right;white-space:nowrap">
                        <Link class="tlink" :href="`/admin/events/${e.id}/edit`">✏️</Link>
                        <button class="tlink tlink--del" @click="destroy(e)">🗑</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="pager">
            <Link v-for="l in events.links" :key="l.label" :href="l.url ?? '#'" preserve-scroll
                  :class="{ on: l.active, off: !l.url }" v-html="l.label" />
        </div>
    </div>
</template>

<style scoped>
.tlink{margin-left:8px;opacity:.75;transition:.2s;background:none;border:none;cursor:pointer;font-size:14px}
.tlink:hover{opacity:1}
.tlink--del:hover{filter:hue-rotate(90deg)}
</style>
