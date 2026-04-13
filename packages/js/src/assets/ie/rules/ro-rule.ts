import { AbstractStateRule } from '../abstract-state-rule.js';

export class RoRule extends AbstractStateRule {
    execute(ie: string): boolean {
        const d = this.digits(ie);
        if (d === '' || this.allSameDigits(d)) return false;

        if (d.length === 14) return this.validate14(d);
        if (d.length === 9) return this.validate9(d);
        return false;
    }

    private validate14(d: string): boolean {
        const dv = this.dvMod11(this.toIntArray(d.slice(0, 13)), [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);
        return Number(d[13]) === dv;
    }

    private validate9(d: string): boolean {
        const empresa5 = d.slice(3, 8);
        const sum = this.sumProducts(this.toIntArray(empresa5), [6, 5, 4, 3, 2]);
        const rest = sum % 11;
        let dv = 11 - rest;
        if (dv >= 10) dv -= 10;
        return Number(d[8]) === dv;
    }

    private dvMod11(digits: number[], weights: number[]): number {
        const rest = this.sumProducts(digits, weights) % 11;
        return rest < 2 ? 0 : 11 - rest;
    }
}
