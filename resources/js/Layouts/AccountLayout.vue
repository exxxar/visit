<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { useAuth } from '@/composables/useAuth'
import { useToast } from '@/composables/useToast'
import { useFlashStore } from '@/stores/flash'
import { watch } from 'vue'

const auth  = useAuth()
const flash = useFlashStore()
const page  = usePage()
const csrf = usePage().props.csrfToken
const { success, error } = useToast()

watch(() => page.props.flash?.success, (m) => m && success(m), { immediate: true })
watch(() => page.props.flash?.error,   (m) => m && error(m),   { immediate: true })

const nav = [
    { href: '/account', icon: '📊', label: 'Дашборд', exact: true },
    { href: '/account/news', icon: '📰', label: 'Новости' },
    { href: '/account/stories', icon: '🎞️', label: 'Мои истории' },
]
const isOn = (i) => i.exact ? page.url === i.href : page.url.startsWith(i.href)
</script>

<template>
    <div class="admin">
        <aside class="side">
            <Link href="/account" class="side__logo">ВИЗИТ<em>ДОНЕЦК · КАБИНЕТ</em></Link>
            <nav class="side__nav">
                <Link v-for="i in nav" :key="i.href" :href="i.href" class="side__link" :class="{ on: isOn(i) }">
                    <span>{{ i.icon }}</span>{{ i.label }}
                </Link>
                <a href="/" class="side__link"><span>🌆</span>На сайт</a>
            </nav>
            <div class="side__foot">
                <div class="side__user"><b>{{ auth.user?.name }}</b><span>владелец заведения</span></div>
                <form method="POST" action="/logout" style="margin:0">
                    <input type="hidden" name="_token" :value="csrf">
                    <button type="submit" class="side__out">Выйти</button>
                </form>
            </div>
        </aside>
        <main class="main"><slot /></main>
        <div class="toasts">
            <TransitionGroup name="tslide">
                <div v-for="t in flash.toasts" :key="t.id" class="toast" :class="`toast--${t.type}`" @click="flash.dismiss(t.id)">{{ t.message }}</div>
            </TransitionGroup>
        </div>
    </div>
</template>
