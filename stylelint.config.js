/** @type {import('stylelint').Config} */
export default {
    extends: ['stylelint-config-standard'],
    ignoreFiles: ['**/vendor/**', '**/public/build/**', '**/node_modules/**'],
    rules: {
        'import-notation': null,
        'selector-class-pattern': [
            '^([a-z][a-z0-9]*(-[a-z0-9]+)*)(__[a-z0-9-]+)?(--[a-z0-9-]+)?$',
            {
                resolveNestedSelectors: true,
            },
        ],
    },
};
