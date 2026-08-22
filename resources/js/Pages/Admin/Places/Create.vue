<script setup>
import { ref, computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import VButton from '@/Components/ui/VButton.vue'

const props = defineProps(['categories', 'districts', 'owners'])

const DAYS = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс']

const form = useForm({
    name: '', category_id: '', district_id: '', address: '',
    phone: '', email: '', site: '', price_level: 2,
    short_description: '', description: '',
    lat: '', lng: '', is_featured: false,
    owner_email: '',
    socials: { telegram: '', vk: '' },
    working_hours: [],
    photos: [],
    hours: DAYS.map(d => ({ d, open: true, from: '09:00', to: '21:00' })),
})

const previews = ref([])

const flatCats = computed(() => {
    const roots = props.categories.filter(c => !c.parent_id)
    const out = []
    roots.forEach(r => {
        out.push(r)
        props.categories
            .filter(c => c.parent_id === r.id)
            .forEach(ch => out.push({ ...ch, name: '— ' + ch.name }))
    })
    return out
})

const onPhotos = e => {
    form.photos = [...e.target.files]
    previews.value.forEach(u => URL.revokeObjectURL(u))
    previews.value = form.photos.map(f => URL.createObjectURL(f))
}

const submit = () => {
    form.working_hours = form.hours
        .filter(h => h.open)
        .map(({ d, from, to }) => ({ d, from, to }))
    form.post('/admin/places', { preserveScroll: true })
}
</script>

<template>
    <div>
        <div class="pg-head">
            <Link href="/admin/places" class="tlink">← Назад</Link>
            <h1>Новое заведение</h1>
            <VButton style="margin-left:auto" :busy="form.processing" @click="submit">Сохранить</VButton>
        </div>

        <div class="formgrid" style="grid-template-columns:2fr 1fr;align-items:start">
            <!-- левая колонка -->
            <div style="display:flex;flex-direction:column;gap:16px">
                <div class="vcard">
                    <h3 class="vcard__t">Основное</h3>
                    <label class="lbl">Название *</label>
                    <input v-model="form.name" class="inp" placeholder="Например: Кофейня «Уголок»">
                    <div v-if="form.errors.name" class="err">{{ form.errors.name }}</div>

                    <div class="formgrid" style="margin-top:12px">
                        <div>
                            <label class="lbl">Категория *</label>
                            <select v-model="form.category_id" class="inp">
                                <option value="" disabled>Выберите…</option>
                                <option v-for="c in flatCats" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="lbl">Район *</label>
                            <select v-model="form.district_id" class="inp">
                                <option value="" disabled>Выберите…</option>
                                <option v-for="d in districts" :key="d.id" :value="d.id">{{ d.name }}</option>
                            </select>
                        </div>
                    </div>

                    <label class="lbl" style="margin-top:12px">Адрес *</label>
                    <input v-model="form.address" class="inp" placeholder="ул. Артёма, 100">

                    <div class="formgrid" style="margin-top:12px">
                        <div>
                            <label class="lbl">Широта (lat) *</label>
                            <input v-model="form.lat" class="inp" placeholder="48.008">
                        </div>
                        <div>
                            <label class="lbl">Долгота (lng) *</label>
                            <input v-model="form.lng" class="inp" placeholder="37.805">
                        </div>
                    </div>

                    <label class="lbl" style="margin-top:12px">Краткое описание (для карточек)</label>
                    <textarea v-model="form.short_description" class="inp" rows="2" maxlength="300"></textarea>

                    <label class="lbl" style="margin-top:12px">Полное описание</label>
                    <textarea v-model="form.description" class="inp" rows="5"></textarea>
                </div>

                <div class="vcard">
                    <h3 class="vcard__t">Фотографии</h3>
                    <input type="file" multiple accept="image/*" class="inp" @change="onPhotos">
                    <div v-if="previews.length" class="ph-preview">
                        <div v-for="(src, i) in previews" :key="i" class="ph-item">
                            <img :src="src" alt="">
                            <span v-if="i === 0" class="ph-cover">обложка</span>
                        </div>
                    </div>
                    <p class="hint">Первое фото станет обложкой карточки.</p>
                </div>

                <div class="vcard">
                    <h3 class="vcard__t">Часы работы</h3>
                    <div v-for="h in form.hours" :key="h.d" class="hour-row">
                        <label class="chk" style="width:70px">
                            <input v-model="h.open" type="checkbox"><i>✓</i><span>{{ h.d }}</span>
                        </label>
                        <input v-model="h.from" type="time" class="inp" :disabled="!h.open">
                        <span class="mut">—</span>
                        <input v-model="h.to" type="time" class="inp" :disabled="!h.open">
                        <span v-if="!h.open" class="mut">закрыто</span>
                    </div>
                </div>
            </div>

            <!-- правая колонка -->
            <div style="display:flex;flex-direction:column;gap:16px">
                <div class="vcard">
                    <h3 class="vcard__t">Контакты</h3>
                    <label class="lbl">Телефон</label>
                    <input v-model="form.phone" class="inp" placeholder="+380 71 000 00 00">
                    <label class="lbl" style="margin-top:10px">E-mail</label>
                    <input v-model="form.email" class="inp" type="email">
                    <label class="lbl" style="margin-top:10px">Сайт</label>
                    <input v-model="form.site" class="inp" placeholder="https://…">
                    <label class="lbl" style="margin-top:10px">Telegram</label>
                    <input v-model="form.socials.telegram" class="inp" placeholder="@handle">
                    <label class="lbl" style="margin-top:10px">VK</label>
                    <input v-model="form.socials.vk" class="inp" placeholder="vk.com/…">
                </div>

                <div class="vcard">
                    <h3 class="vcard__t">Статус и владелец</h3>
                    <label class="lbl">Ценовой уровень</label>
                    <select v-model.number="form.price_level" class="inp">
                        <option :value="1">$ — бюджетно</option>
                        <option :value="2">$$ — средне</option>
                        <option :value="3">$$$ — премиум</option>
                    </select>

                    <label class="lbl" style="margin-top:10px">Владелец (из кабинета бизнеса)</label>
                    <select v-model="form.owner_email" class="inp">
                        <option value="">Без владельца</option>
                        <option v-for="o in owners" :key="o.id" :value="o.email">{{ o.name }} · {{ o.email }}</option>
                    </select>

                    <label class="chk" style="margin-top:12px">
                        <input v-model="form.is_featured" type="checkbox"><i>✓</i>
                        <span>⭐ Спецразмещение</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.ph-preview{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px}
.ph-item{position:relative;width:84px;height:84px;border-radius:12px;overflow:hidden;border:1px solid var(--line)}
.ph-item img{width:100%;height:100%;object-fit:cover}
.ph-cover{position:absolute;left:0;right:0;bottom:0;font-size:9px;text-align:center;
    background:rgba(0,0,0,.65);color:#fff;padding:2px 0;text-transform:uppercase;letter-spacing:.05em}
.hour-row{display:flex;align-items:center;gap:10px;margin-bottom:8px}
.hour-row .inp{width:110px}
.hint{color:var(--mut);font-size:12px;margin-top:8px}
.err{color:var(--rose);font-size:12px;margin-top:4px}
.mut{color:var(--mut);font-size:13px}
</style>
