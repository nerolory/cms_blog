/**
 * Copy the TinyMCE npm package into the public vendor directory.
 *
 * @module scripts/copy-tinymce
 */

import { cpSync, existsSync, mkdirSync, rmSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const source = join(root, 'node_modules', 'tinymce');
const target = join(root, 'public', 'vendor', 'tinymce');

if (!existsSync(source)) {
    console.error('TinyMCE package not found. Run npm install first.');
    process.exit(1);
}

mkdirSync(join(root, 'public', 'vendor'), { recursive: true });

if (existsSync(target)) {
    rmSync(target, { recursive: true, force: true });
}

cpSync(source, target, { recursive: true });
console.log('Copied TinyMCE to public/vendor/tinymce');
