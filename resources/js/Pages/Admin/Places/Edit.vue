<script setup>
import { computed, ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
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
    description: props.description ?? '',
    price_level: props.place.price_level,
    status: props.place.status,
    is_featured: props.place.is_featured,
    socials: props.place.socials ?? {},
    // новые поля
    photos: [],              // новые файлы
    delete_photos: [],       // ID удаляемых фото
    cover_photo_id: props.place.photos?.find(p => p.is_cover)?.id ?? props.place.photos?.[0]?.id ?? null,
})

const roots = computed(() => props.categories.filter((c) => !c.parent_id))
const childrenOf = (id) => props.categories.filter((c) => c.parent_id === id)

/* ---------- фото ---------- */
const newPreviews = ref([])

const onPhotoSelect = (e) => {
    const files = Array.from(e.target.files || [])
    form.photos = files
    // освобождаем старые ObjectURL
    newPreviews.value.forEach(u => URL.revokeObjectURL(u))
    newPreviews.value = files.map(f => URL.createObjectURL(f))
}

const removeNewPhoto = (idx) => {
    URL.revokeObjectURL(newPreviews.value[idx])
    newPreviews.value.splice(idx, 1)
    form.photos.splice(idx, 1)
}

const toggleDelete = (photoId) => {
    const i = form.delete_photos.indexOf(photoId)
    if (i === -1) {
        form.delete_photos.push(photoId)
        // если удаляем текущую обложку — сбрасываем
        if (form.cover_photo_id === photoId) {
            form.cover_photo_id = null
        }
    } else {
        form.delete_photos.splice(i, 1)
    }
}

const isDeleted = (photoId) => form.delete_photos.includes(photoId)

const setCover = (photoId) => {
    form.cover_photo_id = photoId
}

/* ---------- сохранение ---------- */
const save = () => {
    // Inertia + useForm не умеет автоматически отправлять multipart через PUT,
    // поэтому используем post с _method=PUT
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(`/admin/places/${props.place.id}`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            newPreviews.value.forEach(u => URL.revokeObjectURL(u))
            newPreviews.value = []
            form.photos = []
            form.delete_photos = []
        },
    })
}
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
            </VCard>
        </div>

        <!-- ===== ФОТО ===== -->
        <VCard style="margin-top:18px">
            <h3 style="margin-top:0">Фотографии</h3>

            <!-- существующие фото -->
            <div v-if="place.photos?.length" class="ph-grid">
                <div
                    v-for="ph in place.photos"
                    :key="ph.id"
                    class="ph-item"
                    :class="{
                        'ph-item--cover': form.cover_photo_id === ph.id && !isDeleted(ph.id),
                        'ph-item--deleted': isDeleted(ph.id),
                    }"
                >
                    <img :src="ph.path" :alt="ph.name ?? ''">

                    <div class="ph-item__overlay">
                        <button
                            v-if="!isDeleted(ph.id)"
                            type="button"
                            class="ph-btn"
                            :class="{ 'ph-btn--on': form.cover_photo_id === ph.id }"
                            title="Сделать обложкой"
                            @click="setCover(ph.id)"
                        >★</button>
                        <button
                            type="button"
                            class="ph-btn ph-btn--danger"
                            title="Удалить"
                            @click="toggleDelete(ph.id)"
                        >{{ isDeleted(ph.id) ? '↩' : '✕' }}</button>
                    </div>

                    <div v-if="form.cover_photo_id === ph.id && !isDeleted(ph.id)" class="ph-item__badge">
                        Обложка
                    </div>
                    <div v-if="isDeleted(ph.id)" class="ph-item__badge ph-item__badge--del">
                        будет удалено
                    </div>
                </div>
            </div>
            <p v-else class="mut" style="margin:8px 0">Пока нет фото</p>

            <!-- загрузка новых -->
            <div style="margin-top:16px">
                <label class="lbl">Добавить новые фото</label>
                <input
                    type="file"
                    accept="image/*"
                    multiple
                    class="inp"
                    @change="onPhotoSelect"
                >
                <p class="hint">Первое загруженное фото можно сделать обложкой после сохранения.</p>
            </div>

            <!-- превью новых фото -->
            <div v-if="newPreviews.length" class="ph-grid" style="margin-top:12px">
                <div v-for="(src, i) in newPreviews" :key="i" class="ph-item ph-item--new">
                    <img :src="src" alt="">
                    <div class="ph-item__overlay">
                        <button type="button" class="ph-btn ph-btn--danger" title="Убрать из загрузки" @click="removeNewPhoto(i)">✕</button>
                    </div>
                    <div class="ph-item__badge ph-item__badge--new">новое</div>
                </div>
            </div>

            <p v-if="form.errors.photos" class="err" style="margin-top:8px">{{ form.errors.photos }}</p>
        </VCard>
    </div>
</template>

<style scoped>
.ph-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 10px;
    margin-top: 10px;
}

.ph-item {
    position: relative;
    aspect-ratio: 4 / 3;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--line);
    background: var(--panel);
    transition: .2s;
}

.ph-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.ph-item--cover {
    outline: 2px solid var(--cyan);
    outline-offset: -2px;
}

.ph-item--deleted {
    opacity: .4;
    filter: grayscale(.8);
}

.ph-item--deleted img {
    filter: blur(1px);
}

.ph-item__overlay {
    position: absolute;
    inset: auto 0 0 0;
    display: flex;
    justify-content: flex-end;
    gap: 4px;
    padding: 6px;
    background: linear-gradient(0deg, rgba(0,0,0,.55), transparent);
    opacity: 0;
    transition: opacity .2s;
}

.ph-item:hover .ph-item__overlay,
.ph-item--deleted .ph-item__overlay {
    opacity: 1;
}

.ph-btn {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    border: none;
    background: rgba(255,255,255,.95);
    color: #222;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: .2s;
}

.ph-btn:hover {
    transform: scale(1.08);
}

.ph-btn--on {
    background: var(--cyan);
    color: #fff;
}

.ph-btn--danger {
    background: rgba(255,255,255,.95);
    color: var(--rose, #ff5c7a);
}

.ph-btn--danger:hover {
    background: var(--rose, #ff5c7a);
    color: #fff;
}

.ph-item__badge {
    position: absolute;
    top: 6px;
    left: 6px;
    padding: 2px 8px;
    border-radius: 6px;
    background: var(--cyan);
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
}

.ph-item__badge--del {
    background: var(--rose, #ff5c7a);
}

.ph-item__badge--new {
    background: var(--lime, #a3e635);
    color: #0b101d;
}

.hint {
    color: var(--mut);
    font-size: 12px;
    margin: 6px 0 0;
}
</style>
