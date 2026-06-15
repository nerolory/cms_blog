import { describe, expect, it } from 'vitest';

import {
    buildPostTrackedFields,
    checkboxFlag,
    hasDirtyFields,
    POST_FORM_BASE_FIELDS,
    PROFILE_TRACKED_FIELDS,
} from '../../resources/js/lib/form-dirty-state';

describe('form-dirty-state', () => {
    it('builds post tracked fields with optional slug and user id', () => {
        expect(buildPostTrackedFields({ hasSlug: false, hasUserId: false })).toEqual([...POST_FORM_BASE_FIELDS]);
        expect(buildPostTrackedFields({ hasSlug: true, hasUserId: true })).toEqual([
            ...POST_FORM_BASE_FIELDS,
            'slug',
            'user_id',
        ]);
    });

    it('detects dirty fields against baseline', () => {
        const baseline = { title: 'Old', body: 'Text' };
        expect(hasDirtyFields(baseline, { title: 'Old', body: 'Text' })).toBe(false);
        expect(hasDirtyFields(baseline, { title: 'New', body: 'Text' })).toBe(true);
    });

    it('detects removed keys as unchanged when not in baseline comparison keys', () => {
        const baseline = { name: 'Ann' };
        expect(hasDirtyFields(baseline, { name: 'Ann', email: 'new@example.com' })).toBe(false);
    });

    it('maps checkbox to string flag', () => {
        expect(checkboxFlag(true)).toBe('1');
        expect(checkboxFlag(false)).toBe('0');
    });

    it('lists profile tracked fields', () => {
        expect(PROFILE_TRACKED_FIELDS).toContain('theme');
        expect(PROFILE_TRACKED_FIELDS).toHaveLength(5);
    });
});
