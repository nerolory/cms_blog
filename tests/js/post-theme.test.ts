import { describe, expect, it } from 'vitest';

import {
    buildBackgroundImageStyle,
    buildThemeCssVars,
    contentOpacityRatio,
    formatOpacityLabel,
    MAX_CONTENT_OPACITY,
    MIN_CONTENT_OPACITY,
    normalizeContentOpacity,
    resolveThemeColor,
} from '../../resources/js/lib/post-theme';

describe('post-theme', () => {
    it('normalizes opacity to 50–100 like backend PostTheme', () => {
        expect(normalizeContentOpacity(10)).toBe(MIN_CONTENT_OPACITY);
        expect(normalizeContentOpacity(150)).toBe(MAX_CONTENT_OPACITY);
        expect(normalizeContentOpacity(80)).toBe(80);
        expect(normalizeContentOpacity('75')).toBe(75);
        expect(normalizeContentOpacity('invalid')).toBe(MAX_CONTENT_OPACITY);
    });

    it('computes css opacity ratio', () => {
        expect(contentOpacityRatio(100)).toBe(1);
        expect(contentOpacityRatio(50)).toBe(0.5);
        expect(contentOpacityRatio(10)).toBe(0.5);
    });

    it('formats opacity label for preview', () => {
        expect(formatOpacityLabel(88)).toBe('88%');
        expect(formatOpacityLabel(5)).toBe(`${MIN_CONTENT_OPACITY}%`);
    });

    it('resolves theme color with fallback', () => {
        expect(resolveThemeColor('#ff0000', '#000000')).toBe('#ff0000');
        expect(resolveThemeColor('  ', '#0d6efd')).toBe('#0d6efd');
        expect(resolveThemeColor(undefined, '#6c757d')).toBe('#6c757d');
    });

    it('builds css custom properties for preview', () => {
        const vars = buildThemeCssVars('#111111', '#222222', 80);
        expect(vars['--post-primary']).toBe('#111111');
        expect(vars['--post-accent']).toBe('#222222');
        expect(vars['--post-content-opacity']).toBe('0.8');
    });

    it('wraps data url for background preview', () => {
        expect(buildBackgroundImageStyle('data:image/png;base64,abc')).toBe('url("data:image/png;base64,abc")');
    });
});
