<script setup>
import { ref, onMounted } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import axios from 'axios'
import VButton from '@/Components/ui/VButton.vue'
import VBadge from '@/Components/ui/VBadge.vue'
import VModal from '@/Components/ui/VModal.vue'
import { useLabels } from '@/composables/useLabels'

const props = defineProps(['posts'])
const { L, badge } = useLabels()

const places = ref([])
onMounted(async () => {
    const r = await axios.get('/api/v1/places', { params: { per_page: 100 } })
    places.value = r.data.data
})

const show = ref(false)
const editingId = ref(null)

const form = useForm({
    title: '', tag: '', excerpt: '', body: '', cover: '',
    status: 'draft', places: [],
})

const openCreate = () => { editingId.value = null; form.reset(); show.value = true }
const openEdit = (p) => {
    editingId.value = p.id
    form.title = p.title; form.tag = p.tag; form.excerpt = p.excerpt
    form.body = p.body; form.cover = p.cover; form.status = p.status
    form.places = p.places?.map((x) => x.id) ?? []
    show.value = true
}
const submit = () => {
    const opts = { onSuccess: () => (show.value = false) }
    editingId.value ? form.put(`/admin/posts/${editingId.value}`, opts) : form.post('/admin/posts', opts)
}
const destroy = (p) => confirm('Удалить подборку?') && router.delete(`/admin/posts/${p.id}`, { preserveScroll: true })
</script>

<template>
    <div>
        <div class="pg-head">
            <h1>Журнал</h1>
            <VButton style="margin-left:auto" @click="openCreate">+ Подборка</VButton>
        </div>

        <div class="vcard" style="padding:0;overflow:hidden">
            <table class="vtable">
                <thead><tr><th>Заголовок</th><th>Тег</th><th>Статус</th><th>Автор</th><th></th></tr></thead>
                <tbody>
                <tr v-for="p in posts.data" :key="p.id">
                    <td><b>{{ p.title }}</b></td>
                    <td style="color:var(--mut)">{{ p.tag ?? '—' }}</td>
                    <td><VBadge v-bind="badge(L.post ?? {}, p.status)" /></td>
                    <td style="color:var(--mut)">{{ p.author?.name }}</td>
                    <td style="text-align:right;white-space:nowrap">
                        <button class="tlink" @click="openEdit(p)">✏️</button>
                        <button class="tlink" @click="destroy(p)">🗑</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="pager">
            <component :is="'Link'" v-for="l in posts.links" :key="l.label" :href="l.url ?? '#'" preserve-scroll
                       :class="{ on: l.active, off: !l.url }" v-html="l.label" />
        </div>

        <VModal :show="show" :title="editingId ? 'Редактировать подборку' : 'Новая подборка'" @close="show = false">
            <label class="lbl">Заголовок</label><input v-model="form.title" class="inp">
            <div class="formgrid" style="margin-top:12px">
                <div><label class="lbl">Тег</label><input v-model="form.tag" class="inp" placeholder="Подборка / Кофе…"></div>
                <div>
                    <label class="lbl">Статус</label>
                    <select v-model="form.status" class="inp"><option value="draft">Черновик</option><option value="published">Опубликовать</option></select>
                </div>
            </div>
            <label class="lbl" style="margin-top:12px">Обложка (путь/URL)</label>
            <input v-model="form.cover" class="inp">
            <label class="lbl" style="margin-top:12px">Анонс</label>
            <textarea v-model="form.excerpt" class="inp" rows="2"></textarea>
            <label class="lbl" style="margin-top:12px">Текст</label>
            <textarea v-model="form.body" class="inp" rows="5"></textarea>
            <label class="lbl" style="margin-top:12px">Места в подборке</label>
            <div class="chkgrid" style="max-height:180px;overflow-y:auto">
                <label v-for="p in places" :key="p.id" class="chk">
                    <input v-model="form.places" type="checkbox" :value="p.id"><i>✓</i><span>{{ p.name }}</span>
                </label>
            </div>
            <div style="display:flex;gap:10px;margin-top:16px">
                <VButton :busy="form.processing" @click="submit">Сохранить</VButton>
            </div>
        </VModal>
    </div>
</template>
