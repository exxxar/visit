<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import VButton from '@/Components/ui/VButton.vue'
import VCard from '@/Components/ui/VCard.vue'
import { useLabels } from '@/composables/useLabels'
import { useToast } from '@/composables/useToast'

const props = defineProps(['place', 'categories', 'districts'])
const { L } = useLabels()
const { success, error } = useToast()

const form = useForm({
    name: props.place.name,
    category_id: props.place.category_id,
    district_id: props.place.district_id,
    address: props.place.address,
    phone: props.place.phone,
    site: props.place.site,
    short_description: props.place.short_description ?? '',
    description: props.place.description ?? '',
    price_level: props.place.price_level,
    socials: props.place.socials ?? {},
})

const roots = computed(() => props.categories.filter((c) => !c.parent_id))
const childrenOf = (id) => props.categories.filter((c) => c.parent_id === id)

const save = () => form.put(`/account/places/${props.place.id}`)

const upload = async (e) => {
    const file = e.target.files[0]
    if (!file) return
    const fd = new FormData()
    fd.append('image', file)
    try {
        await axios.post(`/account/places/${props.place.id}/photos`, fd)
        router.reload({ only: ['place'] })
    } catch (err) {
        error('Не удалось загрузить фото')
    }
}
</script>

<template>
    <div>
        <div class="pg-head">
            <h1 style="font-size:20px">✏️ {{ place.name }}</h1>
            <VButton style="margin-left:auto" :busy="form.processing" @click="save">Сохранить</VButton>
        </div>

        <div class="vcard" style="margin-bottom:16px;color:var(--yellow);font-size:13px">
            ⚠️ После сохранения карточка уходит на ре-модерацию и временно скрывается с сайта.
        </div>

        <div class="grid grid--2">
            <VCard>
                <h3 style="margin-top:0">Основное</h3>
                <label class="lbl">Название</label><input v-model="form.name" class="inp">
                <div class="formgrid" style="margin-top:12px">
                    <div>
                        <label class="lbl">Категория</label>
                        <select v-model="form.category_id" class="inp">
                            <template v-for="r in roots" :key="r.id">
                                <option :value="r.id">{{ r.name }}</option>
                                <option v-for="c in childrenOf(r.id)" :key="c.id" :value="c.id">&nbsp;&nbsp;└ {{ c.name }}</option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="lbl">Район</label>
                        <select v-model="form.district_id" class="inp">
                            <option v-for="d in districts" :key="d.id" :value="d.id">{{ d.name }}</option>
                        </select>
                    </div>
                </div>
                <label class="lbl" style="margin-top:12px">Адрес</label><input v-model="form.address" class="inp">
                <div class="formgrid" style="margin-top:12px">
                    <div><label class="lbl">Телефон</label><input v-model="form.phone" class="inp"></div>
                    <div>
                        <label class="lbl">Цены</label>
                        <select v-model.number="form.price_level" class="inp">
                            <option :value="1">$</option><option :value="2">$$</option><option :value="3">$$$</option>
                        </select>
                    </div>
                </div>
                <label class="lbl" style="margin-top:12px">Сайт</label><input v-model="form.site" class="inp">
                <div class="formgrid" style="margin-top:12px">
                    <div><label class="lbl">Telegram</label><input v-model="form.socials.telegram" class="inp"></div>
                    <div><label class="lbl">VK</label><input v-model="form.socials.vk" class="inp"></div>
                </div>
                <label class="lbl" style="margin-top:12px">Короткое описание</label>
                <textarea v-model="form.short_description" class="inp" rows="2"></textarea>
                <label class="lbl" style="margin-top:12px">Описание</label>
                <textarea v-model="form.description" class="inp" rows="6"></textarea>
            </VCard>

            <VCard>
                <h3 style="margin-top:0">Фотографии</h3>
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px">
                    <img v-for="ph in place.photos" :key="ph.id" :src="ph.path"
                         style="width:88px;height:64px;object-fit:cover;border-radius:12px;border:1px solid var(--line)"
                         :style="{ outline: ph.is_cover ? '2px solid var(--cyan)' : 'none' }">
                </div>
                <label class="drop" style="display:block">
                    📁 <b>Добавить фото</b> (до 5 МБ)
                    <input type="file" accept="image/*" hidden @change="upload">
                </label>
                <p style="color:var(--mut);font-size:12.5px;margin-top:10px">Первое фото автоматически становится обложкой.</p>
            </VCard>
        </div>
    </div>
</template>
