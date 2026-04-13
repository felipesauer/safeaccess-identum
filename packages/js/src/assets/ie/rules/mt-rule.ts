import { AbstractStateRule } from '../abstract-state-rule.js';

/**
 * Validates Mato Grosso (MT) IE numbers.
 *
 * 11 digits, single Mod-11 DV: dv = 11 - rest; if dv ≥ 10 → 0.
 * Weights [3,2,9,8,7,6,5,4,3,2] over first 10 digits.
 */
export class MtRule extends AbstractStateRule {
    execute(ie: string): boolean {
        const d = this.digits(ie);
        if (d.length !== 11 || this.allSameDigits(d)) return false;

        const dv = this.dvMod11Ge10Eq0(this.toIntArray(d.slice(0, 10)), [3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);
        return Number(d[10]) === dv;
    }

    private dvMod11Ge10Eq0(digits: number[], weights: number[]): number {
        const rest = this.sumProducts(digits, weights) % 11;
        const dv = 11 - rest;
        return dv >= 10 ? 0 : dv;
    }
}
