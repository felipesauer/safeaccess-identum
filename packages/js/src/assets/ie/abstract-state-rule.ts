/**
 * Base class for per-state IE validation rules.
 *
 * Each Brazilian state has its own IE format and check-digit algorithm.
 * Subclasses implement {@link execute} with the state-specific logic.
 *
 * @internal
 */
export abstract class AbstractStateRule {
    abstract execute(ie: string): boolean;

    protected digits(v: string, pattern: RegExp = /\D+/g): string {
        return v.replace(pattern, '');
    }

    protected toIntArray(digits: string): number[] {
        return digits.split('').map(Number);
    }

    protected allSameDigits(digits: string): boolean {
        return digits.length > 0 && new Set(digits.split('')).size === 1;
    }

    protected sumProducts(digits: number[], weights: number[]): number {
        const n = Math.min(digits.length, weights.length);
        let sum = 0;
        for (let i = 0; i < n; i++) {
            sum += digits[i] * weights[i];
        }
        return sum;
    }
}
