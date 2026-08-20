import { defineStore } from 'pinia'
import axios from 'axios'

export const useDictionaryStore = defineStore('dictionary', {
    state: () => ({
        categories: null,
        districts: null,
        loading: false,
    }),

    getters: {
        ready: (s) => !!s.categories && !!s.districts,
        districtName: (s) => (id) => s.districts?.find((d) => d.id === id)?.name ?? '—',
    },

    actions: {
        async load() {
            if (this.ready || this.loading) return
            this.loading = true
            try {
                const [c, d] = await Promise.all([
                    axios.get('/api/v1/categories'),
                    axios.get('/api/v1/districts'),
                ])
                this.categories = c.data
                this.districts = d.data
            } finally {
                this.loading = false
            }
        },
    },
})
