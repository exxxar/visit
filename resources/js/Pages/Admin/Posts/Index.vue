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
    title: '', tag: '', excerpt: '', body: '',
    cover: null,          // ← файл вместо строки
    status: 'draft',
    places: [],
})

/* превью обложки */
const coverPreview = ref(null)
const currentCover = ref(null) // путь уже сохранённой картинки при редактировании

const onCoverSelect = (e) => {
    const file = e.target.files[0] ?? null
    form.cover = file
    if (file) {
        if (coverPreview.value) URL.revokeObjectURL(coverPreview.value)
        coverPreview.value = URL.createObjectURL(file)
    }
}

const clearCover = () => {
    form.cover = null
    if (coverPreview.value) URL.revokeObjectURL(coverPreview.value)
    coverPreview.value = null
    currentCover.value = null
}

const openCreate = () => {
    editingId.value = null
    form.reset()
    coverPreview.value = null
    currentCover.value = null
    show.value = true
}

const openEdit = (p) => {
    editingId.value = p.id
    form.title = p.title
    form.tag = p.tag
    form.excerpt = p.excerpt
    form.body = p.body
    form.cover = null               // новый файл не выбран
    form.status = p.status
    form.places = p.places?.map((x) => x.id) ?? []
    currentCover.value = p.cover || null   // текущая картинка для превью
    coverPreview.value = null
    show.value = true
}

const submit = () => {
    const opts = {
        forceFormData: true,        // ← обязательно для файлов
        preserveScroll: true,
        onSuccess: () => {
            show.value = false
            coverPreview.value = null
            currentCover.value = null
        },
    }
    editingId.value
        ? form.put(`/admin/posts/${editingId.value}`, opts)
        : form.post('/admin/posts', opts)
}

const destroy = (p) => confirm('Удалить подборку?') && router.delete(`/admin/posts/${p.id}`, { preserveScroll: true })

/* абсолютный путь к картинке */
const coverSrc = (path) => {
    if (!path) return null
    if (path.startsWith('http')) return path
    return '/' + path.replace(/^\//, '')
}
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
            <!-- Обложка -->
            <label class="lbl" style="margin-top:12px">Обложка</label>

            <!-- превью -->
            <div v-if="coverPreview || currentCover" class="cover-preview">
                <img v-lazy="coverPreview ?? coverSrc(currentCover)" alt="Обложка">
                <button type="button" class="cover-preview__remove" title="Убрать" @click="clearCover">✕</button>
            </div>

            <!-- выбор файла -->
            <input
                type="file"
                accept="image/*"
                class="inp"
                style="margin-top:8px"
                @change="onCoverSelect"
            >
            <p class="hint">JPG, PNG, WEBP до 5 МБ. Рекомендуемый размер 1200×630.</p>
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
<style scoped>
.hint{color:var(--mut);font-size:12px;margin-top:6px}

.cover-preview{
    position:relative;
    margin-top:8px;
    border-radius:12px;
    overflow:hidden;
    border:1px solid var(--line);
    background:var(--panel);
}
.cover-preview img{
    width:100%;
    max-height:220px;
    object-fit:cover;
    display:block;
}
.cover-preview__remove{
    position:absolute;
    top:8px;
    right:8px;
    width:30px;
    height:30px;
    border-radius:50%;
    border:none;
    background:rgba(0,0,0,.6);
    backdrop-filter:blur(6px);
    color:#fff;
    font-size:14px;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    transition:.2s;
}
.cover-preview__remove:hover{
    background:var(--rose,#fb7185);
    transform:scale(1.08);
}
</style>
