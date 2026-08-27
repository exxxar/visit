<script setup>
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import VButton from '@/Components/ui/VButton.vue'
import { useLabels } from '@/composables/useLabels'

const props = defineProps(['event', 'types', 'places'])
const { L } = useLabels()

const isEdit = !!props.event

const toLocalDT = (iso) => iso ? iso.slice(0, 16) : ''

const form = useForm({
    title: props.event?.title ?? '',
    type: props.event?.type?.value ?? props.event?.type ?? '',
    starts_at: toLocalDT(props.event?.starts_at),
    ends_at: toLocalDT(props.event?.ends_at),
    place_id: props.event?.place_id ?? '',
    description: props.event?.description ?? '',
    price: props.event?.price ?? '',
    image: null,
    status: props.event?.status ?? 'approved',
})

const preview = ref(props.event?.image ? `/storage/${props.event.image}` : null)

const onImage = (e) => {
    form.image = e.target.files[0] ?? null
    if (form.image) {
        if (preview.value && !preview.value.startsWith('/storage/')) URL.revokeObjectURL(preview.value)
        preview.value = URL.createObjectURL(form.image)
    }
}

const submit = () => {
    const url = isEdit ? `/admin/events/${props.event.id}` : '/admin/events'
    form.transform((data) => ({ ...data, _method: isEdit ? 'PUT' : 'POST' }))
        .post(url, { forceFormData: true, preserveScroll: true })
}
</script>

<template>
    <div>
        <div class="pg-head">
            <Link href="/admin/events" class="tlink">← Назад</Link>
            <h1>{{ isEdit ? '✏️ ' + event.title : '➕ Новое событие' }}</h1>
            <VButton style="margin-left:auto" :busy="form.processing" @click="submit">Сохранить</VButton>
        </div>

        <div class="grid" style="grid-template-columns:2fr 1fr;align-items:start;gap:16px">
            <div class="vcard">
                <h3 style="margin-top:0">Основное</h3>

                <label class="lbl">Название *</label>
                <input v-model="form.title" class="inp" placeholder="Концерт группы…">
                <p v-if="form.errors.title" class="err">{{ form.errors.title }}</p>

                <div class="formgrid" style="margin-top:12px">
                    <div>
                        <label class="lbl">Тип *</label>
                        <select v-model="form.type" class="inp">
                            <option value="" disabled>Выберите…</option>
                            <option v-for="t in types" :key="t.value" :value="t.value">
                                {{ t.label }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="lbl">Цена</label>
                        <input v-model="form.price" class="inp" placeholder="от 500 ₽ / Бесплатно">
                    </div>
                </div>

                <div class="formgrid" style="margin-top:12px">
                    <div>
                        <label class="lbl">Начало *</label>
                        <input v-model="form.starts_at" type="datetime-local" class="inp">
                    </div>
                    <div>
                        <label class="lbl">Окончание</label>
                        <input v-model="form.ends_at" type="datetime-local" class="inp">
                    </div>
                </div>

                <label class="lbl" style="margin-top:12px">Место проведения</label>
                <select v-model="form.place_id" class="inp">
                    <option value="">Не указано</option>
                    <option v-for="p in places" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>

                <label class="lbl" style="margin-top:12px">Описание</label>
                <textarea v-model="form.description" class="inp" rows="8" placeholder="Расскажите о событии…"></textarea>
            </div>

            <div style="display:flex;flex-direction:column;gap:16px">
                <div class="vcard">
                    <h3 style="margin-top:0">Афиша</h3>
                    <img v-if="preview" v-lazy="preview" style="width:100%;border-radius:12px;margin-bottom:10px;max-height:200px;object-fit:cover">
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
