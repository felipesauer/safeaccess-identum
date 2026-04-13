import { AbstractValidatableDocument } from '../../contracts/abstract-validatable-document.js';

/**
 * Validates Brazilian RENAVAM (Registro Nacional de Veículos Automotores) numbers.
 */
export class RenavamValidation extends AbstractValidatableDocument {
    protected doValidate(): boolean {
        const digits = this._raw.replace(/\D+/g, '');

        if (digits.length !== 11) {
            return false;
        }

        if (/^(\d)\1{10}$/.test(digits)) {
            return false;
        }

        const base = digits.slice(0, 10);
        const dvIn = Number(digits[10]);

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

        return dv === dvIn;
    }
}
