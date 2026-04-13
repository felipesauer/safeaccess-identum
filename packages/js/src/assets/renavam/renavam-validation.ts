import { AbstractValidatableDocument } from '../../contracts/abstract-validatable-document.js';

/**
 * Validates Brazilian RENAVAM (Registro Nacional de Veículos Automotores) numbers.
 */
export class RenavamValidation extends AbstractValidatableDocument {
    protected doValidate(): boolean {
        // Strip all non-digit characters to get a clean numeric string
        const digits = this._raw.replace(/\D+/g, '');

        // RENAVAM must have exactly 11 digits
        if (digits.length !== 11) {
            return false;
        }

        // Guard: DENATRAN (Brazilian national vehicle registry) does not assign all-same-digit sequences
        if (/^(\d)\1{10}$/.test(digits)) {
            return false;
        }

        const base = digits.slice(0, 10);
        const dvIn = Number(digits[10]);

        // ===== Verification Digit (DV) =====
        // Algorithm: reverse the 10-digit base, then apply weights [2,3,4,5,6,7,8,9,2,3].
        // DV = 11 - (weighted_sum % 11); if DV ≥ 10, set to 0.
        const rev = base.split('').reverse().join('');
        const pesos = [2, 3, 4, 5, 6, 7, 8, 9, 2, 3];

        let soma = 0;
        for (let i = 0; i < 10; i++) {
            soma += Number(rev[i]) * pesos[i];
        }

        const resto = soma % 11;
        let dv = 11 - resto;
        if (dv >= 10) {
            dv = 0;
        }

        // Final verification: check if computed DV matches the check digit at position 10
        return dv === dvIn;
    }
}
