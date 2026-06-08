import { defineConfig } from 'vitest/config';

export default defineConfig({
    test: {
        globals: false,
        coverage: {
            include: ['src/**/*.ts'],
            exclude: [
                'src/index.ts',
            ],
            reporter: ['json', 'clover'],
            thresholds: {
                lines: 100,
                // One legacy branch in ie/rules/ro-rule.ts is uncovered; raise to 100 once it is.
                branches: 95,
                functions: 100,
                statements: 100,
            },
        },
    },
});
