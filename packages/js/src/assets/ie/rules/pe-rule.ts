import { AbstractStateRule } from '../abstract-state-rule.js';

export class PeRule extends AbstractStateRule {
    execute(ie: string): boolean {
        const d = this.digits(ie);
        if (d === '' || this.allSameDigits(d)) return false;

        if (d.length === 14) return this.validate14(d);
        if (d.length === 9) return this.validate9(d);
        return false;
    }

    private validate14(d: string): boolean {
        const rest = this.sumProducts(
            this.toIntArray(d.slice(0, 13)),
            [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2, 9],
        ) % 11;
        const dv = rest < 2 ? 0 : 11 - rest;
        return Number(d[13]) === dv;
    }

    private validate9(d: string): boolean {
        const base7 = d.slice(0, 7);

        const sum1 = this.sumProducts(this.toIntArray(base7), [8, 7, 6, 5, 4, 3, 2]);
        let d1 = 11 - (sum1 % 11);
        if (d1 >= 10) d1 = 0;
        if (Number(d[7]) !== d1) return false;

        const sum2 = this.sumProducts([...this.toIntArray(base7), d1], [9, 8, 7, 6, 5, 4, 3, 2]);
        let d2 = 11 - (sum2 % 11);
        if (d2 >= 10) d2 = 0;
        return Number(d[8]) === d2;
    }
}
