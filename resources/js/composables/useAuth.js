import { computed, reactive } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function useAuth() {
    const page = usePage()

    const user = computed(() => page.props.auth?.user)

    const roles = computed(() => {
        const r = user.value?.roles
        return Array.isArray(r) ? r : Object.values(r ?? {})
    })

    const permissions = computed(() => {
        const p = user.value?.permissions
        return Array.isArray(p) ? p : Object.values(p ?? {})
    })

    const isAdmin = computed(() => roles.value.includes('admin'))

    const can        = (perm) => isAdmin.value || permissions.value.includes(perm)
    const hasRole    = (r)    => roles.value.includes(r)
    const hasAnyRole = (rs)   => rs.some((r) => roles.value.includes(r))

    // reactive() разворачивает вложенные ref'ы для шаблонов
    return reactive({ user, roles, permissions, isAdmin, can, hasRole, hasAnyRole })
}
