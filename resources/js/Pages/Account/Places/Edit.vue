<script setup>
import { computed, ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import axios from 'axios'
import VButton from '@/Components/ui/VButton.vue'
import VCard from '@/Components/ui/VCard.vue'
import { useToast } from '@/composables/useToast'

const props = defineProps(['place', 'categories', 'districts'])
const { success, error } = useToast()

const form = useForm({
    name: props.place.name,
    category_id: props.place.category_id,
    district_id: props.place.district_id,
    address: props.place.address,
    phone: props.place.phone,
    site: props.place.site,
    short_description: props.place.short_description ?? '',
    description: props.place.description ?? '',
    price_level: props.place.price_level,
    socials: props.place.socials ?? {},
})

const roots = computed(() => props.categories.filter((c) => !c.parent_id))
const childrenOf = (id) => props.categories.filter((c) => c.parent_id === id)

const save = () => form.put(`/account/places/${props.place.id}`)

const upload = async (e) => {
    const file = e.target.files[0]
    if (!file) return
    const fd = new FormData()
    fd.append('image', file)
    try {
        await axios.post(`/account/places/${props.place.id}/photos`, fd)
        router.reload({ only: ['place'] })
    } catch (err) {
        error('Не удалось загрузить фото')
    }
}

/* ===== ПРОДВИЖЕНИЕ ===== */
const showPromo = ref(false)
const selectedTariff = ref(null)
const promoForm = useForm({
    service: '',
    requirements: '',
})

const TARIFFS = [
    { id: 'smm', icon: '📱', title: 'Ведение соцсетей', desc: 'Посты, сторис, работа с аудиторией', price: 15000 },
    { id: 'targeting', icon: '🎯', title: 'Таргетированная реклама', desc: 'Настройка и ведение рекламных кампаний', price: 12000 },
    { id: 'content', icon: '📸', title: 'Фотосъёмка и контент', desc: 'Профессиональная съёмка заведения и блюд', price: 9000 },
    { id: 'video', icon: '🎬', title: 'Reels и видеоролики', desc: 'Создание вирусных видео для соцсетей', price: 14000 },
    { id: 'influencer', icon: '⭐', title: 'Пост у блогера', desc: 'Реклама у популярного блогера города', price: 5000 },
    { id: 'complex', icon: '🚀', title: 'Продвижение под ключ', desc: 'Комплексное продвижение на 3 месяца', price: 30000 },
]

const openPromo = () => {
    showPromo.value = true
    selectedTariff.value = null
    promoForm.service = ''
    promoForm.requirements = ''
}

const selectTariff = (tariff) => {
    selectedTariff.value = tariff
    promoForm.service = tariff.id
}

const submitPromo = () => {
    if (!selectedTariff.value) {
        error('Выберите тариф')
        return
    }

    promoForm.post(`/account/places/${props.place.id}/promote`, {
        onSuccess: () => {
            showPromo.value = false
            success(`Заявка «${selectedTariff.value.title}» создана. Рекламный партнёр скоро свяжется с вами.`)
        },
        onError: (errors) => {
            error(errors.message || 'Не удалось создать заявку')
        },
    })
}

const fmtMoney = (n) => new Intl.NumberFormat('ru-RU').format(n) + ' ₽'
</script>

<template>
    <div>
        <div class="pg-head">
            <h1 style="font-size:20px">✏️ {{ place.name }}</h1>
            <div style="margin-left:auto;display:flex;gap:10px">
                <VButton variant="ghost" @click="openPromo">🚀 Продвинуть</VButton>
                <VButton :busy="form.processing" @click="save">Сохранить</VButton>
            </div>
        </div>

        <div class="vcard" style="margin-bottom:16px;color:var(--yellow);font-size:13px">
            ⚠️ После сохранения карточка уходит на ре-модерацию и временно скрывается с сайта.
        </div>

        <div class="grid grid--2">
            <VCard>
                <h3 style="margin-top:0">Основное</h3>
                <label class="lbl">Название</label><input v-model="form.name" class="inp">
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
                <label class="lbl" style="margin-top:12px">Адрес</label><input v-model="form.address" class="inp">
                <div class="formgrid" style="margin-top:12px">
                    <div><label class="lbl">Телефон</label><input v-model="form.phone" class="inp"></div>
                    <div>
                        <label class="lbl">Цены</label>
                        <select v-model.number="form.price_level" class="inp">
                            <option :value="1">$</option><option :value="2">$$</option><option :value="3">$$$</option>
                        </select>
                    </div>
                </div>
                <label class="lbl" style="margin-top:12px">Сайт</label><input v-model="form.site" class="inp">
                <div class="formgrid" style="margin-top:12px">
                    <div><label class="lbl">Telegram</label><input v-model="form.socials.telegram" class="inp"></div>
                    <div><label class="lbl">VK</label><input v-model="form.socials.vk" class="inp"></div>
                </div>
                <label class="lbl" style="margin-top:12px">Короткое описание</label>
                <textarea v-model="form.short_description" class="inp" rows="2"></textarea>
                <label class="lbl" style="margin-top:12px">Описание</label>
                <textarea v-model="form.description" class="inp" rows="6"></textarea>
            </VCard>

            <VCard>
                <h3 style="margin-top:0">Фотографии</h3>
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px">
                    <img v-for="ph in place.photos" :key="ph.id" v-lazy="ph.path"
                         style="width:88px;height:64px;object-fit:cover;border-radius:12px;border:1px solid var(--line)"
                         :style="{ outline: ph.is_cover ? '2px solid var(--cyan)' : 'none' }">
                </div>
                <label class="drop" style="display:block">
                    📁 <b>Добавить фото</b> (до 5 МБ)
                    <input type="file" accept="image/*" hidden @change="upload">
                </label>
                <p style="color:var(--mut);font-size:12.5px;margin-top:10px">Первое фото автоматически становится обложкой.</p>
            </VCard>
        </div>

        <!-- ===== МОДАЛКА ПРОДВИЖЕНИЯ ===== -->
        <Teleport to="body">
            <div v-if="showPromo" class="promo-modal" @click.self="showPromo = false">
                <div class="promo-modal__panel">
                    <button class="promo-modal__close" @click="showPromo = false">✕</button>

                    <div class="promo-modal__head">
                        <div class="promo-modal__kicker">Продвижение</div>
                        <h2 class="promo-modal__title">Выберите тариф для «{{ place.name }}»</h2>
                        <p class="promo-modal__sub">Рекламный партнёр возьмёт заявку в работу и свяжется с вами</p>
                    </div>

                    <div class="promo-tariffs">
                        <div
                            v-for="t in TARIFFS"
                            :key="t.id"
                            class="promo-tariff"
                            :class="{ 'promo-tariff--selected': selectedTariff?.id === t.id }"
                            @click="selectTariff(t)"
                        >
                            <div class="promo-tariff__icon">{{ t.icon }}</div>
                            <div class="promo-tariff__body">
                                <h3>{{ t.title }}</h3>
                                <p>{{ t.desc }}</p>
                                <div class="promo-tariff__price">{{ fmtMoney(t.price) }}</div>
                            </div>
                            <div v-if="selectedTariff?.id === t.id" class="promo-tariff__check">✓</div>
                        </div>
                    </div>

                    <div v-if="selectedTariff" class="promo-requirements">
                        <label class="lbl">Дополнительные пожелания</label>
                        <textarea
                            v-model="promoForm.requirements"
                            class="inp"
                            rows="3"
                            placeholder="Например: нужны фото для меню, акцент на доставку..."
                        ></textarea>
                    </div>

                    <div class="promo-modal__foot">
                        <div v-if="selectedTariff" class="promo-summary">
                            <span>Итого:</span>
                            <b>{{ fmtMoney(selectedTariff.price) }}</b>
                        </div>
                        <button
                            class="btn btn-grad"
                            :disabled="!selectedTariff || promoForm.processing"
                            @click="submitPromo"
                        >
                            {{ promoForm.processing ? 'Создаём...' : 'Создать заявку' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<style scoped>
/* ===== МОДАЛКА ПРОДВИЖЕНИЯ ===== */
.promo-modal {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: rgba(4, 6, 10, 0.78);
    backdrop-filter: blur(12px);
}

.promo-modal__panel {
    position: relative;
    width: min(720px, 100%);
    max-height: calc(100vh - 40px);
    display: flex;
    flex-direction: column;
    background: rgba(14, 19, 32, 0.96);
    border: 1px solid var(--line);
    border-radius: 24px;
    backdrop-filter: blur(24px);
    box-shadow: 0 30px 80px rgba(0, 0, 0, 0.55);
    overflow: hidden;
}

.promo-modal__close {
    position: absolute;
    top: 16px;
    right: 16px;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: var(--panel);
    border: 1px solid var(--line);
    color: var(--mut);
    font-size: 16px;
    cursor: pointer;
    transition: 0.2s;
    z-index: 3;
}

.promo-modal__close:hover {
    background: var(--rose);
    color: #fff;
    border-color: var(--rose);
}

.promo-modal__head {
    padding: 28px 32px 20px;
    border-bottom: 1px solid var(--line);
}

.promo-modal__kicker {
    color: var(--magenta, #f050e0);
    font-size: 11px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    font-weight: 700;
    margin-bottom: 8px;
}

.promo-modal__title {
    font-family: var(--disp);
    font-size: clamp(20px, 2.4vw, 26px);
    font-weight: 800;
    margin: 0 0 6px;
    background: linear-gradient(90deg, #fff, var(--magenta, #f050e0));
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

.promo-modal__sub {
    color: var(--mut);
    font-size: 13px;
    margin: 0;
}

/* ===== ТАРИФЫ ===== */
.promo-tariffs {
    flex: 1;
    overflow-y: auto;
    padding: 24px 32px;
    display: grid;
    gap: 12px;
    scrollbar-width: thin;
}

.promo-tariffs::-webkit-scrollbar {
    width: 8px;
}

.promo-tariffs::-webkit-scrollbar-thumb {
    background: #232a3d;
    border-radius: 8px;
}

.promo-tariff {
    position: relative;
    display: flex;
    gap: 16px;
    padding: 18px 20px;
    background: var(--panel);
    border: 2px solid var(--line);
    border-radius: 16px;
    cursor: pointer;
    transition: 0.25s;
}

.promo-tariff:hover {
    border-color: rgba(240, 80, 224, 0.4);
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(240, 80, 224, 0.12);
}

.promo-tariff--selected {
    border-color: var(--magenta, #f050e0);
    background: linear-gradient(135deg, rgba(240, 80, 224, 0.08), rgba(139, 92, 246, 0.08));
    box-shadow: 0 0 0 3px rgba(240, 80, 224, 0.15);
}

.promo-tariff__icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    font-size: 26px;
    background: linear-gradient(135deg, rgba(240, 80, 224, 0.15), rgba(139, 92, 246, 0.15));
    border: 1px solid rgba(240, 80, 224, 0.25);
    flex-shrink: 0;
}

.promo-tariff__body {
    flex: 1;
    min-width: 0;
}

.promo-tariff__body h3 {
    font-family: var(--disp);
    font-size: 16px;
    font-weight: 700;
    margin: 0 0 4px;
    color: var(--txt);
}

.promo-tariff__body p {
    color: var(--mut);
    font-size: 13px;
    line-height: 1.4;
    margin: 0 0 8px;
}

.promo-tariff__price {
    font-family: var(--disp);
    font-size: 20px;
    font-weight: 800;
    color: var(--magenta, #f050e0);
}

.promo-tariff__check {
    position: absolute;
    top: 14px;
    right: 14px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: var(--magenta, #f050e0);
    color: #fff;
    display: grid;
    place-items: center;
    font-size: 16px;
    font-weight: 900;
    box-shadow: 0 0 14px -3px rgba(240, 80, 224, 0.7);
}

/* ===== ПОЖЕЛАНИЯ ===== */
.promo-requirements {
    padding: 0 32px 20px;
}

/* ===== ФУТЕР ===== */
.promo-modal__foot {
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 18px 32px;
    border-top: 1px solid var(--line);
    background: rgba(5, 7, 13, 0.4);
}

.promo-summary {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.promo-summary span {
    font-size: 11px;
    color: var(--mut);
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.promo-summary b {
    font-family: var(--disp);
    font-size: 24px;
    font-weight: 800;
    color: var(--magenta, #f050e0);
}

.promo-modal__foot .btn {
    min-width: 180px;
}

.promo-modal__foot .btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

@media (max-width: 640px) {
    .promo-modal__head {
        padding: 22px 20px 16px;
    }
    .promo-tariffs {
        padding: 18px 20px;
    }
    .promo-requirements {
        padding: 0 20px 16px;
    }
    .promo-modal__foot {
        padding: 16px 20px;
        flex-direction: column;
        gap: 12px;
        align-items: stretch;
    }
    .promo-modal__foot .btn {
        width: 100%;
        min-width: 0;
    }
}
</style>
