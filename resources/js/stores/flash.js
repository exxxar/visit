import { defineStore } from 'pinia'

let seq = 0

export const useFlashStore = defineStore('flash', {
    state: () => ({ toasts: [] }),

    actions: {
        push(message, type = 'success', timeout = 3500) {
            const t = { id: ++seq, message, type }
            this.toasts.push(t)
            setTimeout(() => this.dismiss(t.id), timeout)
        },
        success(m) { this.push(m, 'success') },
        error(m)   { this.push(m, 'error') },
        dismiss(id){ this.toasts = this.toasts.filter((t) => t.id !== id) },
    },
})
