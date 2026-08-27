<script setup>
import { computed, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useAuth } from '@/composables/useAuth'
import { useToast } from '@/composables/useToast'
import { useFlashStore } from '@/stores/flash'
import { router } from '@inertiajs/vue3'

const auth  = useAuth()
const flash = useFlashStore()
const page  = usePage()
const csrf = usePage().props.csrfToken
const { success, error } = useToast()

// серверный flash → тосты
watch(() => page.props.flash?.success, (m) => m && success(m), { immediate: true })
watch(() => page.props.flash?.error,   (m) => m && error(m),   { immediate: true })

const nav = computed(() => [
    { href: '/admin',            icon: '📊', label: 'Дашборд',   show: true, exact: true },
    { href: '/admin/moderation', icon: '🛡', label: 'Модерация', show: auth.hasAnyRole(['admin', 'moderator']) },
    { href: '/admin/places',     icon: '📍', label: 'Заведения', show: auth.isAdmin },
    { href: '/admin/events',     icon: '🎭', label: 'Афиша', show: auth.isAdmin },
    { href: '/admin/news',     icon: '📰', label: 'Новости', show: auth.isAdmin },
    { href: '/admin/stories',     icon: '🎞️', label: 'Истории', show: auth.isAdmin },
    { href: '/admin/posts',      icon: '📖', label: 'Журнал',    show: auth.hasAnyRole(['admin', 'editor']) },
    { href: '/admin/placements', icon: '✨', label: 'Реклама',   show: auth.can('manage ads') },
    { href: '/admin/leads',      icon: '📥', label: 'Лиды',      show: auth.isAdmin },
    { href: '/admin/users',      icon: '👥', label: 'Пользователи', show: auth.can('manage users') },
    { href: '/admin/roles',      icon: '🔐', label: 'Роли',      show: auth.can('assign roles') },
    { href: '/admin/settings',   icon: '⚙️', label: 'Настройки', show: auth.can('manage settings') },
    { href: '/admin/feedback',   icon: '✉', label: 'Обратная связь', show: auth.can('manage settings') },
].filter((i) => i.show))

const isExact = (i) => i.exact ? page.url === i.href : page.url.startsWith(i.href)

const logout = () => router.post('/logout')
</script>

<template>
    <div class="admin">
        <aside class="side">
            <Link href="/admin" class="side__logo">ВИЗИТ<em>ДОНЕЦК · АДМИН</em></Link>

            <nav class="side__nav">
                <Link v-for="i in nav" :key="i.href" :href="i.href"
                      class="side__link" :class="{ on: isExact(i) }">
                    <span>{{ i.icon }}</span>{{ i.label }}
                </Link>
            </nav>

            <div class="side__foot">
                <div class="side__user">
                    <b>{{ auth.user?.name }}</b>
                    <span>{{ auth.roles.join(', ') }}</span>
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

        <!-- тосты -->
        <div class="toasts">
            <TransitionGroup name="tslide">
                <div v-for="t in flash.toasts" :key="t.id" class="toast" :class="`toast--${t.type}`" @click="flash.dismiss(t.id)">
                    {{ t.message }}
                </div>
            </TransitionGroup>
        </div>
    </div>
</template>
