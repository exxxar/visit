<script setup>
import {ref} from 'vue'
import {Link, router} from '@inertiajs/vue3'
import VButton from '@/Components/ui/VButton.vue'
import VBadge from '@/Components/ui/VBadge.vue'
import {useLabels} from '@/composables/useLabels'

const props = defineProps(['news'])
const {L, badge} = useLabels()

const f = ref({q: '', status: ''})
const apply = () => router.get('/admin/news', {...f.value}, {preserveScroll: true})

const destroy = (n) => {
    if (confirm(`Удалить новость «${n.title}»?`)) {
        router.delete(`/admin/news/${n.id}`, {preserveScroll: true})
    }
}

const fmtDate = (d) => new Date(d).toLocaleDateString('ru-RU', {
    day: '2-digit', month: '2-digit', year: 'numeric'
})
</script>

<template>
    <div>
        <div class="pg-head">
            <h1>Новости</h1>
            <Link href="/admin/news/create">
                <VButton style="margin-left:auto">+ Новость</VButton>
            </Link>
        </div>

        <div class="vcard" style="margin-bottom:18px;display:flex;gap:12px;flex-wrap:wrap">
            <input v-model="f.q" class="inp" style="max-width:260px" placeholder="Поиск…" @keyup.enter="apply">
            <select v-model="f.status" class="inp" style="max-width:180px" @change="apply">
                <option value="">Все статусы</option>
                <option v-for="(m, k) in L.moderation" :key="k" :value="k">{{ m.label }}</option>
            </select>
            <VButton variant="ghost" @click="apply">Применить</VButton>
        </div>

        <div class="vcard" style="padding:0;overflow:hidden">
            <table class="vtable">
                <thead>
                <tr>
                    <th>Заголовок</th>
                    <th>Заведение</th>
                    <th>Создана</th>
                    <th>Опубликована</th>
                    <th>Статус</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="n in news.data" :key="n.id">
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <img v-if="n.image" :src="`/storage/${n.image}`"
                                 style="width:48px;height:36px;object-fit:cover;border-radius:8px">
                            <b>{{ n.title }}</b>
                        </div>
                    </td>
                    <td style="color:var(--mut)">{{ n.place?.name ?? '—' }}</td>
                    <td style="color:var(--mut)">{{ fmtDate(n.created_at) }}</td>
                    <td style="color:var(--mut)">{{ n.published_at ? fmtDate(n.published_at) : '—' }}</td>

                    <td>
                        <VBadge v-bind="badge(L.moderation, n.status)"/>
                    </td>
                    <td style="text-align:right;white-space:nowrap">
                        <Link class="tlink" :href="`/admin/news/${n.id}/edit`">✏️</Link>
                        <button class="tlink tlink--del" @click="destroy(n)">🗑</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="pager">
            <Link v-for="l in news.links" :key="l.label" :href="l.url ?? '#'" preserve-scroll
                  :class="{ on: l.active, off: !l.url }" v-html="l.label"/>
        </div>
    </div>
</template>

<style scoped>
.tlink {
    margin-left: 8px;
    opacity: .75;
    transition: .2s;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 14px
}

.tlink:hover {
    opacity: 1
}

.tlink--del:hover {
    filter: hue-rotate(90deg)
}
</style>
