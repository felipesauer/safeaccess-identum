import { AbstractValidatableDocument } from '../../contracts/abstract-validatable-document.js';

/**
 * Validates Brazilian CPF (Cadastro de Pessoas Físicas) numbers.
 *
 * Applies Mod11 check-digit algorithm with two verification digits.
 */
export class CPFValidation extends AbstractValidatableDocument {
    protected doValidate(): boolean {
        // Strip all non-digit characters to get a clean numeric string
        const digits = this._raw.replace(/\D+/g, '');

        // CPF must have exactly 11 digits
        if (digits.length !== 11) {
            return false;
        }

        // Guard: Receita Federal (Brazilian tax authority) reserves all 11-same-digit sequences
        // (e.g., 000...000, 111...111) as invalid forever — no valid CPF exists with all same digits.
        if (/^(\d)\1{10}$/.test(digits)) {
            return false;
        }

        // ===== First Verification Digit (DV1) =====
        // Algorithm: weighted sum of first 9 digits (weights 10 down to 2) modulo 11.
        // If remainder < 2, DV1 = 0; otherwise DV1 = 11 - remainder.
        let sum = 0;
        for (let i = 0, w = 10; i < 9; i++, w--) {
            sum += Number(digits[i]) * w;
        }
        const rest1 = sum % 11;
        const dv1 = rest1 < 2 ? 0 : 11 - rest1;

        // ===== Second Verification Digit (DV2) =====
        // Algorithm: weighted sum of first 9 digits PLUS DV1 (weights 11 down to 2) modulo 11.
        // If remainder < 2, DV2 = 0; otherwise DV2 = 11 - remainder.
        // Note: the 10th digit slot (index 9) in the weighted sum is DV1, followed by original digits 10–11 (indices 8–9).
        sum = 0;
        for (let i = 0, w = 11; i < 10; i++, w--) {
            sum += Number(digits[i]) * w;
        }
        const rest2 = sum % 11;
        const dv2 = rest2 < 2 ? 0 : 11 - rest2;

        // Final verification: check if the computed DV1/DV2 match the digits at positions 9 and 10
        return digits[9] === String(dv1) && digits[10] === String(dv2);
    }
}
