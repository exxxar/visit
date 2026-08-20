<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import VButton from '@/Components/ui/VButton.vue'
import VCard from '@/Components/ui/VCard.vue'

const props = defineProps(['settings'])
const form = reactive(JSON.parse(JSON.stringify(props.settings)))

const save = () => router.put('/admin/settings', form, { preserveScroll: true })
</script>

<template>
    <div>
        <div class="pg-head">
            <h1>Настройки</h1>
            <VButton style="margin-left:auto" @click="save">Сохранить</VButton>
        </div>

        <div class="grid grid--2">
            <VCard>
                <h3 style="margin-top:0">Hero-блок</h3>
                <label class="lbl">Заголовок</label><input v-model="form.hero.title" class="inp">
                <label class="lbl" style="margin-top:12px">Подзаголовок</label><input v-model="form.hero.sub" class="inp">
                <label class="lbl" style="margin-top:12px">Доп. текст</label><input v-model="form.hero.add" class="inp">
            </VCard>

            <VCard>
                <h3 style="margin-top:0">Счётчики</h3>
                <div class="formgrid">
                    <div><label class="lbl">Мест</label><input v-model="form.counters.places" class="inp"></div>
                    <div><label class="lbl">Категорий</label><input v-model="form.counters.categories" class="inp"></div>
                    <div><label class="lbl">Страниц в печати</label><input v-model="form.counters.pages" class="inp"></div>
                </div>
            </VCard>

            <VCard>
                <h3 style="margin-top:0">Соцсети</h3>
                <label class="lbl">Telegram</label><input v-model="form.socials.telegram" class="inp">
                <label class="lbl" style="margin-top:12px">VK</label><input v-model="form.socials.vk" class="inp">
                <label class="lbl" style="margin-top:12px">Instagram</label><input v-model="form.socials.instagram" class="inp">
            </VCard>

            <VCard>
                <h3 style="margin-top:0">Контакты</h3>
                <label class="lbl">Email</label><input v-model="form.contacts.email" class="inp">
                <label class="lbl" style="margin-top:12px">Телефон</label><input v-model="form.contacts.phone" class="inp">
                <label class="lbl" style="margin-top:12px">Адрес редакции</label><input v-model="form.contacts.address" class="inp">
            </VCard>
        </div>
    </div>
</template>
