import { AbstractStateRule } from '../abstract-state-rule.js';

export class SpRule extends AbstractStateRule {
    execute(ie: string): boolean {
        const raw = ie.toUpperCase().trim();

        if (raw !== '' && raw[0] === 'P') {
            return this.validateProdutorRural(raw);
        }

        return this.validateComercial(raw);
    }

    private validateComercial(raw: string): boolean {
        const d = this.digits(raw);
        if (d.length !== 12 || this.allSameDigits(d)) return false;

        const dv1 = this.dvSpResto(this.toIntArray(d.slice(0, 8)), [1, 3, 4, 5, 6, 7, 8, 10]);
        if (Number(d[8]) !== dv1) return false;

        const body2 = d.slice(0, 8) + String(dv1) + d.slice(9, 11);
        const dv2 = this.dvSpResto(this.toIntArray(body2), [3, 2, 10, 9, 8, 7, 6, 5, 4, 3, 2]);
        return Number(d[11]) === dv2;
    }

    private validateProdutorRural(raw: string): boolean {
        const digits = this.digits(raw);
        if (digits.length !== 12 || this.allSameDigits(digits)) return false;

        const dv = this.dvSpResto(this.toIntArray(digits.slice(0, 8)), [1, 3, 4, 5, 6, 7, 8, 10]);
        return Number(digits[8]) === dv;
    }

    private dvSpResto(digits: number[], weights: number[]): number {
        const rest = this.sumProducts(digits, weights) % 11;
        return rest === 10 ? 0 : rest;
    }
}
