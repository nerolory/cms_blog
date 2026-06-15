/**
 * Lightweight HTML layout checks for Blade view templates.
 *
 * @module scripts/validate-html
 */

import { readFileSync, readdirSync, statSync } from 'node:fs';
import { join } from 'node:path';

const root = join(import.meta.dirname, '..', 'resources', 'views');
const issues = [];

const voidElements = new Set([
    'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'w3c',
]);

const seoMetaPath = join(root, 'components', 'seo', 'meta.blade.php');
let seoMetaValidated = false;

/**
 * Recursively walk a directory and validate each Blade template file.
 *
 * @param {string} dir - Absolute path to the directory to traverse.
 * @returns {void}
 */
function walk(dir) {
    for (const entry of readdirSync(dir)) {
        const path = join(dir, entry);
        const stats = statSync(path);

        if (stats.isDirectory()) {
            walk(path);
            continue;
        }

        if (! path.endsWith('.blade.php')) {
            continue;
        }

        validateFile(path);
    }
}

/**
 * @param {string} source
 * @returns {boolean}
 */
function isLayoutShell(source) {
    return source.includes('<!DOCTYPE html>');
}

/**
 * @param {string} source
 * @returns {boolean}
 */
function isPage(source) {
    return /@extends\s*\(/.test(source);
}

/**
 * @param {string} source
 * @returns {void}
 */
function validateSeoMetaComponent(source) {
    const requiredPatterns = [
        { pattern: /rel\s*=\s*["']canonical["']/i, label: 'link rel="canonical"' },
        { pattern: /property\s*=\s*["']og:title["']/i, label: 'og:title' },
        { pattern: /property\s*=\s*["']og:description["']/i, label: 'og:description' },
        { pattern: /property\s*=\s*["']og:url["']/i, label: 'og:url' },
        { pattern: /property\s*=\s*["']og:locale["']/i, label: 'og:locale' },
    ];

    for (const { pattern, label } of requiredPatterns) {
        if (! pattern.test(source)) {
            issues.push(`resources/views/components/seo/meta.blade.php: missing ${label}`);
        }
    }
}

/**
 * Flag Blade comments that look like implementation hints rather than section markers.
 *
 * @param {string} source
 * @param {string} relative
 * @returns {void}
 */
function validateCommentPolicy(source, relative) {
    const comments = [...source.matchAll(/{{--([\s\S]*?)--}}/g)];

    for (const match of comments) {
        const body = match[1].trim();
        const lower = body.toLowerCase();

        if (
            lower.includes('todo')
            || lower.includes('fixme')
            || lower.includes('hack')
            || lower.includes('xxx')
            || /\b(if|else|endif|foreach|endforeach|inject|php)\b/.test(lower)
        ) {
            issues.push(`${relative}: logic-hint Blade comment is not allowed (${body.slice(0, 40)}…)`);
        }
    }

    const htmlComments = [...source.matchAll(/<!--([\s\S]*?)-->/g)];

    for (const match of htmlComments) {
        const body = match[1].trim();
        const lower = body.toLowerCase();

        if (
            lower.includes('todo')
            || lower.includes('fixme')
            || lower.includes('hack')
            || /\b(if|else|foreach|blade|@)\b/.test(lower)
        ) {
            issues.push(`${relative}: logic-hint HTML comment is not allowed (${body.slice(0, 40)}…)`);
        }
    }
}

/**
 * Run layout validation rules against a single Blade file.
 *
 * @param {string} path - Absolute path to the Blade template.
 * @returns {void}
 */
function validateFile(path) {
    const source = readFileSync(path, 'utf8');
    const relative = path.replace(/\\/g, '/').replace(/.*\/resources\/views\//, 'resources/views/');

    if (! isLayoutShell(source) && ! isPage(source) && ! relative.includes('components/seo/meta.blade.php')) {
        validateCommentPolicy(source, relative);
        validateImages(source, relative);
        return;
    }

    if (isLayoutShell(source)) {
        if (! /<html[^>]*\blang\s*=/i.test(source)) {
            issues.push(`${relative}: missing html[lang] attribute`);
        }

        if (! /<meta[^>]+charset/i.test(source)) {
            issues.push(`${relative}: missing meta charset`);
        }

        if (! /<meta[^>]+name\s*=\s*["']viewport["']/i.test(source)) {
            issues.push(`${relative}: missing meta viewport`);
        }

        if (! /<main[\s>]/i.test(source)) {
            issues.push(`${relative}: missing <main> landmark`);
        }
    }

    if (isPage(source)) {
        const emptyTitle = source.match(/@section\s*\(\s*['"]title['"]\s*,\s*(['"])\s*\1\s*\)/);

        if (emptyTitle) {
            issues.push(`${relative}: @section('title') must not be empty`);
        }

        if (source.includes('x-seo.meta') && ! /@push\s*\(\s*['"]meta['"]\s*\)/.test(source)) {
            issues.push(`${relative}: x-seo.meta must be pushed to the meta stack`);
        }
    }

    validateImages(source, relative);
    validateCommentPolicy(source, relative);

    if (relative === 'resources/views/components/seo/meta.blade.php' && ! seoMetaValidated) {
        validateSeoMetaComponent(source);
        seoMetaValidated = true;
    }
}

/**
 * @param {string} source
 * @param {string} relative
 * @returns {void}
 */
function validateImages(source, relative) {
    for (const match of source.matchAll(/<img\b[^>]*>/gi)) {
        const tag = match[0];
        const hasEmptyAlt = /\balt\s*=\s*["']\s*["']/i.test(tag);
        const isDecorative = /\brole\s*=\s*["']presentation["']/i.test(tag);

        if (hasEmptyAlt && ! isDecorative) {
            issues.push(`${relative}: img with empty alt (use descriptive alt or role="presentation" for decorative images)`);
        }
    }

    const tags = [...source.matchAll(/<(\/?)([a-zA-Z0-9-]+)([^>]*)>/g)];

    for (const match of tags) {
        const tag = match[2].toLowerCase();

        if (tag.startsWith('@')) {
            continue;
        }

        if (voidElements.has(tag)) {
            continue;
        }

        if (match[3].includes('/>')) {
            continue;
        }
    }
}

walk(root);

if (! seoMetaValidated && statSync(seoMetaPath).isFile()) {
    validateSeoMetaComponent(readFileSync(seoMetaPath, 'utf8'));
}

if (issues.length > 0) {
    console.error('HTML validation issues:');
    issues.forEach((issue) => console.error(`- ${issue}`));
    process.exit(1);
}

console.log('HTML layout validation passed.');
