/** Минимальная непрозрачность контента (синхрон с PostTheme::MIN_CONTENT_OPACITY). */
export const MIN_CONTENT_OPACITY = 50;

/** Максимальная непрозрачность контента (синхрон с PostTheme::MAX_CONTENT_OPACITY). */
export const MAX_CONTENT_OPACITY = 100;

export const DEFAULT_PRIMARY_COLOR = '#0d6efd';

export const DEFAULT_ACCENT_COLOR = '#6c757d';

/** CSS custom properties превью темы поста. */
export type ThemeCssVars = {
    '--post-primary': string;
    '--post-accent': string;
    '--post-content-opacity': string;
};

/**
 * Нормализует opacity 50–100 (зеркало PostTheme::normalizeOpacity).
 */
export function normalizeContentOpacity(value: string | number | null | undefined): number {
    const numeric = typeof value === 'number' ? value : Number(value);
    if (!Number.isFinite(numeric)) {
        return MAX_CONTENT_OPACITY;
    }

    return Math.max(MIN_CONTENT_OPACITY, Math.min(MAX_CONTENT_OPACITY, Math.trunc(numeric)));
}

/**
 * Доля непрозрачности 0–1 для CSS custom property.
 */
export function contentOpacityRatio(opacity: number): number {
    return normalizeContentOpacity(opacity) / 100;
}

/**
 * Подпись процента для UI превью.
 */
export function formatOpacityLabel(opacity: number): string {
    return `${normalizeContentOpacity(opacity)}%`;
}

/**
 * Цвет с fallback, если input пустой.
 */
export function resolveThemeColor(value: string | undefined, fallback: string): string {
    const trimmed = value?.trim();

    return trimmed && trimmed.length > 0 ? trimmed : fallback;
}

/**
 * CSS variables для live-preview блока темы поста.
 */
export function buildThemeCssVars(
    primary?: string,
    accent?: string,
    opacity?: string | number,
): ThemeCssVars {
    const ratio = contentOpacityRatio(Number(opacity ?? MAX_CONTENT_OPACITY));

    return {
        '--post-primary': resolveThemeColor(primary, DEFAULT_PRIMARY_COLOR),
        '--post-accent': resolveThemeColor(accent, DEFAULT_ACCENT_COLOR),
        '--post-content-opacity': String(ratio),
    };
}

/**
 * background-image для превью загруженного фона.
 */
export function buildBackgroundImageStyle(dataUrl: string): string {
    return `url("${dataUrl}")`;
}
