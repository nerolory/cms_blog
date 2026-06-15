/** Допустимые символы slug поста. */
export const SLUG_CHAR_PATTERN = /[a-zA-Z0-9_-]/;

/** Максимальная длина slug. */
export const SLUG_MAX_LENGTH = 255;

/**
 * Проверяет, разрешён ли символ в slug.
 */
export function isAllowedSlugChar(char: string): boolean {
    return char.length === 1 && SLUG_CHAR_PATTERN.test(char);
}

/**
 * Оставляет в строке только допустимые символы slug.
 */
export function filterSlugText(text: string): string {
    return text.replace(/[^a-zA-Z0-9_-]/g, '');
}

/**
 * Вставляет текст в значение input с учётом выделения и лимита длины.
 */
export function insertSlugAtSelection(
    currentValue: string,
    start: number,
    end: number,
    text: string,
    maxLength: number = SLUG_MAX_LENGTH,
): { value: string; cursor: number } {
    const nextValue = `${currentValue.slice(0, start)}${text}${currentValue.slice(end)}`.slice(0, maxLength);
    const cursor = Math.min(start + text.length, nextValue.length);

    return { value: nextValue, cursor };
}

/**
 * Был ли отфильтрован хотя бы один символ при вставке slug.
 */
export function slugPasteWasFiltered(original: string, filtered: string): boolean {
    return filtered.length < original.length;
}
