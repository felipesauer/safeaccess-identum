import { AbstractStateRule } from '../abstract-state-rule.js';

/** Validates Rio de Janeiro (RJ) IE numbers. 8 digits, single Mod-11 DV (rest < 2 → 0). */
export class RjRule extends AbstractStateRule {
    execute(ie: string): boolean {
        const d = this.digits(ie);
        if (d === '' || d.length !== 8 || this.allSameDigits(d)) return false;

        const rest = this.sumProducts(this.toIntArray(d.slice(0, 7)), [2, 7, 6, 5, 4, 3, 2]) % 11;
        const dv = rest < 2 ? 0 : 11 - rest;
        return Number(d[7]) === dv;
    }
}
