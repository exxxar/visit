import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function usePartnerRole() {
    const page = usePage()

    const user = computed(() => page.props.auth?.user ?? null)

    const roles = computed(() =>
        (user.value?.roles ?? [])
            .map(r => (typeof r === 'string' ? r : r?.name))
            .filter(Boolean)
    )

    const meta   = computed(() => user.value?.meta ?? {})
    const shared = computed(() => page.props.partner ?? {})

    const isAdmin = computed(() =>
        roles.value.includes('admin') || !!user.value?.is_admin
    )

    // три источника истины: share → Spatie roles → meta из реферальной регистрации
    const isTech = computed(() =>
        isAdmin.value ||
        shared.value.type === 'tech' ||
        roles.value.includes('tech_partner') ||
        meta.value.partner_type === 'tech'
    )

    const isAd = computed(() =>
        isAdmin.value ||
        shared.value.type === 'ad' ||
        roles.value.includes('ad_partner') ||
        meta.value.partner_type === 'ad'
    )

    // если ролей вообще нет — считаем владельцем (совместимость со старыми аккаунтами)
    const isOwner = computed(() =>
        roles.value.includes('owner') ||
        (user.value?.places_count ?? 0) > 0 ||
        (!isTech.value && !isAd.value)
    )

    const roleLabel = computed(() => {
        if (isTech.value && isAd.value) return 'партнёр платформы'
        if (isTech.value) return 'технический партнёр'
        if (isAd.value)  return 'рекламный партнёр'
        return 'владелец заведения'
    })

    const canAccess = (href) => {
        if (href.startsWith('/account/development')) return isTech.value
        if (href.startsWith('/account/promotions'))  return isAd.value
        if (['/account/places', '/account/news', '/account/stories']
            .some(p => href.startsWith(p))) return isOwner.value
        return true
    }

    return { user, roles, isAdmin, isTech, isAd, isOwner, roleLabel, canAccess }
}
