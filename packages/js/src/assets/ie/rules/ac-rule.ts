import { AbstractStateRule } from '../abstract-state-rule.js';

/** Validates Acre (AC) IE numbers. 13 digits, prefix 01. Two Mod-11 DVs (rest < 2 → 0): DV1 weights [4,3,2,9,8,7,6,5,4,3,2]; DV2 weights [5,4,3,2,9,8,7,6,5,4,3,2]. */
export class AcRule extends AbstractStateRule {
    execute(ie: string): boolean {
        const d = this.digits(ie);
        if (d === '' || d.length !== 13 || this.allSameDigits(d)) return false;
        if (d.slice(0, 2) !== '01') return false;

        const base11 = d.slice(0, 11);
        const dv1 = this.dvMod11(this.toIntArray(base11), [4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);
        if (Number(d[11]) !== dv1) return false;

        const base12 = base11 + String(dv1);
        const dv2 = this.dvMod11(this.toIntArray(base12), [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);
        return Number(d[12]) === dv2;
    }

    private dvMod11(digits: number[], weights: number[]): number {
        const rest = this.sumProducts(digits, weights) % 11;
        return rest < 2 ? 0 : 11 - rest;
    }
}
