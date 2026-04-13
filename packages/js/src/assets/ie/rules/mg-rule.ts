import { AbstractStateRule } from '../abstract-state-rule.js';

export class MgRule extends AbstractStateRule {
    execute(ie: string): boolean {
        const d = this.digits(ie);
        if (d === '' || d.length !== 13 || this.allSameDigits(d)) return false;

        const base11 = d.slice(0, 11);
        const aux = base11.slice(0, 3) + '0' + base11.slice(3);

        let sum = 0;
        for (let i = 0; i < 12; i++) {
            const mult = i % 2 === 0 ? 1 : 2;
            const prod = Number(aux[i]) * mult;
            const prodStr = String(prod);
            for (const ch of prodStr) {
                sum += Number(ch);
            }
        }

        const d1 = (10 - (sum % 10)) % 10;
        if (Number(d[11]) !== d1) return false;

        const weights = [3, 2, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2];
        let sum2 = 0;
        for (let i = 0; i < 12; i++) {
            sum2 += Number(i < 11 ? d[i] : String(d1)) * weights[i];
        }
        const rest = sum2 % 11;
        const d2 = rest < 2 ? 0 : 11 - rest;

        return Number(d[12]) === d2;
    }
}
