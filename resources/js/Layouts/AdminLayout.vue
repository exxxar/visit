<script setup>
import { computed, ref, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => page.props.auth?.user || {})

// Хелперы для проверки прав
const auth = {
    isAdmin: computed(() => user.value.roles?.includes('admin') || user.value.is_admin),
    hasRole: (role) => user.value.roles?.includes(role),
    hasAnyRole: (roles) => roles.some(r => user.value.roles?.includes(r)),
    can: (permission) => user.value.permissions?.includes(permission),
}

// 1. Исходная структура с группами
const rawMenu = [
    { href: '/admin', icon: '📊', label: 'Дашборд', exact: true, show: true },
    { href: '/admin/moderation', icon: '🛡', label: 'Модерация', show: auth.hasAnyRole(['admin', 'moderator']) },
    { href: '/admin/feedback', icon: '✉', label: 'Обратная связь', show: auth.can('manage settings') || auth.isAdmin },

    {
        label: 'Контент',
        children: [
            { href: '/admin/places', icon: '📍', label: 'Заведения', show: auth.isAdmin },
            { href: '/admin/reviews/moderation', icon: '⭐', label: 'Модерация отзывов', show: auth.isAdmin },
            { href: '/admin/map/layers', icon: '🗺️', label: 'Редактор карты', show: auth.isAdmin }, // <-- ДОБАВЛЕНО
            { href: '/admin/map/editor', icon: '🗺️', label: 'Визуальный редактор', show: auth.isAdmin },
            { href: '/admin/events', icon: '🎭', label: 'Афиша', show: auth.isAdmin },
            { href: '/admin/news', icon: '📰', label: 'Новости', show: auth.isAdmin },
            { href: '/admin/stories', icon: '🎞️', label: 'Истории', show: auth.isAdmin },
            { href: '/admin/posts', icon: '📖', label: 'Журнал', show: auth.hasAnyRole(['admin', 'editor']) },
        ]
    },
    {
        label: 'Партнёрская система',
        children: [
            { href: '/admin/referrals', icon: '🤝', label: 'Рефералы', show: auth.isAdmin },
            { href: '/admin/partner/withdrawals', icon: '💸', label: 'Выводы средств', show: auth.isAdmin },
            { href: '/admin/partner/transactions', icon: '📊', label: 'Транзакции', show: auth.isAdmin },
            { href: '/admin/partner/requests', icon: '📦', label: 'Заявки партнеров', show: auth.isAdmin },
            { href: '/admin/partner/network', icon: '🌐', label: 'Сетевая структура', show: auth.isAdmin },
        ]
    },
    {
        label: 'Системное',
        children: [
            { href: '/admin/outbound', icon: '🔗', label: 'Внешние ссылки', show: auth.isAdmin },
            { href: '/admin/placements', icon: '✨', label: 'Реклама', show: auth.can('manage ads') || auth.isAdmin },
            { href: '/admin/leads', icon: '📥', label: 'Лиды', show: auth.isAdmin },
            { href: '/admin/users', icon: '👥', label: 'Пользователи', show: auth.can('manage users') || auth.isAdmin },
            { href: '/admin/roles', icon: '🔐', label: 'Роли', show: auth.can('assign roles') || auth.isAdmin },
            { href: '/admin/settings', icon: '⚙️', label: 'Настройки', show: auth.can('manage settings') || auth.isAdmin },
            { href: '/admin/audit', icon: '🛡️', label: 'Журнал аудита', show: auth.isAdmin },
        ]
    }
]

// 2. Фильтрация: убираем скрытые пункты и пустые группы
const menu = computed(() => {
    return rawMenu.map(item => {
        if (item.children) {
            const visibleChildren = item.children.filter(c => c.show)
            return visibleChildren.length > 0 ? { ...item, children: visibleChildren } : null
        }
        return item.show ? item : null
    }).filter(Boolean)
})

// 3. Логика аккордеона
const openGroups = ref(new Set())

// Автоматически раскрываем группу, если текущий URL принадлежит ей
watch(() => page.url, (newUrl) => {
    menu.value.forEach((item, index) => {
        if (item.children) {
            const isActive = item.children.some(child =>
                child.exact ? newUrl === child.href : newUrl.startsWith(child.href)
            )
            if (isActive) {
                openGroups.value.add(index)
            }
        }
    })
}, { immediate: true })

const toggleGroup = (index) => {
    if (openGroups.value.has(index)) {
        openGroups.value.delete(index)
    } else {
        openGroups.value.add(index)
    }
}

// Проверка активного состояния (и для обычных ссылок, и для дочерних)
const isOn = (item) => item.exact
    ? page.url === item.href
    : page.url.startsWith(item.href)
</script>
<template>
    <div class="admin-layout">
        <aside class="side">
            <Link href="/admin" class="side__logo">
                КОД<em>НОВОРОСИИ · ADMIN</em>
            </Link>

            <nav class="side__nav">
                <template v-for="(item, index) in menu" :key="item.href || item.label">

                    <!-- Обычная ссылка (без детей) -->
                    <Link
                        v-if="!item.children"
                        :href="item.href"
                        class="side__link"
                        :class="{ on: isOn(item) }"
                    >
                        <span class="side__icon">{{ item.icon }}</span>
                        <span class="side__text">{{ item.label }}</span>
                    </Link>

                    <!-- Группа-аккордеон -->
                    <div v-else class="side__group">
                        <button
                            class="side__group-header"
                            @click="toggleGroup(index)"
                            :class="{ 'is-open': openGroups.has(index) }"
                        >
                            <span class="side__group-title">{{ item.label }}</span>
                            <svg class="side__group-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>

                        <div class="side__group-children" :class="{ 'is-open': openGroups.has(index) }">
                            <Link
                                v-for="child in item.children"
                                :key="child.href"
                                :href="child.href"
                                class="side__link side__link--child"
                                :class="{ on: isOn(child) }"
                            >
                                <span class="side__icon">{{ child.icon }}</span>
                                <span class="side__text">{{ child.label }}</span>
                            </Link>
                        </div>
                    </div>

                </template>
            </nav>

            <div class="side__foot">
                <div class="side__user">
                    <b>{{ user.name || 'Администратор' }}</b>
                    <span>{{ user.email }}</span>
                </div>
                <Link href="/" class="side__out">🌆 На сайт</Link>
            </div>
        </aside>

        <main class="main">
            <slot />
        </main>
    </div>
