import { defineConfig } from 'vitest/config';

export default defineConfig({
    test: {
        coverage: {
            provider: 'v8',
            include: ['resources/js/lib/**/*.ts'],
            reportsDirectory: 'coverage/js',
            thresholds: {
                lines: 85,
                functions: 85,
                branches: 80,
                statements: 85,
            },
        },
        projects: [
            {
                test: {
                    name: 'unit',
                    include: ['tests/js/**/*.test.ts'],
                    exclude: ['tests/js/**/*.dom.test.ts'],
                    environment: 'node',
                },
            },
            {
                test: {
                    name: 'dom',
                    include: ['tests/js/**/*.dom.test.ts'],
                    environment: 'happy-dom',
                },
            },
        ],
    },
});
