import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { createPinia } from 'pinia'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AccountLayout from '@/Layouts/AccountLayout.vue'
import { vLazy } from './directives/lazy'

createInertiaApp({
    title: (t) => `${t} — ВИЗИТ ДОНЕЦК`,

    resolve: async (name) => {
        console.log('Inertia пытается загрузить страницу:', name) // <-- ПОМОЖЕТ НАЙТИ ПРОБЛЕМУ

        const pages = import.meta.glob('./Pages/**/*.vue')
        const targetPath = `./Pages/${name}.vue`
        const pageModule = pages[targetPath]

        // ЗАЩИТА ОТ ОШИБКИ: проверяем, существует ли файл
        if (!pageModule) {
            console.error(`❌ ОШИБКА: Файл не найден по пути ${targetPath}`)
            throw new Error(`Страница ${name} не найдена в папке Pages`)
        }

        const page = (await pageModule()).default

        // layout по префиксу страницы, если свой не задан
        if (!page.layout) {
            if (name.startsWith('Admin/'))         page.layout = AdminLayout
            else if (name.startsWith('Account/'))  page.layout = AccountLayout
        }

        return page
    },

    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(createPinia())
            .directive('lazy', vLazy)
            .mount(el)
    },

    progress: { color: '#22d3ee', showSpinner: true },
})
