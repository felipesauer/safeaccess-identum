import { AbstractStateRule } from '../abstract-state-rule.js';

export class ApRule extends AbstractStateRule {
    execute(ie: string): boolean {
        const d = this.digits(ie);
        if (d === '' || d.length !== 9 || this.allSameDigits(d)) return false;
        if (d.slice(0, 2) !== '03') return false;

        const base8 = Number(d.slice(0, 8));
        let p = 0;
        let dConst = 0;

        if (base8 >= 3000001 && base8 <= 3017000) {
            p = 5;
            dConst = 0;
        } else if (base8 >= 3017001 && base8 <= 3019022) {
            p = 9;
            dConst = 1;
        }

        const sum = this.sumProducts(this.toIntArray(d.slice(0, 8)), [9, 8, 7, 6, 5, 4, 3, 2]);
        let dv = 11 - ((sum + p) % 11);

        if (dv === 10) {
            dv = 0;
        } else if (dv === 11) {
            dv = dConst;
        }

        return Number(d[8]) === dv;
    }
}
