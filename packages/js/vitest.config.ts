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
                branches: 95,
                functions: 100,
                statements: 99,
            },
        },
    },
});
