<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import VButton from '@/Components/ui/VButton.vue'
import VBadge from '@/Components/ui/VBadge.vue'
import VModal from '@/Components/ui/VModal.vue'
import { useLabels } from '@/composables/useLabels'

const props = defineProps(['stories'])
const { L, badge } = useLabels()

const show = ref(false)
const form = useForm({ place_id: '', media: null, title: '', text: '' })

const onFile = e => { form.media = e.target.files[0] }
const submit = () => form.post('/admin/stories', { onSuccess: () => { show.value = false; form.reset() } })
const act = (s, action) => router.post(`/admin/stories/${s.id}/act`, { action }, { preserveScroll: true })
const destroy = s => confirm('Удалить историю безвозвратно?') && router.delete(`/admin/stories/${s.id}`, { preserveScroll: true })
</script>

<template>
    <div>
        <div class="pg-head">
            <h1>Истории</h1>
            <VButton style="margin-left:auto" @click="show = true">+ История</VButton>
        </div>

        <div class="vcard" style="padding:0;overflow:hidden">
            <table class="vtable">
                <thead><tr><th>Медиа</th><th>Заведение</th><th>Автор</th><th>Статус</th><th>Просмотры</th><th>Истекает</th><th></th></tr></thead>
                <tbody>
                <tr v-for="s in stories.data" :key="s.id">
                    <td><img :src="`/storage/${s.media_path}`" style="width:44px;height:60px;object-fit:cover;border-radius:8px"></td>
                    <td>{{ s.place?.name ?? 'Общегородская' }}</td>
                    <td style="color:var(--mut)">{{ s.user?.name }}</td>
                    <td><VBadge v-bind="badge(L.story ?? {}, s.status)" /></td>
                    <td>{{ s.views_count }}</td>
                    <td style="color:var(--mut)">{{ s.expires_at ? new Date(s.expires_at).toLocaleDateString('ru') : '—' }}</td>
                    <td style="text-align:right;white-space:nowrap">
                        <button v-if="s.status === 'pending'" class="tlink" title="Одобрить" @click="act(s,'approve')">✅</button>
                        <button v-if="s.status === 'pending'" class="tlink" title="Отклонить" @click="act(s,'reject')">🚫</button>
                        <button v-if="s.status === 'approved'" class="tlink" title="В архив" @click="act(s,'archive')">📦</button>
                        <button class="tlink" title="Удалить" @click="destroy(s)">🗑</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <VModal :show="show" title="Новая история" @close="show = false">
            <label class="lbl">Фото или видео</label>
            <input type="file" class="inp" accept="image/*,video/mp4,video/webm" @change="onFile">
            <label class="lbl" style="margin-top:12px">Заголовок</label>
            <input v-model="form.title" class="inp" placeholder="Например: Открылась новая кофейня">
            <label class="lbl" style="margin-top:12px">Текст</label>
            <textarea v-model="form.text" class="inp" rows="4"></textarea>
            <div style="margin-top:16px">
                <VButton :busy="form.processing" @click="submit">Опубликовать</VButton>
            </div>
        </VModal>
    </div>
</template>
