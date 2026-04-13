import { AbstractStateRule } from '../abstract-state-rule.js';

/**
 * Validates Bahia (BA) IE numbers.
 *
 * Bahia uses two different check-digit algorithms depending on the second digit (d[1]):
 * - If d[1] ∈ {0,1,2,3,4,5,8} → Mod-10 digit-sum method (similar to credit card Luhn)
 * - Otherwise (d[1] ∈ {6,7,9}) → Mod-11 (result ≥ 10 → DV = 0)
 *
 * Both 8-digit and 9-digit formats are accepted.
 * DV2 (second-to-last) is calculated first; DV1 (last) is then calculated over body + DV2.
 *
 * Source: Sintegra / NFe fiscal note specification.
 */
export class BaRule extends AbstractStateRule {
    execute(ie: string): boolean {
        const d = this.digits(ie);
        const len = d.length;
        if (d === '' || (len !== 8 && len !== 9) || this.allSameDigits(d)) return false;

        const bodyLen = len - 2;
        const body = d.slice(0, bodyLen);
        const second = Number(d[1]);
        const useMod10 = [0, 1, 2, 3, 4, 5, 8].includes(second);

        const w2 = this.weightsDesc(bodyLen + 1, 2);
        const dv2 = useMod10
            ? this.dvMod10DigitSum(this.toIntArray(body), w2)
            : this.dvMod11Ge10Eq0(this.toIntArray(body), w2);

        const w1 = this.weightsDesc(bodyLen + 2, 2);
        const dv1 = useMod10
            ? this.dvMod10DigitSum(this.toIntArray(body + String(dv2)), w1)
            : this.dvMod11Ge10Eq0(this.toIntArray(body + String(dv2)), w1);

        return Number(d[bodyLen]) === dv1 && Number(d[bodyLen + 1]) === dv2;
    }

    private weightsDesc(from: number, to: number): number[] {
        const w: number[] = [];
        for (let i = from; i >= to; i--) w.push(i);
        return w;
    }

    private dvMod11Ge10Eq0(digits: number[], weights: number[]): number {
        const rest = this.sumProducts(digits, weights) % 11;
        const dv = 11 - rest;
        return dv >= 10 ? 0 : dv;
    }

    private dvMod10DigitSum(digits: number[], weights: number[]): number {
        let sum = 0;
        const n = Math.min(digits.length, weights.length);
        for (let i = 0; i < n; i++) {
            const prod = digits[i] * weights[i];
            const prodStr = String(prod);
            for (const ch of prodStr) {
                sum += Number(ch);
            }
        }
        return (10 - (sum % 10)) % 10;
    }
}
