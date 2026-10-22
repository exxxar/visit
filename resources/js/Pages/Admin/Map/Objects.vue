<script setup>
import { ref } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'

import VButton from '@/Components/ui/VButton.vue'

const props = defineProps(['objects', 'layers', 'places'])
const showForm = ref(false)

const page = usePage()
const initialLayerId = page.props.layer_id || '' // Получаем из URL, если есть

const form = useForm({
    layer_id: initialLayerId, // <-- Используем его здесь
    mode: 'custom',
    place_id: '',
    title: '',
    description: '',
    image: '',
    lat: '',
    lng: '',
    x: '',
    y: '',
    is_active: true
})
// Если выбрали существующее заведение, подтягиваем его данные (опционально можно делать это через watch)
const usePlaceData = () => {
    const place = props.places.find(p => p.id == form.place_id)
    if (place) {
        form.title = place.name
        // lat/lng нужно будет указать вручную на карте, или брать из place, если они там есть
    }
}

const submit = () => {
    form.post('/admin/map/objects', {
        onSuccess: () => {
            showForm.value = false
            form.reset()
        }
    })
}
</script>

<template>
    <div>
        <div class="pg-head">
            <h1>📍 Объекты на карте</h1>
            <VButton @click="showForm = !showForm">{{ showForm ? 'Скрыть форму' : '+ Добавить объект' }}</VButton>
        </div>

        <div v-if="showForm" class="vcard" style="margin-bottom:24px">
            <h3>Новый объект</h3>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-top:16px">
                <div>
                    <label class="lbl">Слой</label>
                    <select v-model="form.layer_id" class="inp">
                        <option v-for="l in layers" :key="l.id" :value="l.id">{{ l.icon }} {{ l.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="lbl">Привязать к существующему заведению (необязательно)</label>
                    <select v-model="form.place_id" @change="usePlaceData" class="inp">
                        <option value="">-- Создать кастомный объект --</option>
                        <option v-for="p in places" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                </div>
            </div>

            <div v-if="!form.place_id" style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-top:16px">
                <div>
                    <label class="lbl">Название *</label>
                    <input v-model="form.title" class="inp" placeholder="Напр. Мурал 'Донецкое небо'">
                </div>
                <div>
                    <label class="lbl">Ссылка на изображение</label>
                    <input v-model="form.image" class="inp" placeholder="/storage/...">
                </div>
                <div style="grid-column: 1 / -1">
                    <label class="lbl">Описание</label>
                    <textarea v-model="form.description" class="inp" rows="3"></textarea>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-top:16px">
                <div>
                    <label class="lbl">Широта (Lat) *</label>
                    <input v-model="form.lat" type="number" step="any" class="inp" placeholder="48.0080000">
                </div>
                <div>
                    <label class="lbl">Долгота (Lng) *</label>
                    <input v-model="form.lng" type="number" step="any" class="inp" placeholder="37.8050000">
                </div>
            </div>
            <p class="ref-hint" style="margin-top:8px; font-size:12px">💡 Подсказка: откройте Google Maps, кликните правой кнопкой мыши по точке и скопируйте координаты.</p>

            <div style="margin-top:20px; display:flex; gap:12px">
                <VButton @click="submit" :busy="form.processing">Сохранить объект</VButton>
                <VButton variant="ghost" @click="showForm = false">Отмена</VButton>
            </div>
        </div>

        <div class="vcard" style="padding:0; overflow:hidden">
            <table class="vtable">
                <thead><tr><th>Слой</th><th>Тип</th><th>Название</th><th>Координаты</th><th>Действия</th></tr></thead>
                <tbody>
                <tr v-for="obj in objects" :key="obj.id">
                    <td>{{ obj.layer?.icon }} {{ obj.layer?.name }}</td>
                    <td>
                        <span v-if="obj.place_id" class="chip" style="font-size:11px; padding:4px 8px">🏢 Заведение</span>
                        <span v-else class="chip" style="font-size:11px; padding:4px 8px; background:var(--panel2)">⭐ Кастом</span>
                    </td>
                    <td><b>{{ obj.place?.name || obj.title }}</b></td>
                    <td style="font-family:monospace; font-size:12px; color:var(--mut)">{{ obj.lat }}, {{ obj.lng }}</td>
                    <td>
                        <VButton variant="ghost" style="color:var(--rose)" @click="$inertia.delete(`/admin/map/objects/${obj.id}`)">Удалить</VButton>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
