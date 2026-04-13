import { AbstractValidatableDocument } from '../../contracts/abstract-validatable-document.js';

/**
 * Validates Brazilian CPF (Cadastro de Pessoas Físicas) numbers.
 *
 * Applies Mod11 check-digit algorithm with two verification digits.
 */
export class CPFValidation extends AbstractValidatableDocument {
    protected doValidate(): boolean {
        const digits = this._raw.replace(/\D+/g, '');

        if (digits.length !== 11) {
            return false;
        }

        // Receita Federal: any 11-same-digit sequence is reserved and permanently invalid
        if (/^(\d)\1{10}$/.test(digits)) {
            return false;
        }

        // DV1: Mod 11 over first 9 digits, weights 10..2
        let sum = 0;
        for (let i = 0, w = 10; i < 9; i++, w--) {
            sum += Number(digits[i]) * w;
        }
        const rest1 = sum % 11;
        const dv1 = rest1 < 2 ? 0 : 11 - rest1;

        // DV2: Mod 11 over first 10 digits (includes DV1), weights 11..2
        sum = 0;
        for (let i = 0, w = 11; i < 10; i++, w--) {
            sum += Number(digits[i]) * w;
        }
        const rest2 = sum % 11;
        const dv2 = rest2 < 2 ? 0 : 11 - rest2;

        return digits[9] === String(dv1) && digits[10] === String(dv2);
    }
}
