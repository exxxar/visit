/**
 * v-lazy — ленивая загрузка изображений с fallback на дефолтную картинку.
 */

/* Твоя заглушка */
export const DEFAULT_IMG = '/assets/placeholder.jpg'

/* Прозрачный пиксель-плейсхолдер, пока картинка не в зоне видимости */
const BLANK = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'

function setErrorFallback(el) {
    el.onerror = null
    el.src = DEFAULT_IMG
    el.classList.add('lazy-img--error')
}

function loadReal(el, src) {
    el.classList.add('lazy-img--loading')

    const img = new Image()
    img.onload = () => {
        el.src = src
        el.classList.remove('lazy-img--loading')
        el.classList.add('lazy-img--loaded')
    }
    img.onerror = () => {
        setErrorFallback(el)
        el.classList.remove('lazy-img--loading')
        el.classList.add('lazy-img--loaded')
    }
    img.src = src
}

export const vLazy = {
    mounted(el, binding) {
        const src = typeof binding.value === 'string' ? binding.value.trim() : ''

        el.classList.add('lazy-img')
        el.setAttribute('loading', 'lazy')

        // нет src — сразу твоя заглушка
        if (!src) {
            el.src = DEFAULT_IMG
            el.classList.add('lazy-img--loaded')
            return
        }

        if ('IntersectionObserver' in window) {
            el.src = BLANK

            const observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            loadReal(el, src)
                            observer.unobserve(el)
                        }
                    })
                },
                { rootMargin: '200px 0px' }
            )
            observer.observe(el)
            el._lazyObserver = observer
        } else {
            loadReal(el, src)
        }
    },

    updated(el, binding) {
        const src = typeof binding.value === 'string' ? binding.value.trim() : ''
        const prev = el._lazySrc

        if (src !== prev) {
            el._lazySrc = src
            if (el._lazyObserver) {
                el._lazyObserver.unobserve(el)
                el._lazyObserver = null
            }
            if (!src) {
                el.src = DEFAULT_IMG
            } else {
                loadReal(el, src)
            }
        }
    },

    unmounted(el) {
        if (el._lazyObserver) {
            el._lazyObserver.disconnect()
            el._lazyObserver = null
        }
    },
}
