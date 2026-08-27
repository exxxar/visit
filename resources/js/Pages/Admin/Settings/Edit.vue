<script setup>
import { reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import VButton from '@/Components/ui/VButton.vue'
import VCard from '@/Components/ui/VCard.vue'

const props = defineProps(['settings'])
const form = reactive(JSON.parse(JSON.stringify(props.settings)))

// гарантируем, что defaults существует
if (!form.districts) form.districts = {}
if (!form.districts.defaults) form.districts.defaults = {}

const DISTRICTS = [
    'Куйбышевский',
    'Киевский',
    'Калининский',
    'Кировский',
    'Ворошиловский',
    'Будённовский',
    'Петровский',
    'Ленинский',
    'Пролетарский',
]

// гарантируем, что для каждого района есть ключ
DISTRICTS.forEach(d => {
    if (form.districts.defaults[d] === undefined) {
        form.districts.defaults[d] = 0
    }
})

const totalPlaces = computed(() =>
    Object.values(form.districts.defaults).reduce((a, b) => a + Number(b || 0), 0)
)

const save = () => router.put('/admin/settings', form, { preserveScroll: true })

const recalculate = () => {
    if (!confirm('Заполнить дефолты актуальным количеством одобренных мест из БД?')) return
    router.post('/admin/settings/recalculate-districts', {}, {
        preserveScroll: true,
        onSuccess: () => {
            // после пересчёта перезагружаем страницу, чтобы получить новые данные
            window.location.reload()
        },
    })
}
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

            <!-- ===== РАЙОНЫ ===== -->
            <VCard style="grid-column:1/-1">
                <div style="display:flex;align-items:center;gap:14px;margin-bottom:14px">
                    <h3 style="margin:0">🗺 Секция «Районы»</h3>
                    <VButton size="sm" variant="ghost" @click="recalculate">
                        ↻ Пересчитать из БД
                    </VButton>
                </div>

                <div class="formgrid">
                    <div>
                        <label class="lbl">Kicker</label>
                        <input v-model="form.districts.kicker" class="inp" placeholder="04 · Районы">
                    </div>
                    <div>
                        <label class="lbl">Подзаголовок</label>
                        <input v-model="form.districts.sub" class="inp">
                    </div>
                </div>

                <div class="formgrid" style="margin-top:12px">
                    <div>
                        <label class="lbl">Заголовок (первая строка)</label>
                        <input v-model="form.districts.title" class="inp">
                    </div>
                    <div>
                        <label class="lbl">Заголовок (вторая строка, градиент)</label>
                        <input v-model="form.districts.title_grad" class="inp">
                    </div>
                </div>

                <h4 style="margin:18px 0 10px">Дефолтные значения (количество мест)</h4>
                <p class="hint" style="margin-bottom:14px">
                    Эти числа отображаются, если реальные данные из БД недоступны.
                    Всего по умолчанию: <b style="color:var(--cyan)">{{ totalPlaces }}</b> мест.
                </p>

                <div class="def-grid">
                    <label v-for="d in DISTRICTS" :key="d" class="def-item">
                        <span class="def-item__name">{{ d }}</span>
                        <input
                            v-model.number="form.districts.defaults[d]"
                            type="number"
                            min="0"
                            class="inp"
                        >
                    </label>
                </div>
            </VCard>
        </div>
    </div>
</template>

<style scoped>
.hint{color:var(--mut);font-size:12px;margin-top:4px}

.def-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill, minmax(220px, 1fr));
    gap:10px;
}
.def-item{
    display:flex;align-items:center;gap:10px;
    padding:10px 12px;
    background:var(--panel);
    border:1px solid var(--line);
    border-radius:10px;
    transition:.2s;
}
.def-item:focus-within{
    border-color:var(--cyan);
}
.def-item__name{
    flex:1;font-size:13px;font-weight:600;color:var(--txt);
}
.def-item .inp{
    width:80px;text-align:center;padding:6px 8px;font-size:14px;font-weight:700;
}
</style>
