import { AbstractStateRule } from '../abstract-state-rule.js';

export class PrRule extends AbstractStateRule {
    execute(ie: string): boolean {
        const d = this.digits(ie);
        if (d.length !== 10 || this.allSameDigits(d)) return false;

        const dv1 = this.dvMod11Lt2Eq0(this.toIntArray(d.slice(0, 8)), [3, 2, 7, 6, 5, 4, 3, 2]);
        if (Number(d[8]) !== dv1) return false;

        const dv2 = this.dvMod11Lt2Eq0(this.toIntArray(d.slice(0, 9)), [4, 3, 2, 7, 6, 5, 4, 3, 2]);
        return Number(d[9]) === dv2;
    }

    private dvMod11Lt2Eq0(digits: number[], weights: number[]): number {
        const rest = this.sumProducts(digits, weights) % 11;
        return rest < 2 ? 0 : 11 - rest;
    }
}