</template>

<style scoped>
.admin-layout { display: flex; min-height: 100vh; background: var(--bg); }

.side {
    width: 260px; background: var(--bg2); border-right: 1px solid var(--line);
    display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 40;
}

.side__logo {
    padding: 24px; font-family: var(--disp); font-weight: 800; font-size: 16px;
    letter-spacing: .06em; color: var(--txt); border-bottom: 1px solid var(--line);
}
.side__logo em { font-style: normal; font-size: 10px; letter-spacing: .3em; color: var(--cyan); display: block; margin-top: 4px; }

.side__nav { flex: 1; overflow-y: auto; padding: 16px 12px; }

/* Обычная ссылка */
.side__link {
    display: flex; align-items: center; gap: 12px; padding: 12px 16px;
    border-radius: 12px; color: var(--mut); font-size: 14px; font-weight: 600;
    transition: .2s; margin-bottom: 4px; text-decoration: none; position: relative;
}
.side__link:hover { background: var(--panel); color: var(--txt); }
.side__link.on {
    background: linear-gradient(90deg, rgba(34,211,238,.15), transparent);
    color: var(--cyan);
    border-left: 3px solid var(--cyan);
}
.side__icon { font-size: 18px; width: 24px; text-align: center; flex-shrink: 0; }

/* ===== АККОРДЕОН ===== */
.side__group { margin-bottom: 8px; }

.side__group-header {
    width: 100%; display: flex; align-items: center; justify-content: space-between;
    padding: 10px 16px; background: none; border: none; border-radius: 10px;
    color: var(--mut); font-size: 11px; font-weight: 800; letter-spacing: .12em;
    text-transform: uppercase; cursor: pointer; transition: .2s;
}
.side__group-header:hover { color: var(--txt); background: var(--panel); }
.side__group-header.is-open { color: var(--cyan); }

.side__group-chevron { transition: transform .3s cubic-bezier(.4, 0, .2, 1); }
.side__group-header.is-open .side__group-chevron { transform: rotate(180deg); }

.side__group-children {
    max-height: 0; opacity: 0; overflow: hidden;
    transition: max-height .35s cubic-bezier(.4, 0, .2, 1), opacity .25s ease;
}
.side__group-children.is-open {
    max-height: 600px; /* Достаточно большое значение для анимации */
    opacity: 1;
}

/* Дочерняя ссылка */
.side__link--child {
    padding-left: 52px; /* Отступ для иерархии */
    font-size: 13.5px;
    margin-bottom: 2px;
}
/* Точка-маркер для дочерних элементов */
.side__link--child::before {
    content: ''; position: absolute; left: 28px; top: 50%;
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--line); transform: translateY(-50%);
    transition: background .2s, box-shadow .2s;
}
.side__link--child.on::before {
    background: var(--cyan);
    box-shadow: 0 0 8px rgba(34, 211, 238, .6);
}

/* ===== ФУТЕР САЙДБАРА ===== */
.side__foot { padding: 20px; border-top: 1px solid var(--line); }
.side__user b { display: block; font-size: 14px; color: var(--txt); }
.side__user span { font-size: 12px; color: var(--mut); }

.side__out {
    display: block; text-align: center; margin-top: 16px; padding: 10px;
    border-radius: 10px; background: var(--panel); border: 1px solid var(--line);
    color: var(--txt); font-size: 13px; font-weight: 600; text-decoration: none; transition: .2s;
}
.side__out:hover { border-color: var(--cyan); color: var(--cyan); }

.main { flex: 1; margin-left: 260px; padding: 32px; }

/* Общие стили для страниц админки (оставлены без изменений) */
.pg-head { display: flex; align-items: center; margin-bottom: 24px; }
.pg-head h1 { font-family: var(--disp); font-size: 24px; font-weight: 800; margin: 0; }
.vcard { background: var(--panel); border: 1px solid var(--line); border-radius: 16px; padding: 20px; }
.vtable { width: 100%; border-collapse: collapse; font-size: 14px; }
.vtable th { text-align: left; padding: 14px 16px; color: var(--mut); font-size: 12px; text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid var(--line); }
.vtable td { padding: 14px 16px; border-bottom: 1px solid var(--line); color: var(--txt); }
.vtable tr:last-child td { border-bottom: none; }
.vtable tr:hover td { background: rgba(255,255,255,.02); }
.inp { width: 100%; padding: 10px 14px; background: var(--bg); border: 1px solid var(--line); border-radius: 10px; color: var(--txt); font-size: 14px; outline: none; }
.inp:focus { border-color: var(--cyan); }
.pager { display: flex; justify-content: center; gap: 6px; margin-top: 24px; }
.pager a, .pager span { padding: 8px 14px; border-radius: 8px; background: var(--panel); border: 1px solid var(--line); color: var(--mut); text-decoration: none; font-size: 13px; }
.pager a.on { background: var(--cyan); color: #000; border-color: var(--cyan); font-weight: 700; }
.pager a.off { opacity: .4; pointer-events: none; }
</style>
