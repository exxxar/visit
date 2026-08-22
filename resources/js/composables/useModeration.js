import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import { useToast } from './useToast'

export function useModeration() {
    const { error, success } = useToast()

    const modal      = ref(null)
    const comment    = ref('')
    const processing = ref(false)

    function act(type, id, action, text = '') {
        console.log('[MODERATION] act:', { type, id, action, text })
        processing.value = true

        router.post(`/admin/moderation/${type}/${id}`, {
            action,      // 'approve' | 'reject' | 'return'
            comment: text,
        }, {
            preserveScroll: true,
            onSuccess: (page) => {
                console.log('[MODERATION] success:', page.props?.flash)
                const msg = page.props?.flash?.success ?? 'Готово'
                success(msg)
            },
            onError: (errors) => {
                console.error('[MODERATION] validation error:', errors)
                const msg = Object.values(errors).flat().join(', ')
                error(msg || 'Не удалось сохранить решение')
            },
            onFinish: () => {
                processing.value = false
                modal.value = null
                comment.value = ''
            },
        })
    }

    const approve = (type, id) => act(type, id, 'approve')

    function openModal(item, type, action) {
        modal.value = {
            type,
            id: item.id,
            title: item.title ?? item.name ?? item.org_name,
            action,  // 'reject' или 'return'
        }
    }

    const submitModal = () => {
        if (!modal.value) return
        act(
            modal.value.type,
            modal.value.id,
            modal.value.action,
            comment.value
        )
    }

    const closeModal = () => {
        modal.value = null
        comment.value = ''
    }

    return reactive({
        modal, comment, processing,
        act, approve, openModal, submitModal, closeModal,
    })
}
