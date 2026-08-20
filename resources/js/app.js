import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { createPinia } from 'pinia'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AccountLayout from '@/Layouts/AccountLayout.vue'

createInertiaApp({
    title: (t) => `${t} — ВИЗИТ ДОНЕЦК`,

    resolve: async (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue')
        const page  = (await pages[`./Pages/${name}.vue`]()).default

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
            .mount(el)
    },

    progress: { color: '#22d3ee', showSpinner: true },
})
