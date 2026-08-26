import { defineConfig } from 'vitest/config';

export default defineConfig({
    test: {
        globals: false,
        coverage: {
            include: ['src/**/*.ts'],
            exclude: ['src/index.ts'],
            reporter: ['json', 'clover'],
            thresholds: {
                lines: 100,
                // Short of 100 by a handful of unreachable branches: the `?? null`
                // guards in cpf/ie extractMeta (their lookup tables are total), and
                // the `dv >= 10` arms inside generate(), which depend on the RNG.
                branches: 99,
                functions: 100,
                statements: 100,
            },
        },
    },
});
