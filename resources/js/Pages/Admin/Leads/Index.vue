<script setup>
import { router } from '@inertiajs/vue3'
import VBadge from '@/Components/ui/VBadge.vue'
import { useLabels } from '@/composables/useLabels'

const props = defineProps(['leads'])
const { L } = useLabels()

const filter = (i) => router.get('/admin/leads', { interest: i || '' }, { preserveScroll: true })
</script>

<template>
    <div>
        <div class="pg-head">
            <h1>Лиды</h1>
            <a class="vbtn vbtn--ghost" href="/admin/leads/export" style="margin-left:auto">↓ CSV</a>
        </div>

        <div class="vcard" style="margin-bottom:18px;display:flex;gap:8px;flex-wrap:wrap">
            <button class="tab on" @click="filter(null)">Все</button>
            <button v-for="(label, key) in L.interest" :key="key" class="tab" @click="filter(key)">{{ label }}</button>
        </div>

        <div class="vcard" style="padding:0;overflow:hidden">
            <table class="vtable">
                <thead><tr><th>Дата</th><th>Имя</th><th>Компания</th><th>Контакты</th><th>Интерес</th><th>Согласия</th></tr></thead>
                <tbody>
                <tr v-for="l in leads.data" :key="l.id">
                    <td style="color:var(--mut)">{{ new Date(l.created_at).toLocaleDateString('ru-RU') }}</td>
                    <td><b>{{ l.name }}</b><br><span style="color:var(--mut);font-size:12px">{{ l.position }}</span></td>
                    <td style="color:var(--mut)">{{ l.company ?? '—' }}</td>
                    <td>{{ l.phone }}<br><span style="color:var(--mut);font-size:12px">{{ l.email }}</span></td>
                    <td><VBadge :label="L.interest[l.interest] ?? l.interest" color="cyan" /></td>
                    <td style="color:var(--mut)">
                        {{ l.consent_data ? '✓ данные' : '—' }} ·
                        {{ l.consent_news ? '✓ рассылка' : '—' }}
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="pager">
            <component :is="'Link'" v-for="l in leads.links" :key="l.label" :href="l.url ?? '#'" preserve-scroll
                       :class="{ on: l.active, off: !l.url }" v-html="l.label" />
        </div>
    </div>
</template>
