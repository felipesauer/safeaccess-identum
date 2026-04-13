import { AbstractValidatableDocument } from '../../contracts/abstract-validatable-document.js';

/**
 * Validates Brazilian PIS/PASEP (Programa de Integração Social) numbers.
 */
export class PISValidation extends AbstractValidatableDocument {
    protected doValidate(): boolean {
        // Strip all non-digit characters to get a clean numeric string
        const digits = this._raw.replace(/\D+/g, '');

        // PIS must have exactly 11 digits
        if (digits.length !== 11) {
            return false;
        }

        // Guard: CEF (Caixa Econômica Federal) reserves all 11-same-digit sequences
        // as invalid forever—no valid PIS exists with all same digits.
        if (/^(\d)\1{10}$/.test(digits)) {
            return false;
        }

        // ===== Verification Digit (DV) =====
        // Algorithm: weighted sum of first 10 digits (weights [3,2,9,8,7,6,5,4,3,2]) modulo 11.
        // DV = 11 - remainder; if DV is 10 or 11 (not representable as a single digit), DV becomes 0.
        const w = [3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        let sum = 0;
        for (let i = 0; i < 10; i++) {
            sum += Number(digits[i]) * w[i];
        }

        const rest = sum % 11;
        let dv = 11 - rest;

        // Edge case: if DV is 10 or 11, it cannot fit in a single digit → set to 0
        if (dv === 10 || dv === 11) {
            dv = 0;
        }

        // Final verification: check if the computed DV matches the digit at position 10
        return String(dv) === digits[10];
    }
}
