import { describe, expect, it } from 'vitest';

import {
    filterSlugText,
    insertSlugAtSelection,
    isAllowedSlugChar,
    slugPasteWasFiltered,
    SLUG_MAX_LENGTH,
} from '../../resources/js/lib/post-slug';

describe('post-slug', () => {
    it('allows alphanumeric dash underscore', () => {
        expect(isAllowedSlugChar('a')).toBe(true);
        expect(isAllowedSlugChar('-')).toBe(true);
        expect(isAllowedSlugChar('_')).toBe(true);
        expect(isAllowedSlugChar(' ')).toBe(false);
        expect(isAllowedSlugChar('é')).toBe(false);
    });

    it('filters disallowed characters from pasted text', () => {
        expect(filterSlugText('Hello World!')).toBe('HelloWorld');
        expect(filterSlugText('post-slug_1')).toBe('post-slug_1');
    });

    it('inserts at selection and respects max length', () => {
        const long = 'a'.repeat(SLUG_MAX_LENGTH);
        const result = insertSlugAtSelection(long, SLUG_MAX_LENGTH, SLUG_MAX_LENGTH, 'b');

        expect(result.value.length).toBe(SLUG_MAX_LENGTH);
        expect(result.cursor).toBe(SLUG_MAX_LENGTH);
    });

    it('detects filtered paste', () => {
        expect(slugPasteWasFiltered('ab c', 'abc')).toBe(true);
        expect(slugPasteWasFiltered('abc', 'abc')).toBe(false);
    });
});
