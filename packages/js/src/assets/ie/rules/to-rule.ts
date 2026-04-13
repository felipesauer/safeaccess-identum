import { AbstractStateRule } from '../abstract-state-rule.js';

export class ToRule extends AbstractStateRule {
    execute(ie: string): boolean {
        const d = this.digits(ie);
        const len = d.length;
        if (d === '' || (len !== 9 && len !== 11) || this.allSameDigits(d)) return false;

        if (len === 9) {
            const dv = this.dvMod11(this.toIntArray(d.slice(0, 8)), [9, 8, 7, 6, 5, 4, 3, 2]);
            return Number(d[8]) === dv;
        }

        const mid = d.slice(2, 4);
        if (!['01', '02', '03', '99'].includes(mid)) return false;

        const calc = d.slice(0, 2) + d.slice(4, 10);
        const dv = this.dvMod11(this.toIntArray(calc), [9, 8, 7, 6, 5, 4, 3, 2]);
        return Number(d[10]) === dv;
    }

    private dvMod11(digits: number[], weights: number[]): number {
        const rest = this.sumProducts(digits, weights) % 11;
        return rest < 2 ? 0 : 11 - rest;
    }
}
