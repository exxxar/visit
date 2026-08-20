import { useFlashStore } from '@/stores/flash'

export function useToast() {
    const flash = useFlashStore()
    return {
        toast:   flash.push,
        success: flash.success,
        error:   flash.error,
    }
}
