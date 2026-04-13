import { AbstractStateRule } from '../abstract-state-rule.js';

export class SeRule extends AbstractStateRule {
    execute(ie: string): boolean {
        const d = this.digits(ie);
        if (d === '' || d.length !== 9 || this.allSameDigits(d)) return false;

        const rest = this.sumProducts(this.toIntArray(d.slice(0, 8)), [9, 8, 7, 6, 5, 4, 3, 2]) % 11;
        const dv = rest < 2 ? 0 : 11 - rest;
        return Number(d[8]) === dv;
    }
}
