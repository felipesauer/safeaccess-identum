import { AbstractStateRule } from '../abstract-state-rule.js';

/**
 * Validates Rio Grande do Norte (RN) IE numbers.
 *
 * 9 or 10 digits, prefix 20. Single Mod-11 DV (rest < 2 → 0).
 * 9-digit: weights [9,8,7,6,5,4,3,2]; 10-digit: weights [10,9,8,7,6,5,4,3,2].
 */
export class RnRule extends AbstractStateRule {
    execute(ie: string): boolean {
        const d = this.digits(ie);
        const len = d.length;
        if (d === '' || (len !== 9 && len !== 10) || this.allSameDigits(d)) return false;
        if (d.slice(0, 2) !== '20') return false;

        if (len === 9) {
            const rest = this.sumProducts(this.toIntArray(d.slice(0, 8)), [9, 8, 7, 6, 5, 4, 3, 2]) % 11;
            const dv = rest < 2 ? 0 : 11 - rest;
            return Number(d[8]) === dv;
        }

        const rest = this.sumProducts(this.toIntArray(d.slice(0, 9)), [10, 9, 8, 7, 6, 5, 4, 3, 2]) % 11;
        const dv = rest < 2 ? 0 : 11 - rest;
        return Number(d[9]) === dv;
    }
}
