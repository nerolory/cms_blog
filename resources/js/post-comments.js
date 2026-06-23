/**
 * Infinite scroll и подгрузка веток комментариев на странице поста.
 *
 * @module resources/js/post-comments
 */

const SKELETON_BATCH_SIZE = 3;

/**
 * @param {Response} response
 */
const parseCommentJson = async (response) => {
    if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
    }

    return response.json();
};

const getSkeletonHtml = () => {
    const template = document.getElementById('comment-skeleton-template');
    if (!template) {
        return '';
    }

    return Array.from({ length: SKELETON_BATCH_SIZE }, () => template.innerHTML).join('');
};

const insertSkeleton = (container, before) => {
    const wrapper = document.createElement('div');
    wrapper.className = 'comment-skeleton-batch';
    wrapper.innerHTML = getSkeletonHtml();
    if (before instanceof Node && before.parentNode === container) {
        container.insertBefore(wrapper, before);
    } else {
        container.appendChild(wrapper);
    }

    return wrapper;
};

/**
 * @param {string} html
 */
const assertRootChunkSafe = (html) => {
    const temp = document.createElement('div');
    temp.innerHTML = html;
    if (temp.querySelector('[data-comment-replies] [data-comment-thread]')) {
        throw new Error('Root chunk must not contain nested comment threads inside replies');
    }

    return temp;
};

/**
 * @param {HTMLElement} container
 * @param {string} html
 */
const appendRootsChunk = (container, html) => {
    if (!container?.matches('[data-comment-roots]')) {
        throw new Error('Root chunks may only be appended to [data-comment-roots]');
    }

    const sentinel = container.querySelector('[data-comment-roots-sentinel]');
    const temp = assertRootChunkSafe(html);
    while (temp.firstChild) {
        container.insertBefore(temp.firstChild, sentinel);
    }
};

/**
 * @param {HTMLElement} container
 * @param {string} html
 */
const appendRepliesChunk = (container, html) => {
    const sentinel = container.querySelector('[data-comment-replies-sentinel]');
    const temp = document.createElement('div');
    temp.innerHTML = html;
    while (temp.firstChild) {
        container.insertBefore(temp.firstChild, sentinel);
    }
};

/**
 * @param {HTMLElement} sentinel
 * @param {{ onLoad: () => Promise<void>, isEnabled: () => boolean }} options
 */
const createIntersectionLoader = (sentinel, { onLoad, isEnabled }) => {
    if (!sentinel || sentinel.dataset.observerDisabled === '1') {
        return () => {};
    }

    let loading = false;
    const observer = new IntersectionObserver(async (entries) => {
        if (!entries.some((entry) => entry.isIntersecting) || loading || !isEnabled()) {
            return;
        }

        loading = true;
        try {
            await onLoad();
        } finally {
            loading = false;
        }
    }, { rootMargin: '200px' });

    observer.observe(sentinel);

    return () => observer.disconnect();
};

/**
 * @param {ParentNode} root
 */
const initReplyToggles = (root) => {
    root.querySelectorAll('[data-comment-reply-toggle]').forEach((button) => {
        if (button.dataset.bound === '1') {
            return;
        }
        button.dataset.bound = '1';
        button.addEventListener('click', () => {
            const targetId = button.getAttribute('data-target');
            const form = targetId ? document.getElementById(targetId) : null;
            form?.classList.toggle('d-none');
        });
    });
};

/**
 * @param {HTMLElement} thread
 */
const initThreadReplies = (thread) => {
    const button = thread.querySelector('[data-comment-load-replies]');
    const repliesContainer = thread.querySelector('[data-comment-replies]');
    const sentinel = thread.querySelector('[data-comment-replies-sentinel]');
    const url = button?.getAttribute('data-url');

    if (!repliesContainer || !sentinel || !url) {
        return;
    }

    let offset = 0;
    let hasMore = false;
    let loaded = false;

    const loadMore = async () => {
        const skeleton = insertSkeleton(repliesContainer, sentinel);
        try {
            const payload = await parseCommentJson(await fetch(`${url}?offset=${offset}`, {
                headers: { Accept: 'application/json' },
            }));
            skeleton.remove();

            if (!loaded) {
                repliesContainer.classList.remove('d-none');
                button?.remove();
                loaded = true;
            }

            appendRepliesChunk(repliesContainer, payload.html);
            offset = payload.next_offset ?? offset;
            hasMore = Boolean(payload.has_more);
            initReplyToggles(repliesContainer);

            if (!hasMore) {
                sentinel.dataset.observerDisabled = '1';
                disconnectObserver();
            }
        } catch {
            skeleton.remove();
            if (button) {
                button.disabled = false;
            }
        }
    };

    let disconnectObserver = createIntersectionLoader(sentinel, {
        onLoad: loadMore,
        isEnabled: () => loaded && hasMore,
    });

    if (button && button.dataset.bound !== '1') {
        button.dataset.bound = '1';
        button.addEventListener('click', async () => {
            button.disabled = true;
            offset = 0;
            await loadMore();
        });
    }
};

/**
 * @param {HTMLElement} section
 */
const initRootsInfiniteScroll = (section) => {
    const rootsContainer = section.querySelector('[data-comment-roots]');
    const sentinel = section.querySelector('[data-comment-roots-sentinel]');
    const rootsUrl = section.getAttribute('data-roots-url');

    if (!rootsContainer || !sentinel || !rootsUrl) {
        return;
    }

    let hasMore = section.dataset.hasMoreRoots === '1';
    let offset = Number(section.dataset.rootsOffset ?? '0');

    let disconnectObserver = () => {};

    const loadMore = async () => {
        const skeleton = insertSkeleton(rootsContainer, sentinel);
        try {
            const payload = await parseCommentJson(await fetch(`${rootsUrl}?offset=${offset}`, {
                headers: { Accept: 'application/json' },
            }));
            skeleton.remove();
            rootsContainer.querySelector('[data-comments-empty]')?.remove();
            appendRootsChunk(rootsContainer, payload.html);
            initReplyToggles(rootsContainer);
            rootsContainer.querySelectorAll('[data-comment-thread]').forEach((thread) => {
                initThreadReplies(thread);
            });
            offset = payload.next_offset ?? offset;
            hasMore = Boolean(payload.has_more);
            section.dataset.rootsOffset = String(offset);
            section.dataset.hasMoreRoots = hasMore ? '1' : '0';

            if (!hasMore) {
                sentinel.dataset.observerDisabled = '1';
                disconnectObserver();
            }
        } catch {
            skeleton.remove();
        }
    };

    disconnectObserver = createIntersectionLoader(sentinel, {
        onLoad: loadMore,
        isEnabled: () => hasMore,
    });
};

/**
 * @param {ParentNode} [root]
 */
export const initPostComments = (root = document) => {
    root.querySelectorAll('[data-comments-section]').forEach((section) => {
        initReplyToggles(section);
        section.querySelectorAll('[data-comment-thread]').forEach((thread) => {
            initThreadReplies(thread);
        });
        initRootsInfiniteScroll(section);
    });
};
