<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useAuth } from '@/composables/useAuth'
import { useToast } from '@/composables/useToast'
import { useFlashStore } from '@/stores/flash'

const auth = useAuth()
const flash = useFlashStore()
const page = usePage()
const csrf = page.props.csrfToken
const { success, error } = useToast()

const user = computed(() => page.props.auth?.user || {})

// Определяем роль пользователя
const isBusiness = computed(() => user.value.roles?.includes('business'))
const isTech = computed(() => user.value.roles?.includes('tech_partner'))
const isAd = computed(() => user.value.roles?.includes('ad_partner'))
const isTourist = computed(() => !isBusiness.value && !isTech.value && !isAd.value)

watch(() => page.props.flash?.success, m => m && success(m), { immediate: true })
watch(() => page.props.flash?.error, m => m && error(m), { immediate: true })

const displayName = computed(() => user.value?.name ?? auth.user?.name ?? '')

const roleLabel = computed(() => {
    if (isBusiness.value) return 'Владелец бизнеса'
    if (isTech.value) return 'Технический партнёр'
    if (isAd.value) return 'Рекламный партнёр'
    return 'Турист'
})

// Меню для туриста
const touristMenu = computed(() => [
    { href: '/account', icon: '📊', label: 'Дашборд', exact: true },
    { href: '/account/favorites', icon: '❤️', label: 'Избранные места' },
    { href: '/account/reviews', icon: '⭐', label: 'Мои отзывы' },
    { href: '/account/profile', icon: '⚙️', label: 'Профиль' },
])

// Меню для владельца бизнеса
const businessMenu = computed(() => [
    { href: '/account', icon: '📊', label: 'Дашборд', exact: true },
    { href: '/account/places', icon: '🏢', label: 'Мои заведения' },
    { href: '/account/news', icon: '📰', label: 'Новости' },
    { href: '/account/stories', icon: '🎞️', label: 'Мои истории' },
    { href: '/account/profile', icon: '⚙️', label: 'Профиль' },
])

// Меню для партнеров
const partnerMenu = computed(() => [
    { href: '/account', icon: '📊', label: 'Дашборд', exact: true },
    ...(isTech.value ? [{ href: '/account/development', icon: '💻', label: 'Заявки на разработку' }] : []),
    ...(isAd.value ? [{ href: '/account/promotions', icon: '📣', label: 'Заявки на продвижение' }] : []),
    { href: '/account/partner/wallet', icon: '💼', label: 'Кошелек' },
    { href: '/account/profile', icon: '⚙️', label: 'Профиль' },
])

const menu = computed(() => {
    if (isBusiness.value) return businessMenu.value
    if (isTech.value || isAd.value) return partnerMenu.value
    return touristMenu.value
})

const isOn = (item) => item.exact
    ? page.url === item.href
    : page.url.startsWith(item.href)
</script>

<template>
    <div class="admin">
        <aside class="side">
            <Link href="/account" class="side__logo">
                ВИЗИТ<em>ДОНЕЦК · КАБИНЕТ</em>
            </Link>

            <div class="side__role">
                <span v-if="isBusiness" class="side__role-chip">🏢 Бизнес</span>
                <span v-else-if="isTech" class="side__role-chip side__role-chip--tech">💻 Tech</span>
                <span v-else-if="isAd" class="side__role-chip side__role-chip--ad">📣 Ad</span>
                <span v-else class="side__role-chip">🧳 Турист</span>
            </div>

            <nav class="side__nav">
                <Link
                    v-for="item in menu"
                    :key="item.href"
                    :href="item.href"
                    class="side__link"
                    :class="{ on: isOn(item) }"
                >
                    <span class="side__ico">{{ item.icon }}</span>
                    <span class="side__text">{{ item.label }}</span>
                </Link>

                <div class="side__sep"></div>
                <a href="/" class="side__link">
                    <span class="side__ico">🌆</span>
                    <span class="side__text">На сайт</span>
                </a>
            </nav>

            <div class="side__foot">
                <div class="side__user">
                    <b>{{ displayName }}</b>
                    <span>{{ roleLabel }}</span>
                </div>
                <form method="POST" action="/logout" style="margin:0">
                    <input type="hidden" name="_token" :value="csrf">
                    <button type="submit" class="side__out">Выйти</button>
                </form>
            </div>
        </aside>

        <main class="main">
            <slot />
        </main>

        <div class="toasts">
            <TransitionGroup name="tslide">
                <div
                    v-for="t in flash.toasts"
                    :key="t.id"
                    class="toast"
                    :class="`toast--${t.type}`"
                    @click="flash.dismiss(t.id)"
                >{{ t.message }}</div>
            </TransitionGroup>
        </div>
    </div>
</template>


<style scoped>
/* Ваши стили остаются без изменений, они отличные */
.side__role{
    display:flex;flex-wrap:wrap;gap:6px;
    padding:0 18px 16px;
}
.side__role-chip{
    font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;
    padding:4px 10px;border-radius:99px;
    background:var(--panel);border:1px solid var(--line);color:var(--mut);
}
.side__role-chip--tech{
    color:#8b5cf6;border-color:rgba(139,92,246,.35);
    background:rgba(139,92,246,.12);
}
.side__role-chip--ad{
    color:#f050e0;border-color:rgba(240,80,224,.35);
    background:rgba(240,80,224,.12);
}

.side__group{
    padding:18px 18px 8px;
    font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;
    color:var(--mut);opacity:.65;
}
.side__sep{height:1px;background:var(--line);margin:14px 18px}

.side__link{
    display:flex;align-items:center;gap:10px;
    position:relative;
}
.side__ico{font-size:16px;flex-shrink:0}
.side__text{flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.side__badge{
    min-width:22px;height:22px;padding:0 6px;
    display:grid;place-items:center;
    border-radius:99px;font-size:11px;font-weight:800;
    background:linear-gradient(135deg,#22d3ee,#8b5cf6);
    color:#fff;
}

.side__link--tech.on{
    border-left-color:#8b5cf6;
    background:linear-gradient(90deg,rgba(139,92,246,.16),transparent);
}
.side__link--ad.on{
    border-left-color:#f050e0;
    background:linear-gradient(90deg,rgba(240,80,224,.16),transparent);
}
.side__link--tech.on .side__badge{background:linear-gradient(135deg,#8b5cf6,#f050e0)}
.side__link--ad.on  .side__badge{background:linear-gradient(135deg,#f050e0,#ff5c7a)}

.side__user span{display:block;font-size:11px;color:var(--mut);margin-top:2px}
</style>
