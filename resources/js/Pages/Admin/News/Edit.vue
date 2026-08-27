<script setup>
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import VButton from '@/Components/ui/VButton.vue'
import { useLabels } from '@/composables/useLabels'

const props = defineProps(['news', 'places'])
const { L } = useLabels()

const isEdit = !!props.news

const form = useForm({
    title: props.news?.title ?? '',
    body: props.news?.body ?? '',
    place_id: props.news?.place_id ?? '',
    image: null,
    status: props.news?.status ?? 'approved',
})

const preview = ref(props.news?.image ? `/storage/${props.news.image}` : null)

const onImage = (e) => {
    form.image = e.target.files[0] ?? null
    if (form.image) {
        if (preview.value && !preview.value.startsWith('/storage/')) URL.revokeObjectURL(preview.value)
        preview.value = URL.createObjectURL(form.image)
    }
}

const submit = () => {
    const url = isEdit ? `/admin/news/${props.news.id}` : '/admin/news'
    form.transform((data) => ({ ...data, _method: isEdit ? 'PUT' : 'POST' }))
        .post(url, { forceFormData: true, preserveScroll: true })
}
</script>

<template>
    <div>
        <div class="pg-head">
            <Link href="/admin/news" class="tlink">← Назад</Link>
            <h1>{{ isEdit ? '✏️ ' + news.title : '➕ Новая новость' }}</h1>
            <VButton style="margin-left:auto" :busy="form.processing" @click="submit">Сохранить</VButton>
        </div>

        <div class="grid" style="grid-template-columns:2fr 1fr;align-items:start;gap:16px">
            <div class="vcard">
                <h3 style="margin-top:0">Основное</h3>

                <label class="lbl">Заголовок *</label>
                <input v-model="form.title" class="inp" placeholder="Открылась новая кофейня…">
                <p v-if="form.errors.title" class="err">{{ form.errors.title }}</p>

                <label class="lbl" style="margin-top:12px">Заведение</label>
                <select v-model="form.place_id" class="inp">
                    <option value="">Не привязано</option>
                    <option v-for="p in places" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>

                <label class="lbl" style="margin-top:12px">Текст новости *</label>
                <textarea v-model="form.body" class="inp" rows="12" placeholder="Полный текст новости…"></textarea>
                <p v-if="form.errors.body" class="err">{{ form.errors.body }}</p>
            </div>

            <div style="display:flex;flex-direction:column;gap:16px">
                <div class="vcard">
                    <h3 style="margin-top:0">Изображение</h3>
                    <img v-if="preview" :src="preview" style="width:100%;border-radius:12px;margin-bottom:10px;max-height:200px;object-fit:cover">
                    <input type="file" accept="image/*" class="inp" @change="onImage">
                    <p class="hint">JPG, PNG, WEBP до 5 МБ</p>
                </div>

                <div class="vcard">
                    <h3 style="margin-top:0">Статус</h3>
                    <select v-model="form.status" class="inp">
                        <option v-for="(m, k) in L.moderation" :key="k" :value="k">{{ m.label }}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.hint{color:var(--mut);font-size:12px;margin-top:8px}
.err{color:var(--rose,#fb7185);font-size:12px;margin-top:4px}
.tlink{opacity:.75;transition:.2s;color:var(--mut);text-decoration:none}
.tlink:hover{opacity:1}
</style>
