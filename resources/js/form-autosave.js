/**
 * FormAutoSave — автосохранение полей формы в localStorage
 *
 * Использование:
 *   new FormAutoSave(formElement, 'ref_client_form');
 *
 * Сохраняет:
 *   - input (text, email, tel, url, number)
 *   - textarea
 *   - select
 *   - checkbox (только с классом .persist)
 *
 * НЕ сохраняет:
 *   - пароли (безопасность)
 *   - файлы (технически невозможно)
 */
class FormAutoSave {
    constructor(form, storageKey, options = {}) {
        this.form = form;
        this.storageKey = storageKey;
        this.excludeSelectors = options.excludeSelectors || [
            '[type="password"]',
            '[type="file"]',
            'input[name="_token"]',
        ];
        this.saveDelay = options.saveDelay || 400; // мс, дебаунс
        this.saveTimer = null;

        this.restore();
        this.bind();
    }

    /* привязка обработчиков */
    bind() {
        this.form.addEventListener('input', (e) => {
            if (this.isExcluded(e.target)) return;
            this.scheduleSave();
        });

        this.form.addEventListener('change', (e) => {
            if (this.isExcluded(e.target)) return;
            this.save(); // select/checkbox — сразу
        });
    }

    isExcluded(el) {
        return this.excludeSelectors.some(sel => el.matches(sel));
    }

    /* сбор данных формы */
    collect() {
        const data = {};
        const elements = this.form.querySelectorAll('input, textarea, select');

        elements.forEach((el, idx) => {
            if (this.isExcluded(el)) return;

            // если нет name — используем id или индекс
            const key = el.name || el.id || `field_${idx}`;
            if (!key) return;

            if (el.type === 'checkbox') {
                // сохраняем только явно помеченные
                if (!el.classList.contains('persist')) return;
                data[key] = el.checked;
            } else if (el.type === 'radio') {
                if (el.checked) data[key] = el.value;
            } else {
                data[key] = el.value;
            }
        });

        return data;
    }

    /* сохранение с дебаунсом */
    scheduleSave() {
        clearTimeout(this.saveTimer);
        this.saveTimer = setTimeout(() => this.save(), this.saveDelay);
    }

    save() {
        try {
            const data = this.collect();
            localStorage.setItem(this.storageKey, JSON.stringify({
                data,
                savedAt: Date.now(),
            }));
        } catch (e) {
            console.warn('[FormAutoSave] Не удалось сохранить:', e);
        }
    }

    /* восстановление из хранилища */
    restore() {
        try {
            const raw = localStorage.getItem(this.storageKey);
            if (!raw) return;

            const { data } = JSON.parse(raw);
            if (!data || typeof data !== 'object') return;

            const elements = this.form.querySelectorAll('input, textarea, select');

            elements.forEach((el, idx) => {
                if (this.isExcluded(el)) return;

                const key = el.name || el.id || `field_${idx}`;
                if (!(key in data)) return;

                if (el.type === 'checkbox') {
                    if (el.classList.contains('persist')) {
                        el.checked = !!data[key];
                    }
                } else if (el.type === 'radio') {
                    el.checked = el.value === data[key];
                } else if (el.type !== 'file') {
                    el.value = data[key];
                }
            });

            // бейдж для пользователя
            this.showRestoreBadge();
        } catch (e) {
            console.warn('[FormAutoSave] Не удалось восстановить:', e);
        }
    }

    /* бейдж "черновик восстановлен" */
    showRestoreBadge() {
        const existing = document.querySelector('.autosave-badge');
        if (existing) existing.remove();

        const badge = document.createElement('div');
        badge.className = 'autosave-badge';
        badge.innerHTML = '💾 Черновик восстановлен';
        document.body.appendChild(badge);

        setTimeout(() => badge.classList.add('show'), 100);
        setTimeout(() => {
            badge.classList.remove('show');
            setTimeout(() => badge.remove(), 400);
        }, 2800);
    }

    /* очистка (после успешной отправки) */
    clear() {
        localStorage.removeItem(this.storageKey);
    }
}

/* экспорт для использования в шаблонах */
window.FormAutoSave = FormAutoSave;

export default FormAutoSave;
