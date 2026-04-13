import { AbstractStateRule } from '../abstract-state-rule.js';

/**
 * Validates Goiás (GO) IE numbers.
 *
 * 9 digits. Standard Mod-11 DV with a special rule when remainder == 1:
 * if the 8-digit base falls in [10103105..10119997], DV = 1; otherwise DV = 0.
 */
export class GoRule extends AbstractStateRule {
    execute(ie: string): boolean {
        const d = this.digits(ie);
        if (d.length !== 9 || this.allSameDigits(d)) return false;

        const body = this.toIntArray(d.slice(0, 8));
        const sum = this.sumProducts(body, [9, 8, 7, 6, 5, 4, 3, 2]);
        const rest = sum % 11;

        let dv: number;
        if (rest === 0) {
            dv = 0;
        } else if (rest === 1) {
            const num = Number(d.slice(0, 8));
            dv = num >= 10103105 && num <= 10119997 ? 1 : 0;
        } else {
            dv = 11 - rest;
        }

        return Number(d[8]) === dv;
    }
}
