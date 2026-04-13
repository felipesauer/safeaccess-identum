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
        // Strip all non-digit characters to get a clean numeric string
        const digits = this._raw.replace(/\D+/g, '');

        // CNH must have exactly 11 digits
        if (digits.length !== 11) {
            return false;
        }

        // Guard: DETRAN (Brazilian traffic authority) does not issue sequential same-digit blocks
        if (/^(\d)\1{10}$/.test(digits)) {
            return false;
        }

        const base = digits.slice(0, 9);
        const dvInformed1 = Number(digits[9]);
        const dvInformed2 = Number(digits[10]);

        // ===== First Verification Digit (DV1) =====
        // Algorithm: weighted sum of first 9 digits (weights 9 down to 1) modulo 11.
        // Overflow rule: if remainder > 9, set DV1 = 0 and flag firstIsTenPlus = true.
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

        // ===== Second Verification Digit (DV2) =====
        // Algorithm: weighted sum of first 9 digits (weights 1 up to 9) modulo 11.
        // Overflow adjustment: if DV1 overflowed, subtract 2 from DV2 (wrapping: if < 0, add 9).
        let sum2 = 0;
        for (let i = 0, w = 1; i < 9; i++, w++) {
            sum2 += Number(base[i]) * w;
        }
        let dv2 = sum2 % 11;

        // When DV1 overflowed, apply DV2 adjustment: subtract 2 with modulo 9 wrapping
        if (firstIsTenPlus) {
            if (dv2 - 2 < 0) {
                dv2 += 9;
            } else {
                dv2 -= 2;
            }
        }

        // Final guard: DV2 overflow (if > 9) also becomes 0
        if (dv2 > 9) {
            dv2 = 0;
        }

        // Final verification: check if computed DV1/DV2 match the informed check digits
        return dvInformed1 === dv1 && dvInformed2 === dv2;
    }
}
