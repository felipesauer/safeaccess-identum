import { AbstractValidatableDocument } from '../../contracts/abstract-validatable-document.js';

/**
 * Validates Brazilian PIS/PASEP (Programa de Integração Social) numbers.
 */
export class PISValidation extends AbstractValidatableDocument {
    protected doValidate(): boolean {
        const digits = this._raw.replace(/\D+/g, '');

        if (digits.length !== 11) {
            return false;
        }

        // CEF: homogeneous sequences are reserved and always invalid
        if (/^(\d)\1{10}$/.test(digits)) {
            return false;
        }

        // Mod 11 with weights [3,2,9,8,7,6,5,4,3,2] over the first 10 digits
        const w = [3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        let sum = 0;
        for (let i = 0; i < 10; i++) {
            sum += Number(digits[i]) * w[i];
        }

        const rest = sum % 11;
        let dv = 11 - rest;

        // Remainder 10 or 11 are not representable as a single digit — DV becomes 0
        if (dv === 10 || dv === 11) {
            dv = 0;
        }

        return String(dv) === digits[10];
    }
}
