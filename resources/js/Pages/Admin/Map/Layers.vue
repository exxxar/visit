<script setup>
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import VButton from '@/Components/ui/VButton.vue'

const props = defineProps(['layers'])

const form = useForm({
    name: '',
    description: '',
    color: '#22d3ee',
    icon: '📍',
    sort_order: 0,
    is_active: true
})

const expandedLayerId = ref(null)

const toggleLayer = (id) => {
    expandedLayerId.value = expandedLayerId.value === id ? null : id
}

const submit = () => {
    form.post('/admin/map/layers', {
        onSuccess: () => form.reset('name', 'description', 'sort_order')
    })
}

const deleteLayer = (id) => {
    if (confirm('Вы уверены? Удаление слоя также удалит ВСЕ объекты, привязанные к нему.')) {
        useForm().delete(`/admin/map/layers/${id}`, { preserveScroll: true })
    }
}

const deleteObject = (layerId, objectId) => {
    if (confirm('Удалить этот объект с карты?')) {
        useForm().delete(`/admin/map/objects/${objectId}`, {
            preserveScroll: true,
            onSuccess: () => {
                // Опционально: можно сделать reload, чтобы обновить счетчик,
                // но для простоты пока оставим как есть, или сделаем:
                // window.location.reload()
            }
        })
    }
}
</script>

<template>
    <div>
        <div class="pg-head"><h1>🗺 Слои карты</h1></div>

        <!-- Форма создания слоя -->
        <div class="vcard" style="margin-bottom:24px">
            <h3 style="margin-top:0">Добавить новый слой</h3>
            <form @submit.prevent="submit" style="margin-top:16px">
                <div style="display:grid; grid-template-columns: 2fr 3fr 100px 80px 80px auto; gap:12px; align-items:end">
                    <div>
                        <label class="lbl">Название</label>
                        <input v-model="form.name" class="inp" placeholder="Напр. Стрит-арт" required>
                    </div>
                    <div>
                        <label class="lbl">Описание</label>
                        <input v-model="form.description" class="inp" placeholder="Краткое описание слоя">
                    </div>
                    <div>
                        <label class="lbl">Цвет</label>
                        <input v-model="form.color" type="color" class="inp" style="height:42px; padding:4px; cursor:pointer">
                    </div>
                    <div>
                        <label class="lbl">Иконка</label>
                        <input v-model="form.icon" class="inp" placeholder="📍" style="text-align:center; font-size:20px" maxlength="2">
                    </div>
                    <div>
                        <label class="lbl">Сорт</label>
                        <input v-model.number="form.sort_order" type="number" class="inp" style="text-align:center">
                    </div>
                    <VButton type="submit" :busy="form.processing" style="height:42px">Создать</VButton>
                </div>
            </form>
        </div>

        <!-- Таблица слоев с раскрывающимися строками -->
        <div class="vcard" style="padding:0; overflow:hidden">
            <table class="vtable">
                <thead>
                <tr>
                    <th style="width:40px"></th>
                    <th style="width:60px">Иконка</th>
                    <th>Название и описание</th>
                    <th style="width:100px">Цвет</th>
                    <th style="width:80px">Объектов</th>
                    <th style="width:120px">Действия</th>
                </tr>
                </thead>
                <tbody>
                <template v-for="layer in layers" :key="layer.id">
                    <!-- Основная строка слоя -->
                    <tr style="cursor:pointer; transition:background .2s"
                        :style="{ background: expandedLayerId === layer.id ? 'rgba(34,211,238,.05)' : 'transparent' }"
                        @click="toggleLayer(layer.id)">
                        <td style="text-align:center; color:var(--mut)">
                            <span :style="{ transform: expandedLayerId === layer.id ? 'rotate(90deg)' : 'rotate(0deg)', transition: 'transform .2s', display:'inline-block' }">▶</span>
                        </td>
                        <td style="font-size:28px; text-align:center">{{ layer.icon }}</td>
                        <td>
                            <b style="font-size:15px">{{ layer.name }}</b>
                            <br><small style="color:var(--mut)">{{ layer.description || 'Без описания' }}</small>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:8px">
                                <span :style="{ width:'16px', height:'16px', borderRadius:'50%', background:layer.color, border:'1px solid rgba(255,255,255,.2)' }"></span>
                                <code style="font-size:12px; color:var(--mut)">{{ layer.color }}</code>
                            </div>
                        </td>
                        <td style="text-align:center">
                                <span class="chip" style="font-size:12px; padding:4px 10px; margin:0">
                                    {{ layer.objects_count }}
                                </span>
                        </td>
                        <td @click.stop>
                            <VButton variant="ghost" size="sm" style="color:var(--rose)" @click="deleteLayer(layer.id)">
                                Удалить слой
                            </VButton>
                        </td>
                    </tr>

                    <!-- Раскрывающаяся панель с объектами -->
                    <tr v-if="expandedLayerId === layer.id">
                        <td colspan="6" style="padding:0; border-top:1px solid var(--line)">
                            <div style="padding:24px; background:var(--bg2)">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px">
                                    <h4 style="margin:0; font-size:15px; color:var(--cyan)">
                                        📍 Объекты слоя «{{ layer.name }}»
                                    </h4>
                                    <!-- Кнопка ведет на страницу объектов с уже выбранным слоем -->
                                    <Link :href="`/admin/map/objects?layer_id=${layer.id}`" class="btn btn-sm btn-grad">
                                        + Добавить объект на этот слой
                                    </Link>
                                </div>

                                <div v-if="layer.objects.length === 0" style="text-align:center; padding:20px; color:var(--mut); font-size:14px; background:var(--panel); border-radius:12px; border:1px dashed var(--line)">
                                    В этом слое пока нет объектов. Нажмите кнопку выше, чтобы добавить.
                                </div>

                                <table v-else class="vtable" style="background:var(--panel); border-radius:12px; overflow:hidden">
                                    <thead style="background:rgba(0,0,0,.2)">
                                    <tr>
                                        <th>Тип</th>
                                        <th>Название</th>
                                        <th>Координаты</th>
                                        <th style="width:100px">Действия</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="obj in layer.objects" :key="obj.id">
                                        <td>
                                                    <span v-if="obj.place_id" class="chip" style="font-size:11px; padding:3px 8px; margin:0; background:rgba(34,211,238,.1); color:var(--cyan); border-color:rgba(34,211,238,.3)">
                                                        🏢 Заведение
                                                    </span>
                                            <span v-else class="chip" style="font-size:11px; padding:3px 8px; margin:0; background:rgba(139,92,246,.1); color:var(--violet); border-color:rgba(139,92,246,.3)">
                                                        ⭐ Кастом
                                                    </span>
                                        </td>
                                        <td><b>{{ obj.place?.name || obj.title }}</b></td>
                                        <td style="font-family:monospace; font-size:12px; color:var(--mut)">
                                            {{ obj.lat }}, {{ obj.lng }}
                                        </td>
                                        <td @click.stop>
                                            <VButton variant="ghost" size="sm" style="color:var(--rose); font-size:12px; padding:4px 8px" @click="deleteObject(layer.id, obj.id)">
                                                Удалить
                                            </VButton>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                </template>

                <tr v-if="!layers.length">
                    <td colspan="6" style="text-align:center; padding:40px; color:var(--mut)">Слои пока не созданы</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>
.lbl { display:block; font-size:11px; font-weight:700; color:var(--mut); text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px; }
</style>
