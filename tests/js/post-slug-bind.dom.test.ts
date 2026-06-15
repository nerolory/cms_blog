// @vitest-environment happy-dom

import { describe, expect, it, vi } from 'vitest';

import { bindSlugInput, bindSlugInputs, insertSlugTextAtSelection, showSlugForbiddenTip } from '../../resources/js/lib/post-slug-bind';

describe('post-slug-bind', () => {
    it('inserts filtered text at cursor', () => {
        const input = document.createElement('input');
        input.value = 'hello';
        input.setSelectionRange(5, 5);

        insertSlugTextAtSelection(input, '-world');

        expect(input.value).toBe('hello-world');
    });

    it('blocks space key and shows forbidden tip', () => {
        const input = document.createElement('input');
        input.dataset.forbiddenMessage = 'Invalid character';
        const tip = document.createElement('span');
        tip.hidden = true;

        bindSlugInput(input, tip);

        const event = new KeyboardEvent('keydown', { key: ' ', bubbles: true, cancelable: true });
        const prevented = !input.dispatchEvent(event);

        expect(prevented).toBe(true);
        expect(tip.hidden).toBe(false);
        expect(tip.textContent).toBe('Invalid character');
    });

    it('filters pasted text and keeps allowed characters', () => {
        const input = document.createElement('input');
        input.value = 'post';
        input.setSelectionRange(4, 4);

        bindSlugInput(input, null);

        const clipboardData = {
            getData: vi.fn().mockReturnValue(' slug!'),
        };
        const event = new Event('paste', { bubbles: true, cancelable: true });
        Object.defineProperty(event, 'clipboardData', { value: clipboardData });
        Object.defineProperty(event, 'preventDefault', { value: vi.fn() });

        input.dispatchEvent(event);

        expect(input.value).toBe('postslug');
    });

    it('blocks invalid beforeinput characters', () => {
        const input = document.createElement('input');
        const tip = document.createElement('span');
        tip.hidden = true;

        bindSlugInput(input, tip);

        const event = new InputEvent('beforeinput', {
            inputType: 'insertText',
            data: '@',
            bubbles: true,
            cancelable: true,
        });
        const prevented = !input.dispatchEvent(event);

        expect(prevented).toBe(true);
        expect(tip.hidden).toBe(false);
    });

    it('initializes all slug inputs in container', () => {
        const root = document.createElement('div');
        root.innerHTML = `
            <div class="post-slug-field">
                <input data-slug-input value="abc" />
                <span data-slug-forbidden-tip hidden></span>
            </div>
        `;
        document.body.appendChild(root);

        bindSlugInputs(root);

        const input = root.querySelector('[data-slug-input]') as HTMLInputElement | null;
        const event = new KeyboardEvent('keydown', { key: ' ', bubbles: true, cancelable: true });
        input?.dispatchEvent(event);

        document.body.removeChild(root);
        expect(event.defaultPrevented).toBe(true);
    });

    it('allows delete beforeinput operations', () => {
        const input = document.createElement('input');
        bindSlugInput(input, null);

        const event = new InputEvent('beforeinput', {
            inputType: 'deleteContentBackward',
            bubbles: true,
            cancelable: true,
        });

        expect(input.dispatchEvent(event)).toBe(true);
    });

    it('ignores non-InputEvent beforeinput payloads', () => {
        const input = document.createElement('input');
        bindSlugInput(input, null);

        const event = new Event('beforeinput', { bubbles: true, cancelable: true });
        expect(input.dispatchEvent(event)).toBe(true);
    });

    it('skips non-input elements when binding slug inputs', () => {
        const root = document.createElement('div');
        root.innerHTML = '<div data-slug-input></div>';
        expect(() => bindSlugInputs(root)).not.toThrow();
    });

    it('shows tip via helper with auto hide timer', () => {
        vi.useFakeTimers();
        const input = document.createElement('input');
        input.dataset.forbiddenMessage = 'Nope';
        const tip = document.createElement('span');
        tip.hidden = true;
        const timers: { hideTimer?: number } = {};

        showSlugForbiddenTip(input, tip, timers);
        expect(tip.classList.contains('is-visible')).toBe(true);

        vi.advanceTimersByTime(2150);
        expect(tip.hidden).toBe(true);
        vi.useRealTimers();
    });
});
