import { AbstractValidatableDocument } from '../../contracts/abstract-validatable-document.js';

/**
 * Validates Brazilian CNS (Cartão Nacional de Saúde) numbers.
 */
export class CNSValidation extends AbstractValidatableDocument {
    protected doValidate(): boolean {
        // Strip all non-digit characters to get a clean numeric string
        const digits = this._raw.replace(/\D+/g, '');

        // CNS (National Health Card) must have exactly 15 digits
        if (digits.length !== 15) {
            return false;
        }

        const first = Number(digits[0]);

        // ===== CNS Type 1 & 2: Derived from PIS/PASEP Registration =====
        // Cards starting with 1 or 2 are linked to a valid PIS registration (digits 0-10).
        // Algorithm: weighted sum of PIS (first 11 digits, weights 15 down to 5) modulo 11.
        // DV calculation: 11 - remainder, with special Ministry of Health rules:
        //   - If DV = 11 → set to 0, suffix = '000'
        //   - If DV = 10 → add 2, recalculate DV = 11 - new_remainder, suffix = '001'
        //   - Otherwise DV, suffix = '000'
        if (first === 1 || first === 2) {
            const pis = digits.slice(0, 11);

            let sum = 0;
            for (let i = 0, w = 15; i < 11; i++, w--) {
                sum += Number(pis[i]) * w;
            }

            const rest = sum % 11;
            let dv = 11 - rest;

            let resultado: string;
            if (dv === 11) {
                dv = 0;
                resultado = pis + '000' + String(dv);
            } else if (dv === 10) {
                // Ministry of Health special rule: offset by 2 and recalculate
                sum += 2;
                dv = 11 - (sum % 11);
                resultado = pis + '001' + String(dv);
            } else {
                resultado = pis + '000' + String(dv);
            }

            return digits === resultado;
        }

        // Cards starting with 7, 8, or 9 are provisional (not tied to PIS)
        if (first === 7 || first === 8 || first === 9) {
            // Weighted sum 15..1 must be divisible by 11
            let sum = 0;
            for (let i = 0, w = 15; i < 15; i++, w--) {
                sum += Number(digits[i]) * w;
            }
            return sum % 11 === 0;
        }

        return false;
    }
}
