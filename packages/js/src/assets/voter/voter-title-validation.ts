import { AbstractValidatableDocument } from '../../contracts/abstract-validatable-document.js';

/**
 * Validates Brazilian Voter Title (Título de Eleitor) numbers.
 */
export class VoterTitleValidation extends AbstractValidatableDocument {
    protected doValidate(): boolean {
        const digits = this._raw.replace(/\D+/g, '');

        if (digits.length !== 12) {
            return false;
        }

        if (/^(\d)\1{11}$/.test(digits)) {
            return false;
        }

        const serial = digits.slice(0, 8);
        const uf = digits.slice(8, 10);
        const dvIn1 = Number(digits[10]);
        const dvIn2 = Number(digits[11]);

        const w1 = [2, 3, 4, 5, 6, 7, 8, 9];
        let sum = 0;
        for (let i = 0; i < 8; i++) {
            sum += Number(serial[i]) * w1[i];
        }
        let dv1 = sum % 11;
        if (dv1 === 10) {
            dv1 = 0;
        }

        const u1 = Number(uf[0]);
        const u2 = Number(uf[1]);
        sum = u1 * 7 + u2 * 8 + dv1 * 9;
        let dv2 = sum % 11;
        if (dv2 === 10) {
            dv2 = 0;
        }

        return dvIn1 === dv1 && dvIn2 === dv2;
    }
}
