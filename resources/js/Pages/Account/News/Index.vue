<script setup>
import { useForm } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import VButton from '@/Components/ui/VButton.vue'
import VBadge from '@/Components/ui/VBadge.vue'
import VCard from '@/Components/ui/VCard.vue'
import { useLabels } from '@/composables/useLabels'

const props = defineProps(['news', 'places'])
const { badge, L } = useLabels()

const form = useForm({ place_id: props.places[0]?.id ?? '', title: '', body: '', image: '' })
const submit = () => form.post('/account/news', { onSuccess: () => form.reset('title', 'body', 'image') })

const destroy = (n) => confirm('Удалить новость?') && router.delete(`/account/news/${n.id}`, { preserveScroll: true })
</script>

<template>
    <div>
        <div class="pg-head"><h1>Новости заведения</h1></div>

        <VCard style="margin-bottom:18px">
            <h3 style="margin-top:0">Новая новость</h3>
            <div class="formgrid">
                <div>
                    <label class="lbl">Заведение</label>
                    <select v-model="form.place_id" class="inp">
                        <option v-for="p in places" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                </div>
                <div><label class="lbl">Заголовок</label><input v-model="form.title" class="inp"></div>
            </div>
            <label class="lbl" style="margin-top:12px">Текст</label>
            <textarea v-model="form.body" class="inp" rows="3"></textarea>
            <VButton style="margin-top:14px" :busy="form.processing" @click="submit">Отправить на модерацию</VButton>
        </VCard>

        <div class="vcard" style="padding:0;overflow:hidden">
            <table class="vtable">
                <thead><tr><th>Заголовок</th><th>Заведение</th><th>Статус</th><th></th></tr></thead>
                <tbody>
                <tr v-for="n in news.data" :key="n.id">
                    <td><b>{{ n.title }}</b></td>
                    <td style="color:var(--mut)">{{ n.place?.name }}</td>
                    <td><VBadge v-bind="badge(L.moderation, n.status)" /></td>
                    <td style="text-align:right"><button class="tlink" @click="destroy(n)">🗑</button></td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="pager">
            <component :is="'Link'" v-for="l in news.links" :key="l.label" :href="l.url ?? '#'" preserve-scroll
                       :class="{ on: l.active, off: !l.url }" v-html="l.label" />
        </div>
    </div>
</template>
