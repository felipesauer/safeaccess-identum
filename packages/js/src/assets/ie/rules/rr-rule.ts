import { AbstractStateRule } from '../abstract-state-rule.js';

/** Validates Roraima (RR) IE numbers. 9 digits. DV = sum % 9 with weights [1,2,3,4,5,6,7,8] over first 8 digits. */
export class RrRule extends AbstractStateRule {
    execute(ie: string): boolean {
        const d = this.digits(ie);
        if (d === '' || d.length !== 9 || this.allSameDigits(d)) return false;

        const sum = this.sumProducts(this.toIntArray(d.slice(0, 8)), [1, 2, 3, 4, 5, 6, 7, 8]);
        const dv = sum % 9;
        return Number(d[8]) === dv;
    }
}
