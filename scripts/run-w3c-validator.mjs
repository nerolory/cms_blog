/**
 * POST rendered HTML fixtures to the W3C Nu Validator API.
 *
 * @module scripts/run-w3c-validator
 */

import { readFileSync, readdirSync } from 'node:fs';
import { join } from 'node:path';

const fixturesDir = join(import.meta.dirname, '..', 'storage', 'framework', 'w3c-fixtures');
const validatorUrl = 'https://validator.w3.org/nu/?out=json';

const files = readdirSync(fixturesDir).filter((name) => name.endsWith('.html'));

if (files.length === 0) {
    console.error('No HTML fixtures found. Run: docker compose exec -T php php scripts/render-w3c-fixtures.php');
    process.exit(1);
}

let totalErrors = 0;
let totalWarnings = 0;

for (const file of files) {
    const html = readFileSync(join(fixturesDir, file), 'utf8');
    const response = await fetch(validatorUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'text/html; charset=utf-8',
        },
        body: html,
    });

    if (! response.ok) {
        console.error(`${file}: validator HTTP ${response.status}`);
        process.exit(1);
    }

    const payload = await response.json();
    const messages = payload.messages ?? [];
    const errors = messages.filter((message) => message.type === 'error');
    const warnings = messages.filter((message) => message.type === 'info' && /warning/i.test(message.message ?? ''));

    totalErrors += errors.length;
    totalWarnings += warnings.length;

    console.log(`\n=== ${file} ===`);
    console.log(`errors: ${errors.length}, warnings: ${warnings.length}`);

    for (const message of [...errors, ...warnings].slice(0, 15)) {
        const line = message.lastLine ?? message.line ?? '?';
        console.log(`  [${message.type}] line ${line}: ${message.message}`);
    }

    if (errors.length + warnings.length > 15) {
        console.log(`  ... and ${errors.length + warnings.length - 15} more`);
    }
}

console.log(`\nW3C Nu Validator summary: ${totalErrors} error(s), ${totalWarnings} warning(s) across ${files.length} page(s).`);
process.exit(totalErrors > 0 ? 1 : 0);
