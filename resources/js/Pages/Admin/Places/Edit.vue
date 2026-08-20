<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import VButton from '@/Components/ui/VButton.vue'
import VCard from '@/Components/ui/VCard.vue'
import { useLabels } from '@/composables/useLabels'

const props = defineProps(['place', 'categories', 'districts'])
const { L } = useLabels()

const form = useForm({
    name: props.place.name,
    category_id: props.place.category_id,
    district_id: props.place.district_id,
    address: props.place.address,
    lat: props.place.lat,
    lng: props.place.lng,
    phone: props.place.phone,
    email: props.place.email,
    site: props.place.site,
    short_description: props.place.short_description ?? '',
    description: props.place.description ?? '',
    price_level: props.place.price_level,
    status: props.place.status,
    is_featured: props.place.is_featured,
    socials: props.place.socials ?? {},
})

const roots = computed(() => props.categories.filter((c) => !c.parent_id))
const childrenOf = (id) => props.categories.filter((c) => c.parent_id === id)

const save = () => form.put(`/admin/places/${props.place.id}`)
</script>

<template>
    <div>
        <div class="pg-head">
            <h1 style="font-size:20px">✏️ {{ place.name }}</h1>
            <VButton style="margin-left:auto" :busy="form.processing" @click="save">Сохранить</VButton>
        </div>

        <div class="grid grid--2">
            <VCard>
                <h3 style="margin-top:0">Основное</h3>
                <label class="lbl">Название</label><input v-model="form.name" class="inp">
                <p v-if="form.errors.name" class="err">{{ form.errors.name }}</p>

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

                <label class="lbl" style="margin-top:12px">Адрес</label>
                <input v-model="form.address" class="inp">
                <div class="formgrid" style="margin-top:12px">
                    <div><label class="lbl">Lat</label><input v-model="form.lat" type="number" step="0.0000001" class="inp"></div>
                    <div><label class="lbl">Lng</label><input v-model="form.lng" type="number" step="0.0000001" class="inp"></div>
                </div>

                <div class="formgrid" style="margin-top:12px">
                    <div>
                        <label class="lbl">Ценовой уровень</label>
                        <select v-model.number="form.price_level" class="inp">
                            <option :value="1">$</option><option :value="2">$$</option><option :value="3">$$$</option>
                        </select>
                    </div>
                    <div>
                        <label class="lbl">Статус</label>
                        <select v-model="form.status" class="inp">
                            <option v-for="(m, k) in L.moderation" :key="k" :value="k">{{ m.label }}</option>
                        </select>
                    </div>
                </div>

                <label class="chk" style="margin-top:14px">
                    <input v-model="form.is_featured" type="checkbox"><i>✓</i><span>Спецразмещение ✨</span>
                </label>
            </VCard>

            <VCard>
                <h3 style="margin-top:0">Контакты и контент</h3>
                <div class="formgrid">
                    <div><label class="lbl">Телефон</label><input v-model="form.phone" class="inp"></div>
                    <div><label class="lbl">Email</label><input v-model="form.email" class="inp"></div>
                </div>
                <label class="lbl" style="margin-top:12px">Сайт</label><input v-model="form.site" class="inp">
                <div class="formgrid" style="margin-top:12px">
                    <div><label class="lbl">Telegram</label><input v-model="form.socials.telegram" class="inp"></div>
                    <div><label class="lbl">VK</label><input v-model="form.socials.vk" class="inp"></div>
                </div>
                <label class="lbl" style="margin-top:12px">Короткое описание</label>
                <textarea v-model="form.short_description" class="inp" rows="2"></textarea>
                <label class="lbl" style="margin-top:12px">Полное описание</label>
                <textarea v-model="form.description" class="inp" rows="6"></textarea>

                <h3 style="margin:18px 0 8px">Фото ({{ place.photos?.length ?? 0 }})</h3>
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                    <img v-for="ph in place.photos" :key="ph.id" :src="ph.path"
                         style="width:72px;height:54px;object-fit:cover;border-radius:10px;border:1px solid var(--line)"
                         :style="{ outline: ph.is_cover ? '2px solid var(--cyan)' : 'none' }">
                </div>
            </VCard>
        </div>
    </div>
</template>
