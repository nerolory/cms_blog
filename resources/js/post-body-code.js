/**
 * Syntax highlighting for code blocks inside published post bodies.
 *
 * @module resources/js/post-body-code
 */

import 'prismjs/themes/prism-tomorrow.css';
import 'prismjs/plugins/line-numbers/prism-line-numbers.css';
import '../css/post-blocks.css';
import '../css/post-comments.css';
import Prism from 'prismjs';
import 'prismjs/components/prism-markup';
import 'prismjs/components/prism-css';
import 'prismjs/components/prism-javascript';
import 'prismjs/components/prism-php';
import 'prismjs/components/prism-sql';
import 'prismjs/components/prism-bash';
import 'prismjs/components/prism-json';
import 'prismjs/plugins/line-numbers/prism-line-numbers';
import { initPostComments } from './post-comments';
import { initPostEngagementSpa } from './post-engagement-spa';

const container = document.querySelector('.post-body');

if (container) {
    container.querySelectorAll('pre[class*="language-"]').forEach((pre) => {
        pre.classList.add('line-numbers');
    });

    Prism.highlightAllUnder(container);
}

initPostComments();
initPostEngagementSpa();
