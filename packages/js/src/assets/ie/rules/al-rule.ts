import { AbstractStateRule } from '../abstract-state-rule.js';

/** Validates Alagoas (AL) IE numbers. 9 digits, prefix 24. DV = (sum × 10) % 11; if result is 10 → DV = 0. Weights [9,8,7,6,5,4,3,2] over first 8 digits. */
export class AlRule extends AbstractStateRule {
    execute(ie: string): boolean {
        const d = this.digits(ie);
        if (d === '' || d.length !== 9 || this.allSameDigits(d)) return false;
        if (d.slice(0, 2) !== '24') return false;

        const sum = this.sumProducts(this.toIntArray(d.slice(0, 8)), [9, 8, 7, 6, 5, 4, 3, 2]);
        let dv = (sum * 10) % 11;
        if (dv === 10) dv = 0;
        return Number(d[8]) === dv;
    }
}
