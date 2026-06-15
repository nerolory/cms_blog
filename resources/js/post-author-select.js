/**
 * Tom Select enhancement for post author dropdown fields.
 *
 * @module resources/js/post-author-select
 */

import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.bootstrap5.css';

/**
 * Initialize Tom Select on all author select elements not yet enhanced.
 *
 * @returns {void}
 */
const initAuthorSelects = () => {
    document.querySelectorAll('select[data-author-select="true"]').forEach((element) => {
        if (element.tomselect) {
            return;
        }

        new TomSelect(element, {
            allowEmptyOption: true,
            create: false,
            maxOptions: null,
            placeholder: element.dataset.placeholder || '',
            sortField: {
                field: 'text',
                direction: 'asc',
            },
        });
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAuthorSelects);
} else {
    initAuthorSelects();
}
