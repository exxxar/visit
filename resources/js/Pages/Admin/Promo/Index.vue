<script setup>
import { ref, onMounted } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import axios from 'axios'
import VButton from '@/Components/ui/VButton.vue'
import VBadge from '@/Components/ui/VBadge.vue'
import VModal from '@/Components/ui/VModal.vue'
import { useLabels } from '@/composables/useLabels'

const props = defineProps(['placements', 'slots'])
const { L, badge } = useLabels()

const places = ref([])
onMounted(async () => {
    const r = await axios.get('/api/v1/places', { params: { per_page: 100 } })
    places.value = r.data.data
})

const show = ref(false)
const form = useForm({ place_id: '', slot: 'hero', starts_at: '', ends_at: '', price: '' })
const submit = () => form.post('/admin/placements', { onSuccess: () => { show.value = false; form.reset() } })

const setStatus = (p, status) => router.put(`/admin/placements/${p.id}`, { status }, { preserveScroll: true })
const destroy = (p) => confirm('Удалить размещение?') && router.delete(`/admin/placements/${p.id}`, { preserveScroll: true })
</script>

<template>
    <div>
        <div class="pg-head">
            <h1>Рекламные размещения</h1>
            <VButton style="margin-left:auto" @click="show = true">+ Запланировать</VButton>
        </div>

        <div class="vcard" style="padding:0;overflow:hidden">
            <table class="vtable">
                <thead><tr><th>Заведение</th><th>Слот</th><th>Период</th><th>Цена</th><th>Статус</th><th></th></tr></thead>
                <tbody>
                <tr v-for="p in placements" :key="p.id">
                    <td><b>{{ p.place?.name }}</b></td>
                    <td style="color:var(--mut)">{{ slots.find((s) => s.value === p.slot)?.label }}</td>
                    <td style="color:var(--mut)">{{ p.starts_at }} → {{ p.ends_at }}</td>
                    <td>{{ p.price ? p.price + ' ₽' : '—' }}</td>
                    <td>
                        <select class="inp" style="width:auto;padding:7px 10px" :value="p.status"
                                @change="setStatus(p, $event.target.value)">
                            <option v-for="(m, k) in L.placement" :key="k" :value="k">{{ m.label }}</option>
                        </select>
                    </td>
                    <td style="text-align:right"><button class="tlink" @click="destroy(p)">🗑</button></td>
                </tr>
                </tbody>
            </table>
        </div>

        <VModal :show="show" title="Новое размещение" @close="show = false">
            <label class="lbl">Заведение</label>
            <select v-model="form.place_id" class="inp">
                <option value="" disabled>Выберите…</option>
                <option v-for="p in places" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
            <label class="lbl" style="margin-top:12px">Слот</label>
            <select v-model="form.slot" class="inp">
                <option v-for="s in slots" :key="s.value" :value="s.value">{{ s.label }}</option>
            </select>
            <div class="formgrid" style="margin-top:12px">
                <div><label class="lbl">С даты</label><input v-model="form.starts_at" type="date" class="inp"></div>
                <div><label class="lbl">По дату</label><input v-model="form.ends_at" type="date" class="inp"></div>
            </div>
            <label class="lbl" style="margin-top:12px">Цена, ₽</label>
            <input v-model="form.price" type="number" class="inp">
            <div style="display:flex;gap:10px;margin-top:16px">
                <VButton :busy="form.processing" @click="submit">Запланировать</VButton>
            </div>
        </VModal>
    </div>
</template>
