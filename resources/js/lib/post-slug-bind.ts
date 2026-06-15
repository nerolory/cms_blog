import {
    filterSlugText,
    insertSlugAtSelection,
    isAllowedSlugChar,
    slugPasteWasFiltered,
} from './post-slug';

type TipTimerState = {
    hideTimer?: number;
};

/**
 * Показывает tooltip при попытке ввести запрещённый символ.
 */
export function showSlugForbiddenTip(
    input: HTMLInputElement,
    tip: HTMLElement | null,
    timers: TipTimerState,
): void {
    if (!tip) {
        return;
    }

    tip.textContent = input.dataset.forbiddenMessage ?? '';
    tip.hidden = false;
    tip.classList.add('is-visible');

    clearTimeout(timers.hideTimer);
    timers.hideTimer = window.setTimeout(() => {
        tip.classList.remove('is-visible');
        window.setTimeout(() => {
            tip.hidden = true;
        }, 150);
    }, 2000);
}

/**
 * Вставляет sanitized текст в slug input.
 */
export function insertSlugTextAtSelection(input: HTMLInputElement, text: string): void {
    const start = input.selectionStart ?? input.value.length;
    const end = input.selectionEnd ?? input.value.length;
    const { value, cursor } = insertSlugAtSelection(input.value, start, end, text);

    input.value = value;
    input.setSelectionRange(cursor, cursor);
    input.dispatchEvent(new Event('input', { bubbles: true }));
}

/**
 * Подключает валидацию slug к одному input.
 */
export function bindSlugInput(input: HTMLInputElement, tip: HTMLElement | null): void {
    const timers: TipTimerState = {};

    input.addEventListener('beforeinput', (event) => {
        if (!(event instanceof InputEvent)) {
            return;
        }
        if (event.inputType.startsWith('delete') || event.inputType === 'insertLineBreak') {
            return;
        }

        if (event.data && !isAllowedSlugChar(event.data)) {
            event.preventDefault();
            showSlugForbiddenTip(input, tip, timers);
        }
    });

    input.addEventListener('paste', (event) => {
        event.preventDefault();

        const pasted = event.clipboardData?.getData('text') ?? '';
        const filtered = filterSlugText(pasted);

        if (slugPasteWasFiltered(pasted, filtered)) {
            showSlugForbiddenTip(input, tip, timers);
        }

        if (filtered !== '') {
            insertSlugTextAtSelection(input, filtered);
        }
    });

    input.addEventListener('keydown', (event) => {
        if (event.key === ' ' || event.key === 'Spacebar') {
            event.preventDefault();
            showSlugForbiddenTip(input, tip, timers);
        }
    });
}

/**
 * Инициализирует все slug inputs в контейнере (по умолчанию document).
 */
export function bindSlugInputs(root: ParentNode = document): void {
    root.querySelectorAll('[data-slug-input]').forEach((element) => {
        if (!(element instanceof HTMLInputElement)) {
            return;
        }
        const tip = element.closest('.post-slug-field')?.querySelector('[data-slug-forbidden-tip]');

        bindSlugInput(element, tip instanceof HTMLElement ? tip : null);
    });
}
