/* ============ МАСКА ТЕЛЕФОНА +7 (###) ###-##-## ============ */
function formatPhone(value) {
    // оставляем только цифры
    let digits = value.replace(/\D/g, '');

    // если пусто — возвращаем пустую строку
    if (!digits.length) return '';

    // если первая цифра не 7/8 — значит ввели без кода страны, добавляем 7
    if (digits[0] === '8') {
        digits = '7' + digits.slice(1);
    } else if (digits[0] !== '7') {
        digits = '7' + digits;
    }

    // ограничиваем до 11 цифр
    digits = digits.slice(0, 11);

    // форматируем: +7 (###) ###-##-##
    let result = '+7';
    if (digits.length > 1) result += ' (' + digits.slice(1, 4);
    if (digits.length >= 5) result += ') ' + digits.slice(4, 7);
    if (digits.length >= 8) result += '-' + digits.slice(7, 9);
    if (digits.length >= 10) result += '-' + digits.slice(9, 11);

    return result;
}

function applyPhoneMask(input) {
    // сохраняем позицию курсора относительно цифр
    const getDigitPos = (el) => {
        const pos = el.selectionStart;
        return el.value.slice(0, pos).replace(/\D/g, '').length;
    };

    const setDigitPos = (el, digitCount) => {
        let charCount = 0;
        let i = 0;
        while (i < el.value.length && charCount < digitCount) {
            if (/\d/.test(el.value[i])) charCount++;
            i++;
        }
        el.setSelectionRange(i, i);
    };

    const handleInput = (e) => {
        // при удалении — если стерли всё до нуля, очищаем
        if (e.inputType === 'deleteContentBackward' || e.inputType === 'deleteContentForward') {
            const digits = input.value.replace(/\D/g, '');
            if (!digits.length) {
                input.value = '';
                return;
            }
        }

        const pos = getDigitPos(input);
        input.value = formatPhone(input.value);
        setDigitPos(input, pos);
    };

    input.addEventListener('input', handleInput);

    // при вставке
    input.addEventListener('paste', (e) => {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData).getData('text');
        input.value = formatPhone(pasted);
        setDigitPos(input, input.value.replace(/\D/g, '').length);
    });

    // начинаем с +7 при фокусе на пустое поле
    input.addEventListener('focus', () => {
        if (!input.value) {
            input.value = '+7';
        }
    });

    // очищаем если только префикс
    input.addEventListener('blur', () => {
        if (input.value.replace(/\D/g, '').length <= 1) {
            input.value = '';
        }
    });

    // запрещаем ввод букв
    input.addEventListener('keypress', (e) => {
        if (!/[\d+]/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete' &&
            e.key !== 'Tab' && !e.ctrlKey && !e.metaKey) {
            e.preventDefault();
        }
    });
}

/* применяем ко всем телефонным полям на странице */
function initPhoneMasks() {
    document.querySelectorAll('input[type="tel"]').forEach(applyPhoneMask);
}

// запускаем при загрузке
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPhoneMasks);
} else {
    initPhoneMasks();
}

/* для динамически добавляемых полей */
const phoneMaskObserver = new MutationObserver((mutations) => {
    mutations.forEach((m) => {
        m.addedNodes.forEach((node) => {
            if (node.nodeType === 1) {
                if (node.tagName === 'INPUT' && node.type === 'tel') {
                    applyPhoneMask(node);
                }
                node.querySelectorAll?.('input[type="tel"]').forEach(applyPhoneMask);
            }
        });
    });
});
phoneMaskObserver.observe(document.body, { childList: true, subtree: true });
