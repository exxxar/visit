/**
 * v-lazy — ленивая загрузка изображений с fallback на дефолтную картинку.
 *
 * Использование:
 *   <img v-lazy="url" alt="...">
 *   <img v-lazy alt="...">            ← сразу дефолтная
 *   <img v-lazy="url" class="my">     ← любые классы работают
 */

/* Дефолтная заглушка: SVG в стиле проекта (тёмный градиент + иконка) */
const DEFAULT_SVG = `
<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300">
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="#151b2b"/>
      <stop offset="1" stop-color="#1e2536"/>
    </linearGradient>
  </defs>
  <rect width="400" height="300" fill="url(#g)"/>
  <g fill="none" stroke="#3a4258" stroke-width="7" stroke-linecap="round" stroke-linejoin="round">
    <rect x="140" y="95" width="120" height="110" rx="14"/>
    <circle cx="176" cy="132" r="13"/>
    <path d="M152 188 l34-36 26 26 20-20 36 36"/>
  </g>
</svg>`

export const DEFAULT_IMG = 'data:image/svg+xml,' + encodeURIComponent(DEFAULT_SVG)

/* Прозрачный пиксель-плейсхолдер, пока картинка не в зоне видимости */
const BLANK = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'

function setErrorFallback(el) {
    // защита от бесконечного цикла, если и дефолт сломан
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

        // нет src — сразу дефолт
        if (!src) {
            el.src = DEFAULT_IMG
            el.classList.add('lazy-img--loaded', 'lazy-img--error')
            return
        }

        // ленивая загрузка: ждём, пока элемент попадёт во viewport
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
                { rootMargin: '200px 0px' } // начинаем грузить за 200px до появления
            )
            observer.observe(el)

            // сохраняем observer, чтобы отключить при размонтировании
            el._lazyObserver = observer
        } else {
            // старый браузер — грузим сразу
            loadReal(el, src)
        }
    },

    // если src изменился (например, при обновлении списка) — перезагружаем
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
