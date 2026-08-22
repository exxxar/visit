<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
    place: Object,
    externalId: String,
    externalSource: String,
})

const menu = ref([])
const loading = ref(false)
const error = ref(null)

const loadMenu = async () => {
    if (!props.externalId || props.externalSource !== 'mypwa.ru') return

    loading.value = true
    error.value = null

    try {
        const response = await fetch(`/api/v1/places/${props.place.id}/menu`)
        const data = await response.json()

        if (response.ok) {
            menu.value = data.data
        } else {
            error.value = 'Не удалось загрузить меню'
        }
    } catch (e) {
        error.value = 'Ошибка загрузки меню'
    } finally {
        loading.value = false
    }
}

onMounted(loadMenu)
</script>

<template>
    <div v-if="externalId && externalSource === 'mypwa.ru'" class="place-menu">
        <h3 class="place-menu__title">
            <span>📋 Меню и прайс-лист</span>
            <span v-if="menu.length" class="place-menu__count">{{ menu.length }} позиций</span>
        </h3>

        <div v-if="loading" class="place-menu__loading">
            <div class="spinner"></div>
            <span>Загружаем меню…</span>
        </div>

        <div v-else-if="error" class="place-menu__error">
            {{ error }}
        </div>

        <div v-else-if="menu.length" class="place-menu__grid">
            <div v-for="item in menu" :key="item.id" class="menu-item">
                <div v-if="item.image" class="menu-item__img">
                    <img :src="item.image" :alt="item.name" loading="lazy">
                </div>
                <div class="menu-item__body">
                    <h4 class="menu-item__name">{{ item.name }}</h4>
                    <p v-if="item.description" class="menu-item__desc">{{ item.description }}</p>
                    <div class="menu-item__price">
                        <span class="price">{{ item.price }} ₽</span>
                        <span v-if="item.weight" class="weight">{{ item.weight }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="place-menu__empty">
            Меню пока не добавлено
        </div>
    </div>
</template>

<style scoped>
.place-menu {
    margin-top: 40px;
    padding-top: 40px;
    border-top: 1px solid var(--line);
}

.place-menu__title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-family: var(--disp);
    font-size: 22px;
    margin: 0 0 20px;
}

.place-menu__count {
    font-size: 14px;
    color: var(--mut);
    font-weight: 500;
}

.place-menu__loading,
.place-menu__error,
.place-menu__empty {
    text-align: center;
    padding: 40px 20px;
    color: var(--mut);
}

.place-menu__loading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
}

.spinner {
    width: 24px;
    height: 24px;
    border: 3px solid var(--line);
    border-top-color: var(--cyan);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.place-menu__grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
}

.menu-item {
    display: flex;
    gap: 14px;
    padding: 14px;
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: 14px;
    transition: border-color .2s;
}

.menu-item:hover {
    border-color: var(--cyan);
}

.menu-item__img {
    width: 80px;
    height: 80px;
    flex-shrink: 0;
    border-radius: 10px;
    overflow: hidden;
    background: var(--bg);
}

.menu-item__img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.menu-item__body {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.menu-item__name {
    font-size: 15px;
    font-weight: 700;
    margin: 0;
    color: var(--txt);
}

.menu-item__desc {
    font-size: 13px;
    color: var(--mut);
    margin: 0;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.menu-item__price {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: auto;
    padding-top: 6px;
}

.price {
    font-size: 16px;
    font-weight: 800;
    color: var(--cyan);
}

.weight {
    font-size: 12px;
    color: var(--mut);
    padding: 2px 8px;
    background: rgba(34, 211, 238, .1);
    border-radius: 6px;
}

@media (max-width: 640px) {
    .place-menu__grid {
        grid-template-columns: 1fr;
    }

    .menu-item {
        flex-direction: column;
    }

    .menu-item__img {
        width: 100%;
        height: 160px;
    }
}
</style>
