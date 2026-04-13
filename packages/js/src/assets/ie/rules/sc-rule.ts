import { AbstractStateRule } from '../abstract-state-rule.js';

export class ScRule extends AbstractStateRule {
    execute(ie: string): boolean {
        const d = this.digits(ie);
        if (d.length !== 9 || this.allSameDigits(d)) return false;

        const dv = this.dvMod11Lt2Eq0(this.toIntArray(d.slice(0, 8)), [9, 8, 7, 6, 5, 4, 3, 2]);
        return Number(d[8]) === dv;
    }

    private dvMod11Lt2Eq0(digits: number[], weights: number[]): number {
        const rest = this.sumProducts(digits, weights) % 11;
        return rest < 2 ? 0 : 11 - rest;
    }
}
