import { AbstractValidatableDocument } from '../../contracts/abstract-validatable-document.js';

/**
 * Validates Brazilian Voter Title (Título de Eleitor) numbers.
 */
export class VoterTitleValidation extends AbstractValidatableDocument {
    protected doValidate(): boolean {
        // Strip all non-digit characters to get a clean numeric string
        const digits = this._raw.replace(/\D+/g, '');

        // Voter Title must have exactly 12 digits
        if (digits.length !== 12) {
            return false;
        }

        // Guard: TSE (Supreme Electoral Court) does not use all-same-digit sequences
        if (/^(\d)\1{11}$/.test(digits)) {
            return false;
        }

        const serial = digits.slice(0, 8);
        const uf = digits.slice(8, 10);
        const dvIn1 = Number(digits[10]);
        const dvIn2 = Number(digits[11]);

        // ===== First Verification Digit (DV1) =====
        // Algorithm: weighted sum of 8-digit serial (weights [2,3,4,5,6,7,8,9]) modulo 11.
        // If remainder = 10, DV1 = 0; otherwise DV1 = remainder.
        const w1 = [2, 3, 4, 5, 6, 7, 8, 9];
        let sum = 0;
        for (let i = 0; i < 8; i++) {
            sum += Number(serial[i]) * w1[i];
        }
        let dv1 = sum % 11;
        if (dv1 === 10) {
            dv1 = 0;
        }

        // ===== Second Verification Digit (DV2) =====
        // Algorithm: weighted combination of UF digits + DV1 (weights 7, 8, 9) modulo 11.
        // DV2 = (u1×7 + u2×8 + dv1×9) % 11; if result = 10, DV2 = 0.
        const u1 = Number(uf[0]);
        const u2 = Number(uf[1]);
        sum = u1 * 7 + u2 * 8 + dv1 * 9;
        let dv2 = sum % 11;
        if (dv2 === 10) {
            dv2 = 0;
        }

        // Final verification: check if computed DV1/DV2 match the informed check digits
        return dvIn1 === dv1 && dvIn2 === dv2;
    }
}
