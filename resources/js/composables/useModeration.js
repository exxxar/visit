import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import { useToast } from './useToast'

export function useModeration() {
    const { error } = useToast()

    // модалка «решение с комментарием» (отклонить / вернуть)
    const modal      = ref(null) // { type, id, title, action }
    const comment    = ref('')
    const processing = ref(false)

    function act(type, id, action, text = '') {
        processing.value = true
        router.post(`/admin/moderation/${type}/${id}`, { action, comment: text }, {
            preserveScroll: true,
            onError:  () => error('Не удалось сохранить решение'),
            onFinish: () => { processing.value = false; modal.value = null; comment.value = '' },
        })
    }

    const approve = (type, id) => act(type, id, 'approved')

    function openModal(item, type, action) {
        modal.value = { type, id: item.id, title: item.title ?? item.name ?? item.org_name, action }
    }

    const submitModal = () => modal.value && act(modal.value.type, modal.value.id, modal.value.action, comment.value)
    const closeModal  = () => { modal.value = null; comment.value = '' }

    // reactive() разворачивает вложенные ref'ы для шаблонов
    return reactive({ modal, comment, processing, act, approve, openModal, submitModal, closeModal })
}
