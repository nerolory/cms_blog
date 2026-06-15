/** Снимок значений полей формы для dirty-tracking. */
export type FormFieldSnapshot = Record<string, string>;

/** Базовые поля формы поста, отслеживаемые на изменения. */
export const POST_FORM_BASE_FIELDS = [
    'title',
    'excerpt',
    'body',
    'editor_mode',
    'theme_primary_color',
    'theme_accent_color',
    'content_opacity',
    'use_article_theme',
] as const;

/** Поля профиля, отслеживаемые на изменения. */
export const PROFILE_TRACKED_FIELDS = [
    'name',
    'email',
    'password',
    'password_confirmation',
    'theme',
] as const;

/**
 * Список полей поста с опциональными slug и user_id (moderator).
 */
export function buildPostTrackedFields(options: { hasSlug: boolean; hasUserId: boolean }): string[] {
    const fields: string[] = [...POST_FORM_BASE_FIELDS];
    if (options.hasSlug) {
        fields.push('slug');
    }
    if (options.hasUserId) {
        fields.push('user_id');
    }

    return fields;
}

/**
 * Есть ли отличия текущего снимка от baseline.
 */
export function hasDirtyFields(baseline: FormFieldSnapshot, current: FormFieldSnapshot): boolean {
    return Object.keys(baseline).some((key) => current[key] !== baseline[key]);
}

/**
 * Checkbox как строковый флаг для сравнения с baseline.
 */
export function checkboxFlag(checked: boolean): '0' | '1' {
    return checked ? '1' : '0';
}
