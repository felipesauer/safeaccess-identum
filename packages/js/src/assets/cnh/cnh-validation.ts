import { AbstractValidatableDocument } from '../../contracts/abstract-validatable-document.js';

/**
 * Validates Brazilian CNH (Carteira Nacional de Habilitação) numbers.
 *
 * Algorithm (DENATRAN):
 *  1. DV1 (position 10): weighted sum of 9 digits with weights 9..1, mod 11.
 *     If rest > 9 → DV1 = 0 and `firstIsTenPlus` flag is set.
 *  2. DV2 (position 11): weighted sum of same 9 digits with weights 1..9, mod 11.
 *     If `firstIsTenPlus` is set, subtract 2 from the result (wrapping: if result < 0, add 9).
 *     If final result > 9 → DV2 = 0.
 */
export class CNHValidation extends AbstractValidatableDocument {
    protected doValidate(): boolean {
        const digits = this._raw.replace(/\D+/g, '');

        if (digits.length !== 11) {
            return false;
        }

        if (/^(\d)\1{10}$/.test(digits)) {
            return false;
        }

        const base = digits.slice(0, 9);
        const dvInformed1 = Number(digits[9]);
        const dvInformed2 = Number(digits[10]);

        let sum1 = 0;
        for (let i = 0, w = 9; i < 9; i++, w--) {
            sum1 += Number(base[i]) * w;
        }
        let dv1 = sum1 % 11;
        let firstIsTenPlus = false;
        if (dv1 > 9) {
            dv1 = 0;
            firstIsTenPlus = true;
        }

        let sum2 = 0;
        for (let i = 0, w = 1; i < 9; i++, w++) {
            sum2 += Number(base[i]) * w;
        }
        let dv2 = sum2 % 11;

        if (firstIsTenPlus) {
            if (dv2 - 2 < 0) {
                dv2 += 9;
            } else {
                dv2 -= 2;
            }
        }

        if (dv2 > 9) {
            dv2 = 0;
        }

        return dvInformed1 === dv1 && dvInformed2 === dv2;
    }
}
