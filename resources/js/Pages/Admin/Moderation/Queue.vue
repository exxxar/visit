<script setup>
import { Link } from '@inertiajs/vue3'
import VButton from '@/Components/ui/VButton.vue'
import VBadge from '@/Components/ui/VBadge.vue'
import VModal from '@/Components/ui/VModal.vue'
import { useModeration } from '@/composables/useModeration'
import { useLabels } from '@/composables/useLabels'

const props = defineProps(['tab', 'items', 'counts'])
const { modal, comment, processing, approve, openModal, submitModal, closeModal } = useModeration()
const { L, badge } = useLabels()

const TABS = [
    ['places', 'Заведения'], ['news', 'Новости'], ['events', 'Афиша'],
    ['reviews', 'Отзывы'], ['applications', 'Заявки'],
]

const title = (i) => i.name ?? i.title ?? i.org_name
const sub = (i) =>
    props.tab === 'places' ? `${i.category?.name} · ${i.district?.name}` :
        props.tab === 'reviews' ? `${i.place?.name} · ★${i.rating}` :
            i.place?.name ?? `${i.contact_name} · ${i.contact_phone}`
</script>

<template>
    <div>
        <div class="pg-head"><h1>Модерация</h1></div>

        <div class="tabs">
            <Link v-for="[key, label] in TABS" :key="key" :href="`/admin/moderation?tab=${key}`"
                  class="tab" :class="{ on: tab === key }">
                {{ label }}<span class="cnt">{{ counts[key] }}</span>
            </Link>
        </div>

        <div v-if="!items.data.length" class="vcard" style="text-align:center;color:var(--mut)">
            Очередь пуста — всё отмодерировано ✨
        </div>

        <div v-for="i in items.data" :key="i.id" class="vcard mrow">
            <div class="mrow__main">
                <b>{{ title(i) }}</b>
                <span>{{ sub(i) }}</span>
                <p v-if="tab === 'reviews' || tab === 'applications'" class="mrow__text">
                    {{ i.text ?? i.description }}
                </p>
                <VBadge v-if="tab === 'applications'" v-bind="badge(L.application, i.status)" />
                <VBadge v-else-if="tab === 'reviews'" v-bind="badge(L.review, i.status)" />
                <VBadge v-else v-bind="badge(L.moderation, i.status)" />
            </div>

            <div class="mrow__act">
                <VButton size="sm" :busy="processing" @click="approve(tab, i.id)">✓ Одобрить</VButton>
                <VButton size="sm" variant="ghost" @click="openModal(i, tab, 'returned')">↩ На правки</VButton>
                <VButton size="sm" variant="danger" @click="openModal(i, tab, 'rejected')">✕ Отклонить</VButton>
            </div>
        </div>

        <div class="pager">
            <Link v-for="l in items.links" :key="l.label" :href="l.url ?? '#'" preserve-scroll
                  :class="{ on: l.active, off: !l.url }" v-html="l.label" />
        </div>

        <VModal :show="!!modal" :title="modal?.action === 'rejected' ? 'Отклонить' : 'Вернуть на правки'" @close="closeModal">
            <p style="margin-top:0;color:var(--mut)">{{ modal?.title }}</p>
            <label class="lbl">Комментарий для владельца</label>
            <textarea v-model="comment" class="inp" rows="4" placeholder="Что нужно исправить…"></textarea>
            <div style="display:flex;gap:10px;margin-top:16px">
                <VButton :busy="processing" @click="submitModal">Сохранить решение</VButton>
                <VButton variant="ghost" @click="closeModal">Отмена</VButton>
            </div>
        </VModal>
    </div>
</template>

<style scoped>
.mrow{display:flex;gap:16px;align-items:center;margin-bottom:12px;flex-wrap:wrap}
.mrow__main{flex:1;min-width:240px}
.mrow__main b{display:block;font-size:15px}
.mrow__main span{color:var(--mut);font-size:13px}
.mrow__text{color:var(--mut);font-size:13px;margin:8px 0}
.mrow__act{display:flex;gap:8px;flex-wrap:wrap}
</style>
