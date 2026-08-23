<script setup>
import { ref, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import VButton from '@/Components/ui/VButton.vue'
import VBadge from '@/Components/ui/VBadge.vue'
import { useLabels } from '@/composables/useLabels'
import { useDictionaryStore } from '@/stores/dictionary'
import { useAuth } from '@/composables/useAuth'
import AdminPlaceMenu from '@/Components/AdminPlaceMenu.vue'  // ← ДОБАВИТЬ

const props = defineProps(['places'])
const { L, badge } = useLabels()
const dict = useDictionaryStore()
const auth = useAuth()

const f = ref({ q: '', status: '', district_id: '' })
onMounted(() => dict.load())

const apply = () => router.get('/admin/places', { ...f.value }, { preserveScroll: true })

const toggleFeatured = (p) =>
    router.post(`/admin/places/${p.id}/featured`, {}, { preserveScroll: true })

const destroy = (p) => {
    if (confirm(`Скрыть «${p.name}» с сайта?`)) {
        router.delete(`/admin/places/${p.id}`, { preserveScroll: true })
    }
}
</script>

<template>
    <div>
        <div class="pg-head">
            <h1>Заведения</h1>
            <div style="margin-left:auto;display:flex;gap:10px">
                <!-- твой поиск/фильтры, если есть -->
                <Link href="/admin/places/create">
                    <VButton>+ Заведение</VButton>
                </Link>
            </div>
        </div>



        <div class="vcard" style="margin-bottom:18px;display:flex;gap:12px;flex-wrap:wrap">
            <input v-model="f.q" class="inp" style="max-width:260px" placeholder="Поиск по названию…" @keyup.enter="apply">
            <select v-model="f.status" class="inp" style="max-width:200px" @change="apply">
                <option value="">Все статусы</option>
                <option v-for="(m, k) in L.moderation" :key="k" :value="k">{{ m.label }}</option>
            </select>
            <select v-model="f.district_id" class="inp" style="max-width:200px" @change="apply">
                <option value="">Все районы</option>
                <option v-for="d in dict.districts ?? []" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>
            <VButton variant="ghost" @click="apply">Применить</VButton>
        </div>

        <div class="vcard" style="padding:0;overflow:hidden">
            <table class="vtable">
                <thead><tr><th>Название</th><th>Категория</th><th>Район</th><th>★</th><th>Статус</th><th></th></tr></thead>
                <tbody>
                <tr v-for="p in places.data" :key="p.id">
                    <td><b>{{ p.name }}</b>{{ p.is_featured ? ' ✨' : '' }}</td>
                    <td>{{ p.category?.icon }} {{ p.category?.name }}</td>
                    <td style="color:var(--mut)">{{ p.district?.name }}</td>
                    <td style="color:var(--lime)">{{ p.rating }}</td>
                    <td><VBadge v-bind="badge(L.moderation, p.status)" /></td>
                    <td style="text-align:right;white-space:nowrap">
                        <AdminPlaceMenu :place="p" />
                        <Link class="tlink" :href="`/admin/places/${p.id}/analytics`">📈</Link>
                        <Link class="tlink" :href="`/admin/places/${p.id}/edit`">✏️</Link>
                        <button v-if="auth.can('manage ads')" class="tlink" @click="toggleFeatured(p)">✨</button>
                        <button class="tlink tlink--del" @click="destroy(p)"></button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="pager">
            <Link v-for="l in places.links" :key="l.label" :href="l.url ?? '#'" preserve-scroll
                  :class="{ on: l.active, off: !l.url }" v-html="l.label" />
        </div>
    </div>
</template>

<style scoped>
.tlink{margin-left:8px;opacity:.75;transition:.2s;background:none;border:none;cursor:pointer;font-size:14px}
.tlink:hover{opacity:1}
.tlink--del:hover{filter:hue-rotate(90deg)}
</style>


