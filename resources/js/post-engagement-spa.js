/**
 * SPA-обновление engagement-блока: fetch-формы и SSE.
 *
 * @module resources/js/post-engagement-spa
 */

import { initPostComments } from './post-comments';

const LOCAL_IGNORE_MS = 2000;

/** @type {EventSource | null} */
let eventSource = null;

/** @type {string | null} */
let lastLocalVersion = null;

/** @type {number} */
let lastLocalAt = 0;

const csrfTokenFrom = (form) => {
    if (form instanceof HTMLFormElement) {
        const input = form.querySelector('input[name="_token"]');
        if (input instanceof HTMLInputElement && input.value !== '') {
            return input.value;
        }
    }

    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
};

const syncCsrfMeta = (scope) => {
    const token = scope.querySelector('input[name="_token"]')?.value;
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (typeof token === 'string' && token !== '' && meta) {
        meta.setAttribute('content', token);
    }
};

/**
 * @param {string} url
 * @param {RequestInit & { form?: HTMLFormElement }} [options]
 */
const fetchEngagement = (url, options = {}) => {
    const { form, ...fetchOptions } = options;
    const headers = {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-Engagement-Spa': '1',
        ...fetchOptions.headers,
    };
    const token = csrfTokenFrom(form);
    if (token !== '') {
        headers['X-CSRF-TOKEN'] = token;
    }

    return fetch(url, {
        credentials: 'same-origin',
        redirect: 'manual',
        ...fetchOptions,
        headers,
    });
};

/**
 * @param {Response} response
 */
const readEngagementJson = async (response) => {
    if (response.type === 'opaqueredirect' || (response.status >= 300 && response.status < 400)) {
        throw new Error(`redirect:${response.status}`);
    }

    const contentType = response.headers.get('Content-Type') ?? '';
    const isJson = contentType.includes('application/json');
    const payload = isJson ? await response.json().catch(() => null) : null;

    if (!response.ok) {
        const details = payload && typeof payload === 'object' && payload !== null
            ? JSON.stringify(payload)
            : `status:${response.status}`;
        throw new Error(`http:${response.status}:${details}`);
    }

    if (!isJson || payload === null) {
        throw new Error(`content-type:${contentType || 'unknown'}`);
    }

    return payload;
};

/**
 * @returns {HTMLElement | null}
 */
const findEngagementApp = () => document.querySelector('[data-post-engagement-app]');

/**
 * @param {HTMLElement} app
 */
const replaceEngagementContent = (app, html, version) => {
    app.innerHTML = html;
    if (version !== undefined) {
        app.dataset.engagementVersion = String(version);
    }
    syncCsrfMeta(app);
    initPostComments(app);
    bindEngagementForms(app);
};

/**
 * @param {HTMLElement} app
 */
const refreshEngagement = async (app) => {
    const url = app.dataset.engagementUrl;
    if (!url) {
        return;
    }

    try {
        const response = await fetchEngagement(url);
        const payload = await readEngagementJson(response);
        replaceEngagementContent(app, payload.engagement_html, payload.version);
    } catch {
        // SSE refresh is best-effort; ignore transient failures.
    }
};

/**
 * @param {HTMLFormElement} form
 * @param {Event['submitter']} submitter
 */
const formBody = (form, submitter) => {
    if (submitter instanceof HTMLButtonElement || submitter instanceof HTMLInputElement) {
        return new FormData(form, submitter);
    }

    return new FormData(form);
};

/**
 * @param {HTMLElement} app
 * @param {HTMLFormElement} form
 * @param {FormData} body
 * @param {HTMLButtonElement[]} [disableButtons]
 */
const submitEngagementForm = async (app, form, body, disableButtons = []) => {
    disableButtons.forEach((button) => {
        button.disabled = true;
    });

    try {
        const response = await fetchEngagement(form.action, {
            method: form.method || 'POST',
            body,
            form,
        });
        const payload = await readEngagementJson(response);
        replaceEngagementContent(app, payload.engagement_html, payload.version);
        lastLocalVersion = String(payload.version);
        lastLocalAt = Date.now();
    } catch (error) {
        console.error('Engagement SPA request error', error, form.action);
    } finally {
        disableButtons.forEach((button) => {
            button.disabled = false;
        });
    }
};

/**
 * @param {HTMLElement} app
 * @param {HTMLFormElement} form
 */
const bindReactionTypeButtons = (app, form) => {
    const typeInput = form.querySelector('[data-engagement-reaction-type]');
    if (!(typeInput instanceof HTMLInputElement)) {
        return;
    }

    form.querySelectorAll('[data-reaction-type]').forEach((button) => {
        if (!(button instanceof HTMLButtonElement) || button.dataset.reactionBound === '1') {
            return;
        }

        button.dataset.reactionBound = '1';
        button.addEventListener('click', async () => {
            const reactionType = button.getAttribute('data-reaction-type') ?? '';
            if (reactionType === '') {
                console.error('Engagement SPA reaction type is empty', form.action);

                return;
            }

            typeInput.value = reactionType;
            const body = new FormData(form);
            body.set('type', reactionType);
            await submitEngagementForm(app, form, body, [button]);
        });
    });
};

/**
 * @param {ParentNode} scope
 */
const bindEngagementForms = (scope = document) => {
    const app = scope.matches?.('[data-post-engagement-app]')
        ? scope
        : scope.querySelector('[data-post-engagement-app]');

    if (!app) {
        return;
    }

    app.querySelectorAll('form').forEach((form) => {
        if (!(form instanceof HTMLFormElement) || form.dataset.spaBound === '1') {
            return;
        }

        form.dataset.spaBound = '1';

        if (form.matches('[data-engagement-reaction-form]')) {
            bindReactionTypeButtons(app, form);

            return;
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const submitter = event.submitter;
            const disableButtons = submitter instanceof HTMLButtonElement || submitter instanceof HTMLInputElement
                ? [submitter]
                : [];

            const body = formBody(form, submitter);
            await submitEngagementForm(app, form, body, disableButtons);
        });
    });
};

const connectEngagementStream = () => {
    const app = findEngagementApp();
    const streamUrl = app?.dataset.streamUrl;
    if (!app || !streamUrl) {
        return;
    }

    eventSource?.close();
    eventSource = new EventSource(streamUrl);

    eventSource.addEventListener('engagement.updated', async (event) => {
        const currentApp = findEngagementApp();
        if (!currentApp) {
            return;
        }

        let version = null;
        try {
            version = String(JSON.parse(event.data).version);
        } catch {
            return;
        }

        if (version === currentApp.dataset.engagementVersion) {
            return;
        }

        if (version === lastLocalVersion && Date.now() - lastLocalAt < LOCAL_IGNORE_MS) {
            currentApp.dataset.engagementVersion = version;

            return;
        }

        await refreshEngagement(currentApp);
    });

    eventSource.onerror = () => {
        eventSource?.close();
        eventSource = null;
        window.setTimeout(connectEngagementStream, 5000);
    };
};

/**
 * @param {ParentNode} [scope]
 */
export const initPostEngagementSpa = (scope = document) => {
    bindEngagementForms(scope);
    connectEngagementStream();
};
